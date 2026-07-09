<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Use Fortify
    |--------------------------------------------------------------------------
    | Aktifkan Laravel Fortify untuk handle login/register/reset password.
    | Disable kalau aplikasi pakai Auth flow custom.
    */
    'use_fortify' => env('NAWASARA_USE_FORTIFY', true),

    /*
    |--------------------------------------------------------------------------
    | Auth
    |--------------------------------------------------------------------------
    | Mode autentikasi (local / sso / both) DIATUR DI SETTING TABLE,
    | bukan di sini — admin bisa toggle live lewat UI tanpa redeploy.
    | Lihat: Nawasara\Core\Auth\AuthMode + halaman /nawasara-core/settings/auth
    |
    | SSO credential (driver, client_id, client_secret, redirect_uri)
    | DIATUR DI VAULT group `sso` — bisa rotate tanpa redeploy + audit log.
    | Lihat: /nawasara-vault → SSO group.
    */
    'auth' => [
        'features' => [
            'registration' => true,
            'reset-passwords' => true,
            'email-verification' => true,
            'two-factor-auth' => false,
        ],

        'views' => [
            'login' => 'nawasara-core::auth.login',
            'register' => 'nawasara-core::auth.register',
            'forgot-password' => 'nawasara-core::auth.forgot-password',
            'reset-password' => 'nawasara-core::auth.reset-password',
            'verify-email' => 'nawasara-core::auth.verify-email',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SSO session binding
    |--------------------------------------------------------------------------
    | Keeps the Laravel session tied to the Keycloak SSO session.
    |
    | check_interval: seconds between refresh-token liveness checks against
    |   Keycloak (EnsureKeycloakSession middleware). Lower = faster detection of
    |   a Keycloak logout, more realm round-trips. 300s (5 min) is a good
    |   balance. A Keycloak logout is reflected in Nawasara within this window.
    |
    | rp_logout: when true, logging out of Nawasara also ends the Keycloak SSO
    |   session (RP-initiated logout via end_session_endpoint).
    */
    'sso' => [
        'check_interval' => env('NAWASARA_SSO_CHECK_INTERVAL', 300),
        'rp_logout' => env('NAWASARA_SSO_RP_LOGOUT', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Webmail SSO Bridge
    |--------------------------------------------------------------------------
    | Auto-login user ke webmail (cPanel/Roundcube) lewat WHM
    | create_user_session API. Mapping user → mailbox via Keycloak claim.
    |
    | sso_claim:
    |   Nama claim di Keycloak ID token yang berisi email mailbox
    |   @ponorogo.go.id user. Contoh value: "bambang@ponorogo.go.id"
    |   atau (kalau multi-mailbox) "bambang@ponorogo.go.id,bambang2@..."
    |
    | claim_delimiter:
    |   Karakter pemisah kalau attribute multi-value diserialize jadi
    |   string tunggal. Defensive — kalau BKD set strict single-value,
    |   tetap aman (string tanpa delimiter di-treat as 1 element array).
    |
    | redirect_after_launch:
    |   URL fallback kalau session URL tidak bisa di-issue (debug only,
    |   normal operation selalu redirect ke session URL WHM).
    */
    'webmail' => [
        'sso_claim' => env('NAWASARA_WEBMAIL_SSO_CLAIM', 'kominfo_email'),
        'claim_delimiter' => env('NAWASARA_WEBMAIL_CLAIM_DELIMITER', ','),
        'service' => env('NAWASARA_WEBMAIL_SERVICE', 'webmaild'),
    ],

    // Sudo Mode tuning lives in config('auth-primitives.sudo') — moved out
    // of core so domain packages (vault, api, keycloak, ...) can read the
    // same values without depending on core. Edit there, or publish via
    // `php artisan vendor:publish --tag=auth-primitives:config`.
];
