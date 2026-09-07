<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;

class ServiceCategoryController extends Controller
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
            $orderDirection = in_array($request->input('order.0.dir'), ['asc', 'desc'], true) ? $request->input('order.0.dir') : 'asc';

            $query = ServiceCategory::query();

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $totalRecords = ServiceCategory::count();
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
                        <a href="' . route('admin.service-categories.edit', $category->id) . '" class="btn btn-outline-primary btn-sm">
                            <i class="fi fi-rr-edit"></i>
                        </a>
                        <form action="' . route('admin.service-categories.destroy', $category->id) . '" method="POST" onsubmit="return confirm(\'Are you sure you want to delete this category?\');" style="display: inline;">
                            ' . csrf_field() . method_field('DELETE') . '
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

        return view('pages.admin.services.categories.index');
    }

    public function create()
    {
        Gate::authorize('cms.manage');
        return view('pages.admin.services.categories.form', [
            'category' => null,
            'mode' => 'create'
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('cms.manage');

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:service_categories',
            'description' => 'nullable|string',
            'status' => 'required|in:draft,published',
        ]);

        ServiceCategory::create($validated);

        return redirect()->route('admin.service-categories.index')
            ->with('success', 'Service category created successfully.');
    }

    public function edit(ServiceCategory $service_category)
    {
        Gate::authorize('cms.manage');
        return view('pages.admin.services.categories.form', [
            'category' => $service_category,
            'mode' => 'edit'
        ]);
    }

    public function update(Request $request, ServiceCategory $service_category)
    {
        Gate::authorize('cms.manage');

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:service_categories,slug,' . $service_category->id,
            'description' => 'nullable|string',
            'status' => 'required|in:draft,published',
        ]);

        $service_category->update($validated);

        return redirect()->route('admin.service-categories.index')
            ->with('success', 'Service category updated successfully.');
    }

    public function destroy(ServiceCategory $service_category)
    {
        Gate::authorize('cms.manage');

        $service_category->delete();

        return redirect()->route('admin.service-categories.index')
            ->with('success', 'Service category deleted successfully.');
    }

    public function data(Request $request)
    {
        return $this->index($request);
    }
}
