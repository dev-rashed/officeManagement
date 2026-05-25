<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('code_tag_number', 100)->unique();
            $table->string('category', 100);
            $table->string('brand', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 10, 2)->nullable();
            $table->decimal('current_value', 10, 2)->nullable();
            $table->string('vendor_supplier', 255)->nullable();
            $table->string('location', 100)->nullable();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('condition', ['excellent', 'good', 'fair', 'poor']);
            $table->enum('status', ['active', 'in_repair', 'retired', 'lost', 'disposed'])->default('active');
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable();
            $table->timestamps();

            $table->index('category');
            $table->index('status');
            $table->index('location');
            $table->index('assigned_user_id');
            $table->index('purchase_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
