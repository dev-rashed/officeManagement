<?php

use App\Models\User;

return [
    'roles' => [
        User::ROLE_SUPERADMIN => ['*'],

        User::ROLE_ADMIN => [
            'finance.view',
            'finance.manage',
            'approvals.manage',
            'users.manage',
            'settings.manage',
        ],

        User::ROLE_ACCOUNTANT => [
            'finance.view',
            'finance.manage',
            'approvals.manage',
        ],

        User::ROLE_CHAIRMAN => [
            'finance.view',
            'approvals.manage',
        ],

        User::ROLE_MANAGING_DIRECTOR => [
            'finance.view',
            'approvals.manage',
        ],

        User::ROLE_DIRECTOR => [
            'finance.view',
            'approvals.manage',
        ],
    ],
];
