<?php

$prefix = 'nawasara-core';
return [
    [
        'label' => 'Dashboard',
        'icon' => 'heroicon-o-home',
        'url' => $prefix.'/dashboard',
        'permission' => $prefix.'.view-dashboard',
    ],
    [
        'label' => 'User Management',
        'icon' => 'heroicon-o-users',
        'url' => $prefix.'/users',
        'permission' => $prefix.'.view-users',
        'submenu' => [
            [
                'label' => 'List User',
                'icon' => 'heroicon-o-list-bullet',
                'url' => $prefix.'/users',
                'permission' => $prefix.'.view-users',
            ],
            [
                'label' => 'Role & Permission',
                'icon' => 'heroicon-o-key',
                'url' => $prefix.'/roles',
                'permission' => $prefix.'.view-roles',
            ],
        ],
    ],
    [
        'label' => 'Komponen',
        'icon' => 'heroicon-o-cube',
        'url' => $prefix.'/components',
        'permission' => $prefix.'.view-components',
        'submenu' => [
            [
                'label' => 'Table',
                'icon' => 'heroicon-o-puzzle-piece',
                'url' => $prefix.'/components/table',
                'permission' => $prefix.'.view-components',
            ],
            [
                'label' => 'Base Komponen',
                'icon' => 'heroicon-o-puzzle-piece',
                'url' => $prefix.'/components/base',
                'permission' => $prefix.'.view-components',
            ],
        ],
    ],
    // Tambahkan menu lain sesuai kebutuhan
];
