<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class UserPermissionTest extends TestCase
{
    public function test_permissions_are_loaded_from_role_configuration(): void
    {
        $user = new User(['role' => User::ROLE_ACCOUNTANT]);

        $this->assertTrue($user->hasPermission('finance.manage'));
        $this->assertFalse($user->hasPermission('users.manage'));
    }

    public function test_superadmin_has_wildcard_access(): void
    {
        $user = new User(['role' => User::ROLE_SUPERADMIN]);

        $this->assertTrue($user->hasPermission('finance.view'));
        $this->assertTrue($user->hasPermission('users.manage'));
        $this->assertTrue($user->hasAnyPermission(['missing.permission', 'finance.manage']));
    }

    public function test_user_without_role_has_no_permissions(): void
    {
        $user = new User();

        $this->assertSame([], $user->permissions());
        $this->assertFalse($user->hasPermission('finance.view'));
    }
}
