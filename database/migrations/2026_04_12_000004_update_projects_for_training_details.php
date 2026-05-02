<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (! Schema::hasColumn('projects', 'targeted_trainees')) {
                $table->unsignedInteger('targeted_trainees')->nullable()->after('end_date');
            }

            if (! Schema::hasColumn('projects', 'completed_trainees')) {
                $table->unsignedInteger('completed_trainees')->default(0)->after('targeted_trainees');
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'completed_trainees')) {
                $table->dropColumn('completed_trainees');
            }

            if (Schema::hasColumn('projects', 'targeted_trainees')) {
                $table->dropColumn('targeted_trainees');
            }
        });
    }
};
