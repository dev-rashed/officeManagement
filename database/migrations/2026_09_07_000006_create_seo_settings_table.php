<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Site-wide SEO defaults and the analytics / verification IDs.
     *
     * A single row, like contact_settings.
     */
    public function up(): void
    {
        Schema::create('seo_settings', function (Blueprint $table): void {
            $table->id();

            // Defaults used when a page has nothing of its own.
            $table->string('site_name')->nullable();
            $table->string('title_template')->default('{title} | {site}');
            $table->text('default_meta_description')->nullable();
            $table->string('default_og_image')->nullable();
            $table->string('canonical_base_url')->nullable();
            $table->string('twitter_handle', 50)->nullable();

            // Google tools.
            $table->string('google_analytics_id', 40)->nullable();
            $table->string('google_tag_manager_id', 40)->nullable();
            $table->string('google_site_verification', 120)->nullable();
            $table->string('bing_site_verification', 120)->nullable();

            // Organisation JSON-LD.
            $table->string('organization_type', 60)->default('Organization');
            $table->string('organization_logo')->nullable();
            $table->json('social_profiles')->nullable();

            // Escape hatches for anything not covered above.
            $table->text('custom_head_snippet')->nullable();
            $table->text('custom_body_snippet')->nullable();

            $table->text('robots_txt')->nullable();

            // Master switch. Off means every page emits noindex -- for staging.
            $table->boolean('is_indexable')->default(true);

            $table->timestamps();
        });

        DB::table('seo_settings')->insert([
            'site_name' => 'HashTag Research & Technology Ltd.',
            'title_template' => '{title} | {site}',
            'default_meta_description' => 'HashTag Research & Technology Ltd. delivers software development, web applications, AI solutions, digital marketing, and IT consulting.',
            'organization_type' => 'Organization',
            'is_indexable' => true,
            'robots_txt' => "User-agent: *\nAllow: /\n\nDisallow: /dashboard\nDisallow: /settings\nDisallow: /admin\n",
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_settings');
    }
};
