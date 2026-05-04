<?php

$prefix = 'nawasara-core';

return [
    [
        'workspace' => 'user-management',
        'label' => 'User Management',
        'icon' => 'lucide-users',
        'url' => '',
        'permission' => $prefix.'.user.view',
        'submenu' => [
            [
                'label' => 'Users',
                'icon' => 'lucide-user',
                'url' => url($prefix.'/users'),
                'permission' => $prefix.'.user.view',
                'navigate' => true,
            ],
            [
                'label' => 'Role',
                'icon' => 'lucide-shield-check',
                'url' => url($prefix.'/roles'),
                'permission' => $prefix.'.role.view',
                'navigate' => true,
            ],
        ],
    ],

    [
        'workspace' => 'settings',
        'label' => 'Pengaturan',
        'icon' => 'lucide-settings',
        'url' => '',
        'permission' => $prefix.'.branding.manage',
        'submenu' => [
            [
                'label' => 'Branding',
                'icon' => 'lucide-palette',
                'url' => url($prefix.'/branding'),
                'permission' => $prefix.'.branding.manage',
                'navigate' => true,
            ],
            [
                'label' => 'Authentication',
                'icon' => 'lucide-key-round',
                'url' => url($prefix.'/settings/auth'),
                'permission' => $prefix.'.auth.manage',
                'navigate' => true,
            ],
        ],
    ],
];
