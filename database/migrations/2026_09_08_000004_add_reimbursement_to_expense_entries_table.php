<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Who actually paid for an expense, and whether the office has settled up.
     *
     * Anyone can record money they spent from their own pocket; finance then
     * marks it reimbursed once the office pays them back. An expense paid
     * straight from an office account needs no reimbursement at all.
     */
    public function up(): void
    {
        Schema::table('expense_entries', function (Blueprint $table): void {
            // 'office' = paid from a company account, 'personal' = someone
            // spent their own money and is owed it back.
            $table->string('payment_source', 20)->default('office')->after('payment_method');

            // The person owed the money. Not always whoever typed the entry.
            $table->foreignId('paid_by')->nullable()->after('payment_source')
                ->constrained('users')->nullOnDelete();

            $table->string('reimbursement_status', 20)->default('not_required')->after('paid_by');
            $table->timestamp('reimbursed_at')->nullable()->after('reimbursement_status');
            $table->foreignId('reimbursed_by')->nullable()->after('reimbursed_at')
                ->constrained('users')->nullOnDelete();
            $table->string('reimbursement_note', 255)->nullable()->after('reimbursed_by');

            $table->index(['payment_source', 'reimbursement_status'], 'expense_reimbursement_index');
            $table->index('paid_by');
        });

        // Everything recorded so far predates this idea, so it stays as an
        // office payment needing no reimbursement -- which is what the defaults
        // already say. Nothing to backfill.
    }

    public function down(): void
    {
        Schema::table('expense_entries', function (Blueprint $table): void {
            $table->dropIndex('expense_reimbursement_index');
            $table->dropIndex(['paid_by']);
            $table->dropConstrainedForeignId('paid_by');
            $table->dropConstrainedForeignId('reimbursed_by');
            $table->dropColumn(['payment_source', 'reimbursement_status', 'reimbursed_at', 'reimbursement_note']);
        });
    }
};
