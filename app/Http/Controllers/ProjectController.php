<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectCategory;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $totalProjects = Project::count();
        $activeProjects = Project::where('status', Project::STATUS_ACTIVE)->count();
        $completedProjects = Project::where('status', Project::STATUS_COMPLETED)->count();
        $categories = ProjectCategory::where('status', ProjectCategory::STATUS_ACTIVE)->orderBy('name')->get();

        return view('pages.projects.index', compact(
            'totalProjects',
            'activeProjects',
            'completedProjects',
            'categories',
        ));
    }

    public function data(Request $request)
    {
        $query = Project::query()->with('category')->latest('start_date');
        $totalRecords = Project::count();

        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($categoryQuery) use ($search) {
                        $categoryQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $filteredRecords = $query->count();
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 20);

        $projects = $query->skip($start)->take($length)->get();

        $data = $projects->map(function (Project $project) {
            $statusClass = match ($project->status) {
                Project::STATUS_ACTIVE => 'is-active',
                Project::STATUS_COMPLETED => 'is-completed',
                Project::STATUS_CANCELLED => 'is-danger',
                default => 'is-pending',
            };

            $actions = '<div class="project-actions">';
            $actions .= '<button type="button" class="project-action-btn is-primary project-edit-btn"'
                .' data-project-id="'.$project->id.'"'
                .' data-title="'.e($project->title).'"'
                .' data-project-category-id="'.($project->project_category_id ?? '').'"'
                .' data-status="'.$project->status.'"'
                .' data-start-date="'.optional($project->start_date)->format('Y-m-d\TH:i').'"'
                .' data-end-date="'.optional($project->end_date)->format('Y-m-d\TH:i').'"'
                .' data-targeted-trainees="'.e((string) ($project->targeted_trainees ?? '')).'"'
                .' data-completed-trainees="'.e((string) ($project->completed_trainees ?? 0)).'"'
                .' data-description="'.e($project->description ?? '').'"'
                .' data-notes="'.e($project->notes ?? '').'"'
                .'>Edit</button>';
            $actions .= '<button type="button" class="project-action-btn is-danger project-delete-btn" data-destroy-url="'.route('projects.destroy', $project).'">Delete</button>';
            $actions .= '</div>';

            return [
                'title' => '<span class="project-title-text">'.e($project->title).'</span>',
                'category' => '<span class="project-subtle-text">'.e($project->category?->name ?? 'Uncategorized').'</span>',
                'status' => '<span class="project-status-badge '.$statusClass.'">'.e($project->statusLabel()).'</span>',
                'start_date' => optional($project->start_date)->format('d M Y, h:i A') ?? '&mdash;',
                'end_date' => optional($project->end_date)->format('d M Y, h:i A') ?? '&mdash;',
                'trainees' => '<span class="project-subtle-text">'.number_format($project->completed_trainees ?? 0).' / '.($project->targeted_trainees !== null ? number_format($project->targeted_trainees) : 'Target open').'</span>',
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

        $project = Project::create($data);

        return response()->json([
            'message' => 'Project created successfully.',
            'project' => $project,
        ], 201);
    }

    public function update(Request $request, Project $project)
    {
        $data = $this->validatedData($request, $project);
        $project->update($data);

        return response()->json(['message' => 'Project updated successfully.']);
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return response()->json(['message' => 'Project deleted successfully.']);
    }

    private function validatedData(Request $request, ?Project $project = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'project_category_id' => ['nullable', 'integer', 'exists:project_categories,id'],
            'status' => ['required', 'string', 'in:planning,active,on_hold,completed,cancelled'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'targeted_trainees' => ['nullable', 'integer', 'min:0'],
            'completed_trainees' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
