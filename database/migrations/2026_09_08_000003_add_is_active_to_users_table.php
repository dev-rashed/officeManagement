<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lets an account be switched off without deleting it.
     *
     * Deleting a user would orphan everything they created -- expenses,
     * approvals, students. Disabling keeps the history intact while stopping
     * them signing in.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('is_active')->default(true)->after('is_protected');
            $table->timestamp('disabled_at')->nullable()->after('is_active');
            $table->foreignId('disabled_by')->nullable()->after('disabled_at')
                ->constrained('users')->nullOnDelete();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('disabled_by');
            $table->dropColumn(['is_active', 'disabled_at']);
        });
    }
};
