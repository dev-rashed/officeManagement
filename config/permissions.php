<?php

use App\Models\User;

return [
    'roles' => [
        User::ROLE_SUPERADMIN => ['*'],

        User::ROLE_ADMIN => [
            'finance.view',
            'finance.manage',
            'finance.categories.manage',
            'approvals.manage',
            'users.manage',
            'settings.manage',
            'cms.manage',
            'assets.manage',
            'projects.view',
            'projects.manage',
            'students.manage',
            'activity.view',
        ],

        User::ROLE_ACCOUNTANT => [
            'finance.view',
            'finance.manage',
            'finance.categories.manage',
            'approvals.manage',
            'cms.manage',
            'assets.manage',
            'projects.view',
            'projects.manage',
            'students.manage',
        ],

        User::ROLE_CHAIRMAN => [
            'finance.view',
            'approvals.manage',
            'projects.view',
        ],

        User::ROLE_MANAGING_DIRECTOR => [
            'finance.view',
            'approvals.manage',
            'projects.view',
        ],

        User::ROLE_DIRECTOR => [
            'finance.view',
            'approvals.manage',
            'projects.view',
        ],
    ],
];
