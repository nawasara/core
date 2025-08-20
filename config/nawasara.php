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
];
