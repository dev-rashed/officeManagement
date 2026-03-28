<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approvals', function (Blueprint $table): void {
            $table->id();
            $table->morphs('approvable');
            $table->string('stage');
            $table->string('status');
            $table->text('comments')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->unique(['approvable_type', 'approvable_id', 'stage']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};
