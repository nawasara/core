<?php

$prefix = 'nawasara-core';
return [
    // [
    //     'label' => 'Overview',
    //     'icon' => 'heroicon-o-users',
    //     'url' => $prefix.'/users',
    //     'permission' => $prefix.'.view-users',
    //     'submenu' => [
    //         [
    //             'label' => 'Dashboard',
    //             'icon' => 'heroicon-o-home',
    //             'url' => 'home',
    //             'permission' => $prefix.'.view-dashboard',
    //         ]
    //     ],
    // ],
    [
        'label' => 'User Management',
        'icon' => 'heroicon-o-users',
        'url' => '',
        'permission' => $prefix.'.view-users',
        'submenu' => [
            [
                'label' => 'Users',
                'icon' => 'heroicon-o-list-bullet',
                'url' => url($prefix.'/users'),
                'permission' => $prefix.'.view-users',
                'navigate' => true,
            ],
            [
                'label' => 'Role',
                'icon' => 'heroicon-o-key',
                'url' => url($prefix.'/roles'),
                'permission' => $prefix.'.view-roles',
                'navigate' => true,
            ],
        ],
    ],
    [
        'label' => 'Components',
        'icon' => 'heroicon-o-cube',
        'url' => $prefix.'/components',
        'permission' => $prefix.'.view-components',
        'submenu' => [
            [
                'label' => 'Table',
                'icon' => 'heroicon-o-puzzle-piece',
                'url' => $prefix.'/components/table',
                'permission' => $prefix.'.view-components',
                'navigate' => true,
            ],
            [
                'label' => 'Base Komponen',
                'icon' => 'heroicon-o-puzzle-piece',
                'url' => $prefix.'/components/base',
                'permission' => $prefix.'.view-components',
                'navigate' => true,
            ],
            
            [
                'label' => 'Form Komponen',
                'icon' => 'heroicon-o-puzzle-piece',
                'url' => $prefix.'/components/form',
                'permission' => $prefix.'.view-components',
                'navigate' => true,
            ],
            
        ],
    ],
    // Tambahkan menu lain sesuai kebutuhan
];
