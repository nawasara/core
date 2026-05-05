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
];
