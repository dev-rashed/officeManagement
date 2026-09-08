<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Moves roles and permissions out of code and into the database.
     *
     * `users.role` deliberately stays a slug string rather than becoming a
     * foreign key: every existing helper (isChairman(), hasRole(), the
     * approval chain) keeps working untouched, and a role can be renamed for
     * display without rewriting every user row.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 50)->unique();      // the slug stored on users.role
            $table->string('label', 80);
            $table->string('description', 255)->nullable();

            // Superadmin holds every permission implicitly and cannot be
            // edited or deleted, or an admin could lock the owner out.
            $table->boolean('is_superadmin')->default(false);

            // System roles can be edited but not deleted or renamed, because
            // code refers to their slugs.
            $table->boolean('is_system')->default(false);

            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 60)->unique();      // e.g. finance.manage
            $table->string('label', 100);
            $table->string('description', 255)->nullable();
            $table->string('group', 50)->default('General');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('permission_role', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['role_id', 'permission_id']);
        });

        $this->seed();
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }

    /**
     * Seeds from the existing constants and config so nobody's access changes
     * the moment this runs.
     */
    private function seed(): void
    {
        $now = now();

        $roleMeta = [
            User::ROLE_SUPERADMIN => ['Superadmin', 'Full access, including roles and permissions', true, true],
            User::ROLE_ADMIN => ['Admin', 'Runs the system day to day', false, true],
            User::ROLE_ACCOUNTANT => ['Accountant', 'Finance, CMS, assets and students', false, true],
            User::ROLE_CHAIRMAN => ['Chairman', 'Third and final approval stage', false, true],
            User::ROLE_MANAGING_DIRECTOR => ['Managing Director', 'First approval stage', false, true],
            User::ROLE_DIRECTOR => ['Director', 'Second approval stage', false, true],
        ];

        $order = 0;

        foreach ($roleMeta as $slug => [$label, $description, $isSuper, $isSystem]) {
            DB::table('roles')->insert([
                'name' => $slug,
                'label' => $label,
                'description' => $description,
                'is_superadmin' => $isSuper,
                'is_system' => $isSystem,
                'sort_order' => $order++,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $permissionMeta = [
            'finance.view' => ['See income and expenses', 'Finance'],
            'finance.manage' => ['Create and edit income and expenses', 'Finance'],
            'finance.categories.manage' => ['Manage income and expense categories', 'Finance'],
            'approvals.manage' => ['Act on the approval chain', 'Finance'],
            'projects.view' => ['See projects and categories', 'Projects'],
            'projects.manage' => ['Create and edit projects, categories and registration forms', 'Projects'],
            'students.manage' => ['Register and edit student records', 'Projects'],
            'cms.manage' => ['Manage website content, SEO and analytics', 'Website'],
            'assets.manage' => ['Manage the asset register', 'Operations'],
            'activity.view' => ['Read the audit trail', 'Administration'],
            'users.manage' => ['Create and edit user accounts', 'Administration'],
            'settings.manage' => ['Change system settings and notification rules', 'Administration'],
        ];

        $order = 0;

        foreach ($permissionMeta as $name => [$label, $group]) {
            DB::table('permissions')->insert([
                'name' => $name,
                'label' => $label,
                'group' => $group,
                'sort_order' => $order++,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Attach exactly what config/permissions.php grants today.
        $roleIds = DB::table('roles')->pluck('id', 'name');
        $permissionIds = DB::table('permissions')->pluck('id', 'name');
        $rows = [];

        foreach (config('permissions.roles', []) as $roleSlug => $granted) {
            if (! isset($roleIds[$roleSlug]) || in_array('*', $granted, true)) {
                continue; // Superadmin is implicit, never a stored list.
            }

            foreach ($granted as $permission) {
                if (! isset($permissionIds[$permission])) {
                    continue;
                }

                $rows[] = [
                    'role_id' => $roleIds[$roleSlug],
                    'permission_id' => $permissionIds[$permission],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if ($rows) {
            DB::table('permission_role')->insert($rows);
        }
    }
};
