<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-page SEO overrides.
     *
     * A row belongs to EITHER a model (a course, a service, a portfolio item)
     * through the morph columns, OR to a static route through page_key --
     * never both. Static pages such as /about have no model to hang meta off,
     * so the alternative would be a second table saying the same thing.
     */
    public function up(): void
    {
        Schema::create('seo_meta', function (Blueprint $table): void {
            $table->id();

            $table->nullableMorphs('seoable');
            $table->string('page_key', 60)->nullable()->unique();

            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();

            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image')->nullable();

            $table->string('canonical_url')->nullable();
            $table->boolean('noindex')->default(false);
            $table->boolean('nofollow')->default(false);

            $table->string('schema_type', 60)->nullable();

            $table->timestamps();

            $table->unique(['seoable_type', 'seoable_id'], 'seo_meta_seoable_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_meta');
    }
};
