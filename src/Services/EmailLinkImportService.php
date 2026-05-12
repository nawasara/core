<?php

namespace Nawasara\Core\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToArray;
use Nawasara\Core\Models\EmailLinkImport;
use Nawasara\Core\Models\Role;
use Nawasara\Core\Models\UserEmailLink;
use Nawasara\Keycloak\Services\KeycloakClient;
use Throwable;

/**
 * Excel-driven importer for user → kominfo-email mapping.
 *
 * Per-row flow (see project notes; locked 2026-05-12):
 *   1. Look up the user in Keycloak by username (== NIP from Excel). If not
 *      found, SKIP the row entirely. We do NOT create users speculatively.
 *   2. If Keycloak user exists but Laravel user doesn't, auto-create the
 *      Laravel user with auth_type=sso and role=guest. The user can promote
 *      to a higher role later via the role-management UI.
 *   3. Set Keycloak user attribute `kominfo_email` to the Excel value.
 *      Failure here is NON-fatal — the local mapping below still applies.
 *   4. Upsert the local UserEmailLink with source='manual' (overwrites any
 *      previous mapping for the user; Q-C4 = overwrite).
 *
 * The service writes back to the EmailLinkImport row as it goes (counts,
 * errors_json, status) so the UI can poll progress.
 */
class EmailLinkImportService
{
    private const REASON_KEYCLOAK_USER_NOT_FOUND = 'keycloak_user_not_found';
    private const REASON_LARAVEL_USER_AUTO_CREATED = 'laravel_user_auto_created';
    private const REASON_KEYCLOAK_ATTRIBUTE_SET_FAILED = 'keycloak_attribute_set_failed';
    private const REASON_INVALID_ROW = 'invalid_row';
    private const REASON_DUPLICATE_USERNAME_IN_FILE = 'duplicate_username_in_file';

    public function __construct(
        private readonly KeycloakClient $keycloak,
    ) {
    }

    /**
     * Process the import file referenced by the given import-batch row.
     *
     * Idempotent only at the row level (upsert by user_id); calling this
     * twice for the same import row will double-count metrics. The Job that
     * dispatches this guards against double-processing via status check.
     */
    public function process(EmailLinkImport $import): void
    {
        $import->update([
            'status' => EmailLinkImport::STATUS_PROCESSING,
            'started_at' => now(),
        ]);

        try {
            $rows = $this->readSpreadsheet($import->storage_path);
        } catch (Throwable $e) {
            $import->update([
                'status' => EmailLinkImport::STATUS_FAILED,
                'worker_error' => 'Failed to read spreadsheet: '.$e->getMessage(),
                'completed_at' => now(),
            ]);
            return;
        }

        $import->update(['total_rows' => count($rows)]);

        $errors = [];
        $successCount = 0;
        $skippedCount = 0;
        $errorCount = 0;
        $seenUsernames = [];

        foreach ($rows as $i => $row) {
            $rowNumber = $i + 2; // +1 for 0-index, +1 for header row
            $username = $this->cleanString($row[0] ?? '');
            $email = $this->cleanString($row[1] ?? '');

            if ($username === '' || $email === '') {
                $errorCount++;
                $errors[] = $this->makeError($rowNumber, $username, self::REASON_INVALID_ROW,
                    'Row kosong atau kolom NIP/email kosong');
                continue;
            }

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errorCount++;
                $errors[] = $this->makeError($rowNumber, $username, self::REASON_INVALID_ROW,
                    "Format email tidak valid: {$email}");
                continue;
            }

            if (isset($seenUsernames[$username])) {
                $errorCount++;
                $errors[] = $this->makeError($rowNumber, $username, self::REASON_DUPLICATE_USERNAME_IN_FILE,
                    "Username {$username} muncul di baris ke-{$seenUsernames[$username]} dan ke-{$rowNumber}; baris ke-{$rowNumber} di-skip");
                continue;
            }
            $seenUsernames[$username] = $rowNumber;

            $rowResult = $this->processRow($username, $email);

            match ($rowResult['outcome']) {
                'success' => $successCount++,
                'skipped' => $skippedCount++,
                'error' => $errorCount++,
            };

            if ($rowResult['outcome'] !== 'success') {
                $errors[] = $this->makeError(
                    $rowNumber,
                    $username,
                    $rowResult['reason'],
                    $rowResult['message'] ?? ''
                );
            }
        }

        $import->update([
            'status' => EmailLinkImport::STATUS_COMPLETED,
            'completed_at' => now(),
            'success_count' => $successCount,
            'skipped_count' => $skippedCount,
            'error_count' => $errorCount,
            'errors_json' => $errors ?: null,
        ]);

