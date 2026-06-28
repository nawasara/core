<?php

// $prefix is used for URL paths and route names ONLY (the original
// long-form `nawasara-core` is intentionally kept across all packages
// for namespace clarity). Permission names use the short alias `core.*`
// to match the convention used by every other package (whm.*, vault.*,
// cloudflare.*, etc).
$prefix = 'nawasara-core';

return [
    [
        'workspace' => 'user-management',
        'label' => 'Manajemen User',
        'icon' => 'lucide-users',
        'group' => 'Pengaturan',
        'url' => '',
        'permission' => 'core.user.view',
        'submenu' => [
            [
                'label' => 'Users',
                'icon' => 'lucide-user',
                'url' => url($prefix.'/users'),
                'permission' => 'core.user.view',
                'navigate' => true,
            ],
            [
                'label' => 'Role',
                'icon' => 'lucide-shield-check',
                'url' => url($prefix.'/roles'),
                'permission' => 'core.role.view',
                'navigate' => true,
            ],
        ],
    ],

    [
        'workspace' => 'settings',
        'label' => 'Pengaturan',
        'icon' => 'lucide-settings',
        'group' => 'Pengaturan',
        'url' => '',
        'permission' => 'core.branding.manage',
        'submenu' => [
            [
                'label' => 'Branding',
                'icon' => 'lucide-palette',
                'url' => url($prefix.'/branding'),
                'permission' => 'core.branding.manage',
                'navigate' => true,
            ],
            [
                'label' => 'Authentication',
                'icon' => 'lucide-key-round',
                'url' => url($prefix.'/settings/auth'),
                'permission' => 'core.auth.manage',
                'navigate' => true,
            ],
            [
                'label' => 'Email Link',
                'icon' => 'lucide-mail-search',
                'url' => url($prefix.'/settings/email-link'),
                'permission' => 'core.email-link.manage',
                'navigate' => true,
            ],
        ],
    ],
];
