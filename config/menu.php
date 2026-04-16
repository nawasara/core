<?php

$prefix = 'nawasara-core';

return [
    [
        'label' => 'User Management',
        'icon' => 'heroicon-o-users',
        'url' => '',
        'permission' => $prefix.'.user.view',
        'submenu' => [
            [
                'label' => 'Users',
                'icon' => 'heroicon-o-list-bullet',
                'url' => url($prefix.'/users'),
                'permission' => $prefix.'.user.view',
                'navigate' => true,
            ],
            [
                'label' => 'Role',
                'icon' => 'heroicon-o-key',
                'url' => url($prefix.'/roles'),
                'permission' => $prefix.'.role.view',
                'navigate' => true,
            ],
        ],
    ],
];
