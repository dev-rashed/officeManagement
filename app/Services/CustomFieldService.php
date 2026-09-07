<?php

namespace App\Services;

use App\Models\FieldDefinition;
use App\Models\FieldValue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * The one place custom fields are turned into validation rules and back into
 * stored values.
 *
 * The registration form, the student importer and the API all go through here,
 * so a field defined once behaves the same everywhere.
 */
class CustomFieldService
{
    /** Form inputs are namespaced so they cannot collide with core columns. */
    public const INPUT_PREFIX = 'custom_fields';

    /**
     * The active definitions that apply to a project, in display order.
     *
     * @return Collection<int, FieldDefinition>
     */
    public function definitionsFor(?int $projectId): Collection
    {
        return FieldDefinition::query()
            ->forProject($projectId)
            ->active()
            ->ordered()
            ->get();
    }

    /**
     * Validation rules for those definitions, keyed by input name.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rulesFor(?int $projectId): array
    {
        $rules = [];

        foreach ($this->definitionsFor($projectId) as $definition) {
            $name = $this->inputName($definition);
            $set = [$definition->is_required ? 'required' : 'nullable'];

            switch ($definition->type) {
                case FieldDefinition::TYPE_NUMBER:
                    $set[] = 'numeric';
                    break;

                case FieldDefinition::TYPE_DATE:
                    $set[] = 'date';
                    break;

                case FieldDefinition::TYPE_CHECKBOX:
                    $set[] = 'boolean';
                    break;

                case FieldDefinition::TYPE_MULTISELECT:
                    $set[] = 'array';
                    $rules[$name.'.*'] = ['string', 'in:'.implode(',', $definition->options ?? [])];
                    break;

                case FieldDefinition::TYPE_SELECT:
                case FieldDefinition::TYPE_RADIO:
                    $set[] = 'string';
                    $set[] = 'in:'.implode(',', $definition->options ?? []);
                    break;

                default:
                    $set[] = 'string';
                    $set[] = 'max:5000';
            }

            // Anything the definition adds on top, e.g. {"max": 11}.
            foreach ($definition->validation ?? [] as $rule => $parameter) {
                $set[] = $parameter === true || $parameter === null
                    ? $rule
                    : $rule.':'.(is_array($parameter) ? implode(',', $parameter) : $parameter);
            }

            $rules[$name] = $set;
        }

        return $rules;
    }

    /**
     * Human-readable names so errors say "Household income", not
     * "custom_fields.7".
     *
     * @return array<string, string>
     */
    public function attributeNamesFor(?int $projectId): array
    {
        $names = [];

        foreach ($this->definitionsFor($projectId) as $definition) {
            $names[$this->inputName($definition)] = $definition->label;
        }

        return $names;
    }

    /**
     * Persist the submitted answers against a record.
     *
     * Definitions not present in the payload are left untouched rather than
     * wiped, so a partial form never silently deletes stored answers.
     */
    public function save(Model $record, ?int $projectId, array $submitted): void
    {
        $definitions = $this->definitionsFor($projectId);

        if ($definitions->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($record, $definitions, $submitted): void {
            foreach ($definitions as $definition) {
                if (! array_key_exists($definition->id, $submitted)) {
                    continue;
                }

                $value = $this->normalise($definition, $submitted[$definition->id]);

                FieldValue::updateOrCreate(
                    [
                        'field_definition_id' => $definition->id,
                        'fieldable_id' => $record->getKey(),
                        'fieldable_type' => $record->getMorphClass(),
                    ],
                    ['value' => $value],
                );
            }
        });
    }

    /**
     * Stored answers for a record, keyed by definition id.
     *
     * @return array<int, string|null>
     */
    public function valuesFor(Model $record): array
    {
        return FieldValue::query()
            ->where('fieldable_id', $record->getKey())
            ->where('fieldable_type', $record->getMorphClass())
            ->pluck('value', 'field_definition_id')
            ->all();
    }

    /**
     * Definitions paired with their stored value, for a detail page or export.
     *
     * @return array<int, array{definition: FieldDefinition, value: FieldValue|null}>
     */
    public function answeredFor(Model $record, ?int $projectId): array
    {
        $values = FieldValue::query()
            ->with('definition')
            ->where('fieldable_id', $record->getKey())
            ->where('fieldable_type', $record->getMorphClass())
            ->get()
            ->keyBy('field_definition_id');

        $out = [];

        foreach ($this->definitionsFor($projectId) as $definition) {
            $out[] = [
                'definition' => $definition,
                'value' => $values->get($definition->id),
            ];
        }

        return $out;
    }

    public function inputName(FieldDefinition $definition): string
    {
        return self::INPUT_PREFIX.'.'.$definition->id;
    }

    /**
     * Turn a submitted value into the string that goes in the value column.
     */
    private function normalise(FieldDefinition $definition, mixed $value): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        return match ($definition->type) {
            FieldDefinition::TYPE_MULTISELECT => json_encode(array_values((array) $value)),
            FieldDefinition::TYPE_CHECKBOX => filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0',
            default => (string) $value,
        };
    }
}