        activity('email-link-import')
            ->performedOn($import)
            ->causedBy($import->user_id)
            ->withProperties([
                'attributes' => [
                    'total' => $import->total_rows,
                    'success' => $successCount,
                    'skipped' => $skippedCount,
                    'error' => $errorCount,
                ],
            ])
            ->log("Email-link import completed: {$successCount} success, {$skippedCount} skipped, {$errorCount} error");
    }

    /**
     * Process a single row. Returns one of:
     *   ['outcome' => 'success']
     *   ['outcome' => 'skipped', 'reason' => '<slug>', 'message' => '<human>']
     *   ['outcome' => 'error',   'reason' => '<slug>', 'message' => '<human>']
     */
    private function processRow(string $username, string $email): array
    {
        // Step 1: Keycloak is the source of truth for "does this user exist?"
        try {
            $kcUser = $this->keycloak->findUserByUsername($username);
        } catch (Throwable $e) {
            return [
                'outcome' => 'error',
                'reason' => 'keycloak_api_error',
                'message' => 'Gagal lookup Keycloak: '.$e->getMessage(),
            ];
        }

        if ($kcUser === null) {
            return [
                'outcome' => 'skipped',
                'reason' => self::REASON_KEYCLOAK_USER_NOT_FOUND,
                'message' => "User {$username} tidak ditemukan di Keycloak — skip seluruh baris",
            ];
        }

        $kcUserId = (string) ($kcUser['id'] ?? '');
        if ($kcUserId === '') {
            return [
                'outcome' => 'error',
                'reason' => 'keycloak_user_missing_id',
                'message' => "Keycloak user untuk {$username} tidak punya ID — anomaly",
            ];
        }

        // Step 2: Find or auto-create the Laravel user.
        $laravelUser = User::where('username', $username)->first();
        $autoCreated = false;

        if (! $laravelUser) {
            try {
                $laravelUser = DB::transaction(function () use ($username, $email, $kcUser) {
                    $first = trim((string) ($kcUser['firstName'] ?? ''));
                    $last = trim((string) ($kcUser['lastName'] ?? ''));
                    $name = trim($first.' '.$last);

                    $user = User::create([
                        'name' => $name !== '' ? $name : $username,
                        'username' => $username,
                        'email' => $email,
                        // Random unguessable password — auth_type=sso means
                        // the user will never authenticate against this hash;
                        // we just satisfy the NOT NULL constraint.
                        'password' => bcrypt(Str::random(40)),
                        'auth_type' => 'sso',
                    ]);

                    if (Role::where('name', 'guest')->exists()) {
                        $user->assignRole('guest');
                    }

                    return $user;
                });
                $autoCreated = true;
            } catch (Throwable $e) {
                return [
                    'outcome' => 'error',
                    'reason' => 'laravel_user_create_failed',
                    'message' => 'Gagal create Laravel user: '.$e->getMessage(),
                ];
            }
        }

        // Step 3: Set Keycloak attribute. Failure is non-fatal — the local
        // mapping below still gives Nawasara its source-of-truth.
        $kcAttributeOk = true;
        try {
            $kcAttributeOk = $this->keycloak->setUserAttribute($kcUserId, 'kominfo_email', $email);
        } catch (Throwable $e) {
            $kcAttributeOk = false;
            Log::warning('[email-link-import] keycloak setUserAttribute threw', [
                'username' => $username,
                'error' => $e->getMessage(),
            ]);
        }

        // Step 4: Upsert local UserEmailLink (overwrite per Q-C4).
        UserEmailLink::updateOrCreate(
            ['user_id' => $laravelUser->id],
            [
                'email_account' => $email,
                'source' => UserEmailLink::SOURCE_MANUAL,
                'linked_at' => now(),
            ]
        );

        // Audit per-row important transitions, even on success.
        if ($autoCreated) {
            activity('email-link-import')
                ->performedOn($laravelUser)
                ->withProperties([
                    'attributes' => [
                        'username' => $username,
                        'role' => 'guest',
                        'source' => 'excel_import',
                    ],
                ])
                ->log('Laravel user auto-created from Excel import');
        }

        if (! $kcAttributeOk) {
            // Partial success: local mapping saved, Keycloak attribute didn't take.
            return [
                'outcome' => 'error',
                'reason' => self::REASON_KEYCLOAK_ATTRIBUTE_SET_FAILED,
                'message' => "Keycloak attribute kominfo_email gagal di-set untuk {$username} — local mapping tetap tersimpan, retry manual atau ulangi import",
            ];
        }

        return ['outcome' => 'success'];
    }

    private function readSpreadsheet(string $path): array
    {
        $importer = new class implements ToArray
        {
            public array $rows = [];

            public function array(array $rows): void
            {
                // Drop header row + any fully-empty trailing rows.
                array_shift($rows);
                $this->rows = array_values(array_filter($rows, function ($row) {
                    return ! empty(array_filter($row, fn ($v) => $v !== null && $v !== ''));
                }));
            }
        };

        Excel::import($importer, $path);

        return $importer->rows;
    }

    private function cleanString(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        return trim((string) $value);
    }

    private function makeError(int $row, string $username, string $reason, string $message): array
    {
        return [
            'row' => $row,
            'username' => $username,
            'reason' => $reason,
            'message' => $message,
        ];
    }
}
