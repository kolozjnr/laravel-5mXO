<?php

return [
    /**
     * Control if the seeder should create a user per role while seeding the data.
     */
    'create_users' => false,

    /**
     * Control if all the laratrust tables should be truncated before running the seeder.
     */
    'truncate_tables' => true,

    'roles_structure' => [
        'superadministrator' => [
            'users' => 'c,r,u,d',
            'payments' => 'c,r,u,d',
            'profile' => 'r,u',
        ],
        'businessadministrator' => [
            'users' => 'c,r,u,d',
            'profile' => 'r,u',
        ],
        'businessmanager' => [
            'users' => 'c,r,u,d',
            'profile' => 'r,u',
        ],
        'driver' => [
            'users' => 'c,r,u,d',
            'profile' => 'r,u',
        ],
        'customer' => [
            'profile' => 'r,u',
        ],
        'carrier' => [
            'users' => 'c,r,u,d',
            'profile' => 'r,u',
        ],
        'user' => [
            'profile' => 'r,u',
        ],
        // 'role_name' => [
        //     'module_1_name' => 'c,r,u,d',
        // ],
    ],

    'permissions_map' => [
        'c' => 'create',
        'r' => 'read',
        'u' => 'update',
        'd' => 'delete',
    ],
];
