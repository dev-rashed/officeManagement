<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('status')->default('published');
            $table->timestamps();
        });

        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('short_description');
            $table->longText('full_description')->nullable();
            $table->string('featured_image')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('course_categories')->onDelete('set null');
            $table->string('duration')->nullable();
            $table->string('class_type')->default('offline');
            $table->string('location')->nullable();
            $table->string('schedule_batch')->nullable();
            $table->string('fee')->nullable();
            $table->string('instructor_name')->nullable();
            $table->longText('course_outline')->nullable();
            $table->integer('sort_order')->default(0);
            $table->string('status')->default('draft');
            $table->timestamps();

            $table->index('status');
            $table->index('slug');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
        Schema::dropIfExists('course_categories');
    }
};
