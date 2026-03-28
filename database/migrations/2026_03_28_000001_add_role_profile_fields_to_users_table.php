<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->nullable()->after('password');
            $table->string('phone')->nullable()->after('role');
            $table->string('profile_photo_path')->nullable()->after('phone');
            $table->string('digital_signature_path')->nullable()->after('profile_photo_path');
            $table->string('two_factor_type')->default('authenticator')->after('two_factor_confirmed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone', 'profile_photo_path', 'digital_signature_path', 'two_factor_type']);
        });
    }
};
