<?php

namespace App\Http\Controllers;

use App\Models\Trainee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TraineeController extends Controller
{
    public function index()
    {
        $totalTrainees = Trainee::count();
        $withPhoto = Trainee::whereNotNull('photo_path')->count();
        $withEmail = Trainee::whereNotNull('email')->count();

        return view('pages.projects.trainees.index', compact(
            'totalTrainees',
            'withPhoto',
            'withEmail',
        ));
    }

    public function data(Request $request)
    {
        $query = Trainee::query()->latest();
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

        return response()->json(['message' => 'Trainee updated successfully.']);
    }

    public function destroy(Trainee $trainee)
    {
        Storage::disk('public')->delete($trainee->photo_path);
        $trainee->delete();

        return response()->json(['message' => 'Trainee deleted successfully.']);
    }

    private function validatedData(Request $request, ?Trainee $trainee = null): array
    {
        return $request->validate([
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
        ]);
    }
}
