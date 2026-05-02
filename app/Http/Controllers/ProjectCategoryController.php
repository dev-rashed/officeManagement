<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectCategory;
use Illuminate\Http\Request;

class ProjectCategoryController extends Controller
{
    public function index()
    {
        $totalCategories = ProjectCategory::count();
        $activeCategories = ProjectCategory::where('status', ProjectCategory::STATUS_ACTIVE)->count();
        $inactiveCategories = ProjectCategory::where('status', ProjectCategory::STATUS_INACTIVE)->count();

        return view('pages.projects.categories.index', compact(
            'totalCategories',
            'activeCategories',
            'inactiveCategories',
        ));
    }

    public function data(Request $request)
    {
        $query = ProjectCategory::query()->orderBy('name');
        $totalRecords = ProjectCategory::count();

        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        $filteredRecords = $query->count();
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 20);

        $categories = $query->skip($start)->take($length)->get();

        $data = $categories->map(function (ProjectCategory $category) {
            $projectCount = Project::where('project_category_id', $category->id)->count();

            $statusBadge = '<span class="category-status-badge '
                .($category->isActive() ? 'is-active' : 'is-inactive')
                .'"><span class="category-status-dot"></span>'.ucfirst($category->status).'</span>';

            $actions = '<div class="category-actions">';
            $actions .= '<button type="button" class="category-action-btn is-primary category-edit-btn"'
                .' data-category-id="'.$category->id.'"'
                .' data-category-name="'.e($category->name).'"'
                .' data-category-status="'.$category->status.'"'
                .'>Edit</button>';
            $actions .= '<button type="button" class="category-action-btn is-danger category-delete-btn"'
                .' data-destroy-url="'.route('projects.categories.destroy', $category).'"'
                .'>Delete</button>';
            $actions .= '</div>';

            return [
                'name' => '<span class="category-name-text">'.e($category->name).'</span>',
                'status' => $statusBadge,
                'projects_count' => number_format($projectCount),
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
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:project_categories,name'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        $category = ProjectCategory::create($data);

        return response()->json([
            'message' => 'Project category created successfully.',
            'category' => $category,
        ], 201);
    }

    public function update(Request $request, ProjectCategory $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:project_categories,name,'.$category->id],
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        $category->update($data);

        return response()->json(['message' => 'Project category updated successfully.']);
    }

    public function destroy(ProjectCategory $category)
    {
        $category->delete();

        return response()->json(['message' => 'Project category deleted successfully.']);
    }
}
