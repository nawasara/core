<?php

return [
    'theme' => [
        'primary_color' => '#4F46E5',
        'secondary_color' => '#9333EA',
        'font_family' => 'Inter, sans-serif',
    ],
    'components' => [
        'toaster' => true,
        'modal' => true,
        'table' => true,
    ],
    /*
    |--------------------------------------------------------------------------
    | Default Home Page
    |--------------------------------------------------------------------------
    | Tentukan apakah package ini harus menyediakan halaman default index
    | Jika true, route "/" akan diarahkan ke view bawaan package.
    | Jika false, aplikasi akan mengatur halaman utamanya sendiri.
    |
    */

    'use_default_home' => true,

    /*
    |--------------------------------------------------------------------------
    | Default Home Route
    |--------------------------------------------------------------------------
    | Nama route bawaan dari core. Bisa dioverride oleh aplikasi.
    |
    */

    'home_route' => 'nawasara-core.dashboard',
    
    /*
    |--------------------------------------------------------------------------
    | Authentication Provider
    |--------------------------------------------------------------------------
    | Tentukan provider auth yang digunakan core.
    | Opsi: "jetstream", "keycloak"
    */
    'auth_provider' => 'jetstream',
    'use_fortify' => env('NAWASARA_USE_FORTIFY', true),

    'auth' => [
        'default' => env('NAWASARA_AUTH_DRIVER', 'form'), // form | sso
        'default_role' => env('NAWASARA_DEFAULT_ROLE', 'user'),

        'sso' => [
            'driver' => env('SSO_DRIVER', 'keycloak'), // keycloak | google | saml
            'client_id' => env('SSO_CLIENT_ID'),
            'client_secret' => env('SSO_CLIENT_SECRET'),
            'redirect' => env('SSO_REDIRECT_URI'),
            'base_url' => env('SSO_BASE_URL', null), // untuk keycloak
        ],
        // fitur Fortify
        'features' => [
            'registration'      => true,
            'reset-passwords'   => true,
            'email-verification'=> true,
            'two-factor-auth'   => false,
        ],

        'views' => [
            'login'             => 'nawasara-core::auth.login',
            'register'          => 'nawasara-core::auth.register',
            'forgot-password'   => 'nawasara-core::auth.forgot-password',
            'reset-password'    => 'nawasara-core::auth.reset-password',
            'verify-email'      => 'nawasara-core::auth.verify-email',
        ],
    ],
];
