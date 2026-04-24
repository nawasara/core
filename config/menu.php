<?php

$prefix = 'nawasara-core';

return [
    [
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
];
