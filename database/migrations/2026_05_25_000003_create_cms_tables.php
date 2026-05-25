<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_sections', function (Blueprint $table) {
            $table->id();
            $table->string('page_slug', 50);
            $table->string('section_key', 50);
            $table->string('title', 255);
            $table->string('subtitle', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->string('button_text', 100)->nullable();
            $table->string('button_link', 255)->nullable();
            $table->boolean('visibility')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['page_slug', 'section_key']);
            $table->index('visibility');
            $table->index('sort_order');
        });

        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('position', 100);
            $table->text('bio')->nullable();
            $table->string('image_path')->nullable();
            $table->string('email', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->json('social_links')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('status');
            $table->index('sort_order');
        });

        Schema::create('portfolio_items', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->json('technologies')->nullable();
            $table->string('link', 255)->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('status');
            $table->index('slug');
            $table->index('sort_order');
        });

        Schema::create('contact_settings', function (Blueprint $table) {
            $table->id();
            $table->string('email', 100);
            $table->string('phone', 20)->nullable();
            $table->string('address', 255)->nullable();
            $table->json('social_links')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_sections');
        Schema::dropIfExists('team_members');
        Schema::dropIfExists('portfolio_items');
        Schema::dropIfExists('contact_settings');
    }
};
