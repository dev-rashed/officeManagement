<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('field_values', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('field_definition_id')
                ->constrained('field_definitions')
                ->cascadeOnDelete();

            // Polymorphic so the same engine can later carry custom fields for
            // assets or expenses without a second implementation.
            $table->morphs('fieldable');

            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(
                ['field_definition_id', 'fieldable_id', 'fieldable_type'],
                'field_values_definition_owner_unique',
            );
        });

        // Reporting filters hit `value`, which is TEXT -- MySQL requires a
        // prefix length to index it, which the schema builder cannot express.
        if (DB::getDriverName() === 'mysql') {
            DB::statement('CREATE INDEX field_values_value_index ON field_values (value(191))');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('field_values');
    }
};
