<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Marks an account that must survive everything.
     *
     * A protected account cannot be deleted and cannot be demoted out of
     * superadmin, so there is always a way back into the system even if
     * somebody deletes every other user or strips every role.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('is_protected')->default(false)->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('is_protected');
        });
    }
};
