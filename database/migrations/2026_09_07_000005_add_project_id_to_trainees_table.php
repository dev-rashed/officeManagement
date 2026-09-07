<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The project a trainee was registered under.
     *
     * This decides which set of custom registration fields their form shows.
     * Nullable, because trainees registered before this existed have no
     * project, and because a trainee can later take part in other projects --
     * this records where they came in, not the only project they may join.
     */
    public function up(): void
    {
        Schema::table('trainees', function (Blueprint $table): void {
            $table->foreignId('project_id')
                ->nullable()
                ->after('created_by')
                ->constrained('projects')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('trainees', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('project_id');
        });
    }
};
