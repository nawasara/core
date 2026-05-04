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
];
