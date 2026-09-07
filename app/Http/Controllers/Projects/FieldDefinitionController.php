<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\FieldDefinition;
use App\Models\FieldValue;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FieldDefinitionController extends Controller
{
    /**
     * The registration form builder for one project.
     */
    public function index(Project $project)
    {
        $definitions = FieldDefinition::query()
            ->where('project_id', $project->id)
            ->ordered()
            ->withCount('values')
            ->get();

        $shared = FieldDefinition::query()
            ->whereNull('project_id')
            ->ordered()
            ->get();

        $types = FieldDefinition::TYPES;

        return view('pages.projects.fields.index', compact('project', 'definitions', 'shared', 'types'));
    }

    public function store(Request $request, Project $project)
    {
        $data = $this->validated($request, $project, null);

        $data['project_id'] = $project->id;
        $data['sort_order'] = (int) FieldDefinition::where('project_id', $project->id)->max('sort_order') + 1;

        $definition = FieldDefinition::create($data);

        return response()->json([
            'message' => 'Field added to the registration form.',
            'definition' => $definition,
        ], 201);
    }

    public function update(Request $request, Project $project, FieldDefinition $field)
    {
        $this->assertBelongsTo($project, $field);

        $data = $this->validated($request, $project, $field);

        // The key is the identity of every stored answer. Renaming it would
        // orphan them all, so it is frozen once answers exist.
        if ($field->values()->exists()) {
            unset($data['key']);
        }

        $field->update($data);

        return response()->json(['message' => 'Field updated.']);
    }

    public function destroy(Project $project, FieldDefinition $field)
    {
        $this->assertBelongsTo($project, $field);

        $answers = $field->values()->count();

        if ($answers > 0) {
            return response()->json([
                'message' => "This field has {$answers} stored answer(s). Set it to inactive instead of deleting it.",
            ], 422);
        }

        $field->delete();

        return response()->json(['message' => 'Field deleted.']);
    }

    /**
     * Persist a new drag-and-drop order.
     */
    public function reorder(Request $request, Project $project)
    {
        $data = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer'],
        ]);

        foreach (array_values($data['order']) as $position => $id) {
            FieldDefinition::where('project_id', $project->id)
                ->where('id', $id)
                ->update(['sort_order' => $position]);
        }

        return response()->json(['message' => 'Order saved.']);
    }

    private function validated(Request $request, Project $project, ?FieldDefinition $field): array
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:150'],
            'key' => [
                'nullable', 'string', 'max:50', 'regex:/^[a-z][a-z0-9_]*$/',
                Rule::unique('field_definitions', 'key')
                    ->where(fn ($q) => $q->where('project_id', $project->id))
                    ->ignore($field?->id),
            ],
            'type' => ['required', 'string', Rule::in(array_keys(FieldDefinition::TYPES))],
            'options' => ['nullable', 'array'],
            'options.*' => ['string', 'max:150'],
            'is_required' => ['boolean'],
            'help_text' => ['nullable', 'string', 'max:255'],
            'placeholder' => ['nullable', 'string', 'max:150'],
            'status' => ['required', 'string', Rule::in([FieldDefinition::STATUS_ACTIVE, FieldDefinition::STATUS_INACTIVE])],
        ], [
            'key.regex' => 'The key must be lowercase letters, numbers and underscores, starting with a letter.',
        ]);

        // Derive the key from the label when the user does not supply one.
        if (blank($data['key'] ?? null)) {
            $data['key'] = $this->uniqueKey($project, $data['label'], $field?->id);
        }

        $data['is_required'] = $request->boolean('is_required');

        $needsOptions = in_array($data['type'], FieldDefinition::CHOICE_TYPES, true);
        $options = array_values(array_filter(array_map('trim', $data['options'] ?? [])));

        if ($needsOptions && count($options) < 1) {
            abort(response()->json([
                'message' => 'This field type needs at least one choice.',
                'errors' => ['options' => ['Add at least one choice.']],
            ], 422));
        }

        $data['options'] = $needsOptions ? $options : null;

        return $data;
    }

    private function uniqueKey(Project $project, string $label, ?int $ignoreId): string
    {
        $base = Str::of($label)->lower()->slug('_')->limit(45, '')->value();
        $base = preg_replace('/^[^a-z]+/', '', $base) ?: 'field';

        $key = $base;
        $n = 2;

        while (FieldDefinition::where('project_id', $project->id)
            ->where('key', $key)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $key = $base.'_'.$n++;
        }

        return $key;
    }

    private function assertBelongsTo(Project $project, FieldDefinition $field): void
    {
        abort_unless($field->project_id === $project->id, 404);
    }
}
