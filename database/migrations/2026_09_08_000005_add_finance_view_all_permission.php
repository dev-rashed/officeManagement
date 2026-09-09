<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Splits "see finance records" into two rights.
     *
     * Everyone can always see their own expenses -- that is not a permission,
     * it is a baseline. `finance.view_all` is what lets someone see everybody
     * else's, and it is granted to the roles that already had blanket
     * finance.view so nobody loses access.
     */
    public function up(): void
    {
        $now = now();

        $id = DB::table('permissions')->where('name', 'finance.view_all')->value('id');

        if (! $id) {
            $id = DB::table('permissions')->insertGetId([
                'name' => 'finance.view_all',
                'label' => "See everyone's income and expenses",
                'description' => 'Without this a user sees only the expenses they recorded or are owed for.',
                'group' => 'Finance',
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Anyone who could already see all finance keeps that reach.
        $roleIds = DB::table('roles')
            ->join('permission_role', 'permission_role.role_id', '=', 'roles.id')
            ->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
            ->where('permissions.name', 'finance.view')
            ->pluck('roles.id');

        foreach ($roleIds as $roleId) {
            $exists = DB::table('permission_role')
                ->where('role_id', $roleId)->where('permission_id', $id)->exists();

            if (! $exists) {
                DB::table('permission_role')->insert([
                    'role_id' => $roleId,
                    'permission_id' => $id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        $id = DB::table('permissions')->where('name', 'finance.view_all')->value('id');

        if ($id) {
            DB::table('permission_role')->where('permission_id', $id)->delete();
            DB::table('permissions')->where('id', $id)->delete();
        }
    }
};
