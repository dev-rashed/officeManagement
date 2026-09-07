<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['field_definition_id', 'fieldable_id', 'fieldable_type', 'value'])]
class FieldValue extends Model
{
    use HasFactory;

    public function definition(): BelongsTo
    {
        return $this->belongsTo(FieldDefinition::class, 'field_definition_id');
    }

    public function fieldable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The stored string turned back into something usable, according to the
     * definition's type.
     */
    public function typed(): mixed
    {
        $definition = $this->definition;

        if (! $definition || $this->value === null) {
            return null;
        }

        return match ($definition->type) {
            FieldDefinition::TYPE_MULTISELECT => json_decode($this->value, true) ?: [],
            FieldDefinition::TYPE_CHECKBOX => $this->value === '1',
            FieldDefinition::TYPE_NUMBER => is_numeric($this->value) ? $this->value + 0 : null,
            default => $this->value,
        };
    }

    /**
     * How the value should read on a detail page or in an export.
     */
    public function display(): string
    {
        $definition = $this->definition;

        if (! $definition || $this->value === null || $this->value === '') {
            return '—';
        }

        return match ($definition->type) {
            FieldDefinition::TYPE_MULTISELECT => implode(', ', json_decode($this->value, true) ?: []),
            FieldDefinition::TYPE_CHECKBOX => $this->value === '1' ? 'Yes' : 'No',
            default => (string) $this->value,
        };
    }
}
