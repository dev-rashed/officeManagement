<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Where the money came from.
     *
     * Income either comes out of a project, or is put in personally by a
     * member of the board -- a Managing Director, Director or Chairman funding
     * the office. Anything else stays as 'other'.
     */
    public function up(): void
    {
        Schema::table('income_entries', function (Blueprint $table): void {
            $table->string('source_type', 20)->default('other')->after('source_category');

            $table->foreignId('project_id')->nullable()->after('source_type')
                ->constrained('projects')->nullOnDelete();

            // The person who personally put the money in.
            $table->foreignId('contributor_id')->nullable()->after('project_id')
                ->constrained('users')->nullOnDelete();

            $table->index(['source_type', 'date'], 'income_source_date_index');
            $table->index('contributor_id');
            $table->index('project_id');
        });
    }

    public function down(): void
    {
        Schema::table('income_entries', function (Blueprint $table): void {
            $table->dropIndex('income_source_date_index');
            $table->dropIndex(['contributor_id']);
            $table->dropIndex(['project_id']);
            $table->dropConstrainedForeignId('project_id');
            $table->dropConstrainedForeignId('contributor_id');
            $table->dropColumn('source_type');
        });
    }
};
