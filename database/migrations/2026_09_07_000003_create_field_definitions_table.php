<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('field_definitions', function (Blueprint $table): void {
            $table->id();

            // Null means the field applies to every project, not just one.
            $table->foreignId('project_id')
                ->nullable()
                ->constrained('projects')
                ->cascadeOnDelete();

            $table->string('key', 50);
            $table->string('label', 150);
            $table->string('type', 20)->default('text');
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->json('validation')->nullable();
            $table->string('help_text', 255)->nullable();
            $table->string('placeholder', 150)->nullable();
            $table->integer('sort_order')->default(0);
            $table->string('status', 20)->default('active');
            $table->timestamps();

            // A key is unique within its project. Two projects may both define
            // "household_income" and they are different fields.
            $table->unique(['project_id', 'key']);
            $table->index(['project_id', 'status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('field_definitions');
    }
};
