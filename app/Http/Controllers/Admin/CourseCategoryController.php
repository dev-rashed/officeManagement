<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;

class CourseCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('cms.manage');

        if ($request->ajax()) {
            $draw = $request->input('draw');
            $start = $request->input('start');
            $length = $request->input('length');
            $search = $request->input('search.value');
            $orderColumnIndex = $request->input('order.0.column');
            $orderDirection = $request->input('order.0.dir');

            $query = CourseCategory::query();

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $totalRecords = CourseCategory::count();
            $filteredRecords = $query->count();

            $columns = ['id', 'name', 'status'];
            $orderColumn = $columns[$orderColumnIndex] ?? 'id';
            $query->orderBy($orderColumn, $orderDirection);

            $categories = $query->skip($start)->take($length)->get();

            $data = $categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => Str::limit($category->description ?? '', 100),
                    'status' => $category->status === 'published' ? '<span class="badge bg-success">Published</span>' : '<span class="badge bg-secondary">Draft</span>',
                    'actions' => '<div class="btn-group btn-group-sm" role="group">
                        <a href="' . route('admin.course-categories.edit', $category->id) . '" class="btn btn-outline-primary btn-sm">
                            <i class="fi fi-rr-edit"></i>
                        </a>
                        <form action="' . route('admin.course-categories.destroy', $category->id) . '" method="POST" onsubmit="return confirm(\'Are you sure you want to delete this category?\');" style="display: inline;">
                            @csrf
                            @method("DELETE")
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="fi fi-rr-trash"></i>
                            </button>
                        </form>
                    </div>',
                ];
            });

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
        }

        return view('pages.admin.courses.categories.index');
    }

    public function create()
    {
        Gate::authorize('cms.manage');
        return view('pages.admin.courses.categories.form', [
            'category' => null,
            'mode' => 'create'
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('cms.manage');

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:course_categories',
            'description' => 'nullable|string',
            'status' => 'required|in:draft,published',
        ]);

        CourseCategory::create($validated);

        return redirect()->route('admin.course-categories.index')
            ->with('success', 'Course category created successfully.');
    }

    public function edit(CourseCategory $category)
    {
        Gate::authorize('cms.manage');
        return view('pages.admin.courses.categories.form', [
            'category' => $category,
            'mode' => 'edit'
        ]);
    }

    public function update(Request $request, CourseCategory $category)
    {
        Gate::authorize('cms.manage');

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:course_categories,slug,' . $category->id,
            'description' => 'nullable|string',
            'status' => 'required|in:draft,published',
        ]);

        $category->update($validated);

        return redirect()->route('admin.course-categories.index')
            ->with('success', 'Course category updated successfully.');
    }

    public function destroy(CourseCategory $category)
    {
        Gate::authorize('cms.manage');

        $category->delete();

        return redirect()->route('admin.course-categories.index')
            ->with('success', 'Course category deleted successfully.');
    }

    public function data(Request $request)
    {
        return $this->index($request);
    }
}
