<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per public page view.
     *
     * No raw IP is stored. The visitor is identified by a salted hash so the
     * same person can be counted once across a day without the table holding
     * anything that identifies them.
     */
    public function up(): void
    {
        Schema::create('page_views', function (Blueprint $table): void {
            $table->id();

            $table->string('path', 255);
            $table->string('route_name', 100)->nullable();
            $table->string('page_key', 60)->nullable();
            $table->string('title', 255)->nullable();

            // Which course / service / portfolio item was being looked at.
            $table->nullableMorphs('viewable');

            $table->string('visitor_hash', 64)->nullable();
            $table->string('session_hash', 64)->nullable();

            $table->string('referrer_host', 150)->nullable();
            $table->string('referrer_url', 500)->nullable();
            $table->string('utm_source', 100)->nullable();
            $table->string('utm_medium', 100)->nullable();
            $table->string('utm_campaign', 100)->nullable();

            $table->string('device', 10)->nullable();   // desktop | mobile | tablet
            $table->string('browser', 40)->nullable();
            $table->string('platform', 40)->nullable();

            $table->boolean('is_bot')->default(false);

            $table->timestamp('viewed_at')->index();
            $table->timestamps();

            // The dashboard groups by day, by path and by visitor.
            $table->index(['viewed_at', 'is_bot'], 'page_views_time_bot_index');
            $table->index(['path', 'viewed_at'], 'page_views_path_time_index');
            $table->index(['visitor_hash', 'viewed_at'], 'page_views_visitor_time_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_views');
    }
};
