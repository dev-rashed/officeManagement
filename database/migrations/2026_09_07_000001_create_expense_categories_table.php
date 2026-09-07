<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('code', 50)->nullable()->unique();
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index('status');
        });

        $now = now();

        $defaults = [
            ['name' => 'Office Rent', 'code' => 'OFF-RENT'],
            ['name' => 'Utilities', 'code' => 'UTIL'],
            ['name' => 'Salaries & Wages', 'code' => 'SAL'],
            ['name' => 'Training Materials', 'code' => 'TRN-MAT'],
            ['name' => 'Transport & Travel', 'code' => 'TRVL'],
            ['name' => 'Equipment Purchase', 'code' => 'EQP'],
            ['name' => 'Repairs & Maintenance', 'code' => 'MNT'],
            ['name' => 'Printing & Stationery', 'code' => 'STN'],
            ['name' => 'Marketing & Advertising', 'code' => 'MKT'],
            ['name' => 'Miscellaneous', 'code' => 'MISC'],
        ];

        DB::table('expense_categories')->insert(array_map(
            fn (array $row): array => $row + [
                'description' => null,
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            $defaults,
        ));
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_categories');
    }
};
