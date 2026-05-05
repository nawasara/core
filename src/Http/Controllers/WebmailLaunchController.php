<?php

namespace Nawasara\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Nawasara\Core\Models\WebmailSession;
use Nawasara\Core\Services\EmailLinkResolver;

/**
 * Entry point auto-login webmail dari portal ASN (atau menu shortcut
 * di Nawasara). User klik link → resolver cek mapping → kalau OK,
 * forge WHM session URL → 302 redirect.
 *
 * Audit setiap launch (issued | failed | rejected) di
 * nawasara_webmail_sessions — ada IP, UA, error message, match strategy.
 *
 * Loose coupling ke nawasara/whm: pakai class_exists guard. Kalau
 * package whm dilepas atau service belum ke-register, controller render
 * pesan jelas instead of fatal.
 */
class WebmailLaunchController extends Controller
{
    public function __construct(protected EmailLinkResolver $resolver)
    {
    }

    public function launch(Request $request)
    {
        $user = $request->user();

        // Defensive — middleware auth seharusnya sudah jaga ini, tapi
        // kalau dipanggil dari context tak terduga, jangan crash.
        if (! $user) {
            return redirect()->route('login');
        }

        $resolved = $this->resolver->resolve($user);

        switch ($resolved['status']) {
            case EmailLinkResolver::STATUS_NOT_LINKED:
                $this->logSession($request, $user, null, null, WebmailSession::STATUS_REJECTED, 'not_linked');
                return response()->view('nawasara-core::webmail.not-linked', [
                    'userEmail' => $user->email,
                ], 200);

            case EmailLinkResolver::STATUS_AMBIGUOUS:
                $this->logSession($request, $user, null, null, WebmailSession::STATUS_REJECTED, 'ambiguous');
                return response()->view('nawasara-core::webmail.ambiguous', [
                    'candidates' => $resolved['candidates'] ?? [],
                ], 409);

            case EmailLinkResolver::STATUS_OK:
                return $this->issueSession(
                    $request,
                    $user,
                    $resolved['mailbox'],
                    $resolved['source'],
                );

            default:
                $this->logSession($request, $user, null, null, WebmailSession::STATUS_FAILED, 'unknown_resolver_status');
                return response()->view('nawasara-core::webmail.error', [
                    'message' => 'Resolver mengembalikan status tak dikenal.',
                ], 500);
        }
    }

    protected function issueSession(Request $request, $user, string $mailbox, string $source): RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        $serviceClass = \Nawasara\Whm\Services\WebmailSessionService::class;

        if (! class_exists($serviceClass)) {
            $this->logSession($request, $user, $mailbox, $source, WebmailSession::STATUS_FAILED, 'whm_package_missing');
            return response()->view('nawasara-core::webmail.error', [
                'message' => 'Package nawasara/whm belum terpasang.',
            ], 500);
        }

        try {
            /** @var \Nawasara\Whm\Services\WebmailSessionService $service */
            $service = app($serviceClass);
            $result = $service->createWebmailUrl($mailbox);
        } catch (\Throwable $e) {
            // Spesifik exception (MailboxNotFound / MailboxSuspended) sengaja
            // tidak di-catch terpisah — pesan sudah cukup informatif untuk
            // user, dan classification sudah ada di `error` field log.
            $this->logSession($request, $user, $mailbox, $source, WebmailSession::STATUS_FAILED, $e->getMessage());

            Log::warning('[webmail] launch failed', [
                'user_id' => $user->id,
                'mailbox' => $mailbox,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            return response()->view('nawasara-core::webmail.error', [
                'message' => $this->humanError($e),
            ], 502);
        }

        $this->resolver->touchLink($user, $mailbox);
        $this->logSession($request, $user, $mailbox, $source, WebmailSession::STATUS_ISSUED, null);

        return redirect()->away($result['url']);
    }

    /**
     * Translasi exception class ke pesan user-friendly. Kalau tidak match,
     * fall back ke generic message — jangan expose internal error verbatim
     * ke end-user.
     */
    protected function humanError(\Throwable $e): string
    {
        $class = get_class($e);

        return match (true) {
            str_ends_with($class, 'MailboxNotFoundException') =>
                'Mailbox Anda tidak terdaftar di server email. Hubungi admin.',
            str_ends_with($class, 'MailboxSuspendedException') =>
                'Mailbox Anda atau akun parent sedang suspended. Hubungi admin.',
            default =>
                'Auto-login email sementara tidak tersedia. Silakan coba beberapa saat lagi atau login manual.',
        };
    }

    protected function logSession(Request $request, $user, ?string $mailbox, ?string $source, string $status, ?string $error): void
    {
        try {
            WebmailSession::create([
                'user_id' => $user->id,
                'email_account' => $mailbox,
                'match_strategy' => $source,
                'ip' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
                'status' => $status,
                'error' => $error,
            ]);
        } catch (\Throwable $e) {
            // Audit failure tidak boleh block flow user — log saja.
            Log::warning('[webmail] audit log failed: '.$e->getMessage());
        }
    }
}
