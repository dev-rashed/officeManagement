<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Replace the free-text expense_category column with a foreign key.
     *
     * Every distinct value already stored is promoted to a real category row
     * first, so no existing entry loses its category.
     */
    public function up(): void
    {
        Schema::table('expense_entries', function (Blueprint $table): void {
            $table->foreignId('expense_category_id')
                ->nullable()
                ->after('title')
                ->constrained('expense_categories')
                ->nullOnDelete();
        });

        if (Schema::hasColumn('expense_entries', 'expense_category')) {
            $this->backfill();

            Schema::table('expense_entries', function (Blueprint $table): void {
                $table->dropColumn('expense_category');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('expense_entries', 'expense_category')) {
            Schema::table('expense_entries', function (Blueprint $table): void {
                $table->string('expense_category')->nullable()->after('title');
            });

            DB::table('expense_entries')
                ->join('expense_categories', 'expense_categories.id', '=', 'expense_entries.expense_category_id')
                ->update(['expense_entries.expense_category' => DB::raw('expense_categories.name')]);
        }

        Schema::table('expense_entries', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('expense_category_id');
        });
    }

    /**
     * Promote every distinct legacy string to a category, then point the
     * entries at it.
     */
    private function backfill(): void
    {
        $names = DB::table('expense_entries')
            ->whereNotNull('expense_category')
            ->where('expense_category', '!=', '')
            ->distinct()
            ->pluck('expense_category');

        $now = now();

        foreach ($names as $name) {
            $name = trim($name);

            if ($name === '') {
                continue;
            }

            $categoryId = DB::table('expense_categories')->where('name', $name)->value('id');

            if (! $categoryId) {
                $categoryId = DB::table('expense_categories')->insertGetId([
                    'name' => $name,
                    'code' => null,
                    'description' => null,
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            DB::table('expense_entries')
                ->where('expense_category', $name)
                ->update(['expense_category_id' => $categoryId]);
        }
    }
};
