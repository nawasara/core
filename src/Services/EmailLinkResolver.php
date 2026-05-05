<?php

namespace Nawasara\Core\Services;

use App\Models\User;
use Nawasara\Core\Models\UserEmailLink;

/**
 * Resolve mailbox @ponorogo.go.id untuk user app Nawasara — input ke
 * WebmailSessionService.
 *
 * Priority:
 *   1. Manual override (admin set di Setting UI) — selalu menang. Kalau
 *      ada >1 manual link, picker policy: kita pakai yang paling baru.
 *      (Konvensi: admin biasanya set 1 mailbox per user.)
 *   2. SSO attribute claim (cached di nawasara_user_email_links saat login).
 *      Kalau ada >1 row sso_attribute → return ambiguous (admin harus set
 *      1 manual sebagai primary).
 *   3. Tidak ada link → not_linked.
 */
class EmailLinkResolver
{
    public const STATUS_OK = 'ok';
    public const STATUS_AMBIGUOUS = 'ambiguous';
    public const STATUS_NOT_LINKED = 'not_linked';

    /**
     * Resolve primary mailbox untuk user.
     *
     * @return array{
     *   status: 'ok'|'ambiguous'|'not_linked',
     *   mailbox?: string,
     *   source?: string,
     *   candidates?: array<int, array{mailbox:string, source:string}>
     * }
     */
    public function resolve(User $user): array
    {
        // Step 1: Manual override menang
        $manual = UserEmailLink::query()
            ->forUser($user->id)
            ->manual()
            ->latest('updated_at')
            ->first();

        if ($manual) {
            return [
                'status' => self::STATUS_OK,
                'mailbox' => $manual->email_account,
                'source' => UserEmailLink::SOURCE_MANUAL,
            ];
        }

        // Step 2: SSO attribute (auto-cached saat login)
        $ssoLinks = UserEmailLink::query()
            ->forUser($user->id)
            ->fromSso()
            ->orderBy('email_account')
            ->get();

        if ($ssoLinks->isEmpty()) {
            return ['status' => self::STATUS_NOT_LINKED];
        }

        if ($ssoLinks->count() === 1) {
            return [
                'status' => self::STATUS_OK,
                'mailbox' => $ssoLinks->first()->email_account,
                'source' => UserEmailLink::SOURCE_SSO_ATTRIBUTE,
            ];
        }

        // >1 sso_attribute tanpa manual override — biar admin pilih primary
        return [
            'status' => self::STATUS_AMBIGUOUS,
            'candidates' => $ssoLinks->map(fn ($l) => [
                'mailbox' => $l->email_account,
                'source' => $l->source,
            ])->all(),
        ];
    }

    /**
     * Catat penggunaan link supaya admin Setting UI bisa show "last used"
     * (tau mana mapping yang aktif vs stale). Idempotent untuk record sama.
     */
    public function touchLink(User $user, string $mailbox): void
    {
        UserEmailLink::query()
            ->forUser($user->id)
            ->where('email_account', $mailbox)
            ->update(['last_used_at' => now()]);
    }
}
