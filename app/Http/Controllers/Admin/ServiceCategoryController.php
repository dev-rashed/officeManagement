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
            $start = max(0, (int) $request->input('start', 0));
            // -1 is DataTables' "show all"; anything absent falls back to a page.
            $length = (int) $request->input('length', 10);
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

            // Must line up with the columns the table actually shows:
            // 0 name, 1 slug, 2 description, 3 status, 4 actions. The previous
            // map started at 'id', so every sort ordered by the wrong column.
            $columns = ['name', 'slug', 'description', 'status'];
            $orderColumn = $columns[$orderColumnIndex] ?? 'name';
            $query->orderBy($orderColumn, $orderDirection);

            $categories = $query->skip($start)->take($length > 0 ? $length : 100)->get();

            $data = $categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    // The table has a Slug column; without this key DataTables
                    // throws "Requested unknown parameter 'slug'".
                    'slug' => $category->slug,
                    'description' => Str::limit($category->description ?? '', 100),
                    'status' => $category->status === 'published' ? '<span class="dt-badge is-on">Published</span>' : '<span class="dt-badge is-off">Draft</span>',
                    'actions' => '<div class="dt-actions">
                        <a href="' . route('admin.service-categories.edit', $category->id) . '" class="dt-btn is-edit" title="Edit" aria-label="Edit"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M2.695 14.763l-1.262 3.155a.5.5 0 00.65.649l3.155-1.262a4 4 0 001.343-.885L17.5 5.5a2.121 2.121 0 00-3-3L3.58 13.42a4 4 0 00-.885 1.343z"/></svg></a>
                        <form action="' . route('admin.service-categories.destroy', $category->id) . '" method="POST" class="dt-del" onsubmit="return confirm(\'Are you sure you want to delete this category?\');">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button type="submit" class="dt-btn is-delete" title="Delete" aria-label="Delete"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd"/></svg></button>
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
