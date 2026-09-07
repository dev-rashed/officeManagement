<?php

namespace App\Http\Controllers;

use App\Models\FieldDefinition;
use App\Models\Project;
use App\Models\Trainee;
use App\Services\CustomFieldService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TraineeController extends Controller
{
    public function index()
    {
        $totalTrainees = Trainee::count();
        $withPhoto = Trainee::whereNotNull('photo_path')->count();
        $withEmail = Trainee::whereNotNull('email')->count();

        $projects = Project::orderBy('title')->get(['id', 'title']);

        return view('pages.projects.trainees.index', compact(
            'totalTrainees',
            'withPhoto',
            'withEmail',
            'projects',
        ));
    }

    public function data(Request $request)
    {
        $query = Trainee::query()->with('fieldValues')->latest();
        $totalRecords = Trainee::count();

        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('nid', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $filteredRecords = $query->count();
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 20);

        $trainees = $query->skip($start)->take($length)->get();

        $data = $trainees->map(function (Trainee $trainee) {
            $photo = $trainee->photo_path
                ? '<img src="'.e(Storage::url($trainee->photo_path)).'" alt="" class="trainee-photo">'
                : '<span class="trainee-photo is-empty">'.e(strtoupper(substr($trainee->first_name, 0, 1))).'</span>';

            $actions = '<div class="trainee-actions">';
            $actions .= '<button type="button" class="trainee-action-btn is-primary trainee-edit-btn"'
                .' data-trainee-id="'.$trainee->id.'"'
                .' data-first-name="'.e($trainee->first_name).'"'
                .' data-last-name="'.e($trainee->last_name ?? '').'"'
                .' data-nid="'.e($trainee->nid).'"'
                .' data-email="'.e($trainee->email ?? '').'"'
                .' data-phone="'.e($trainee->phone ?? '').'"'
                .' data-date-of-birth="'.optional($trainee->date_of_birth)->format('Y-m-d').'"'
                .' data-father-name="'.e($trainee->father_name ?? '').'"'
                .' data-mother-name="'.e($trainee->mother_name ?? '').'"'
                .' data-emergency-contact-number="'.e($trainee->emergency_contact_number ?? '').'"'
                .' data-project-id="'.e((string) $trainee->project_id).'"'
                .' data-custom-values="'.e($this->customValuesJson($trainee)).'"'
                .'>Edit</button>';
            $actions .= '<button type="button" class="trainee-action-btn is-danger trainee-delete-btn" data-destroy-url="'.route('projects.trainees.destroy', $trainee).'">Delete</button>';
            $actions .= '</div>';

            return [
                'name' => '<div class="trainee-name-cell">'.$photo.'<div><span class="trainee-name-text">'.e($trainee->fullName()).'</span><div class="trainee-subtle-text">NID: '.e($trainee->nid).'</div></div></div>',
                'contact' => '<span class="trainee-subtle-text">'.e($trainee->phone ?: 'No phone').'<br>'.e($trainee->email ?: 'No email').'</span>',
                'date_of_birth' => optional($trainee->date_of_birth)->format('d M Y') ?? '&mdash;',
                'parents' => '<span class="trainee-subtle-text">Father: '.e($trainee->father_name ?: 'N/A').'<br>Mother: '.e($trainee->mother_name ?: 'N/A').'</span>',
                'emergency_contact' => '<span class="trainee-subtle-text">'.e($trainee->emergency_contact_number ?: 'N/A').'</span>',
                'actions' => $actions,
            ];
        });

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $data['created_by'] = $request->user()?->id;

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('uploads/trainees', 'public');
        }

        $trainee = Trainee::create($data);

        app(CustomFieldService::class)->save(
            $trainee,
            $trainee->project_id,
            $request->input(CustomFieldService::INPUT_PREFIX, []),
        );

        return response()->json([
            'message' => 'Trainee created successfully.',
            'trainee' => $trainee,
        ], 201);
    }

    public function update(Request $request, Trainee $trainee)
    {
        $data = $this->validatedData($request, $trainee);

        if ($request->hasFile('photo')) {
            Storage::disk('public')->delete($trainee->photo_path);
            $data['photo_path'] = $request->file('photo')->store('uploads/trainees', 'public');
        }

        $trainee->update($data);

        app(CustomFieldService::class)->save(
            $trainee,
            $trainee->project_id,
            $request->input(CustomFieldService::INPUT_PREFIX, []),
        );

        return response()->json(['message' => 'Trainee updated successfully.']);
    }

    public function destroy(Trainee $trainee)
    {
        Storage::disk('public')->delete($trainee->photo_path);
        $trainee->delete();

        return response()->json(['message' => 'Trainee deleted successfully.']);
    }

    /**
     * Stored custom answers keyed by definition id, ready for the edit modal.
     *
     * Multi-select answers are stored as a JSON array and must come back as an
     * array; every other type is a plain string and is returned unchanged.
     */
    private function customValuesJson(Trainee $trainee): string
    {
        $values = [];

        foreach ($trainee->fieldValues as $fieldValue) {
            $raw = $fieldValue->value;
            $decoded = is_string($raw) ? json_decode($raw, true) : null;

            $values[$fieldValue->field_definition_id] = is_array($decoded) ? $decoded : $raw;
        }

        return json_encode($values, JSON_UNESCAPED_UNICODE) ?: '{}';
    }

    /**
     * The custom fields a project asks for, so the registration modal can
     * rebuild itself when the project selector changes.
     */
    public function fields(Request $request, CustomFieldService $fields)
    {
        $request->validate(['project_id' => ['nullable', 'integer', 'exists:projects,id']]);

        $projectId = $request->integer('project_id') ?: null;

        return response()->json([
            'fields' => $fields->definitionsFor($projectId)->map(fn (FieldDefinition $d) => [
                'id' => $d->id,
                'key' => $d->key,
                'label' => $d->label,
                'type' => $d->type,
                'options' => $d->options ?? [],
                'is_required' => $d->is_required,
                'help_text' => $d->help_text,
                'placeholder' => $d->placeholder,
                'is_shared' => $d->project_id === null,
            ])->values(),
        ]);
    }

    private function validatedData(Request $request, ?Trainee $trainee = null): array
    {
        $fields = app(CustomFieldService::class);
        $projectId = $request->integer('project_id') ?: null;

        $rules = [
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'nid' => ['required', 'string', 'max:255', Rule::unique('trainees', 'nid')->ignore($trainee)],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_number' => ['nullable', 'string', 'max:255'],
        ];

        // Custom field rules come from the chosen project's definitions, so the
        // form and the (future) importer validate identically.
        $validated = $request->validate(
            $rules + $fields->rulesFor($projectId),
            [],
            $fields->attributeNamesFor($projectId),
        );

        // Keep only the core columns -- custom answers are stored separately.
        return Arr::except($validated, [CustomFieldService::INPUT_PREFIX]);
    }
}
