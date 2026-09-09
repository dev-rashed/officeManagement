<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\HandlesImageUploads;
use App\Http\Controllers\Controller;
use App\Models\PageSection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

class PageSectionController extends Controller
{
    use HandlesImageUploads;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Check authorization
        Gate::authorize('cms.manage');

        if ($request->ajax()) {
            // AJAX DataTable request
            $draw = $request->input('draw');
            $start = max(0, (int) $request->input('start', 0));
            // -1 is DataTables' "show all"; anything absent falls back to a page.
            $length = (int) $request->input('length', 10);
            $search = $request->input('search.value');
            $orderColumnIndex = $request->input('order.0.column');
            $orderDirection = in_array($request->input('order.0.dir'), ['asc', 'desc'], true) ? $request->input('order.0.dir') : 'asc';

            $query = PageSection::query();

            // Search
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('page_slug', 'like', "%{$search}%")
                      ->orWhere('section_key', 'like', "%{$search}%");
                });
            }

            $totalRecords = PageSection::count();
            $filteredRecords = $query->count();

            // Ordering
            $columns = ['id', 'page_slug', 'section_key', 'title', 'visibility', 'sort_order'];
            $orderColumn = $columns[$orderColumnIndex] ?? 'id';
            $query->orderBy($orderColumn, $orderDirection);

            $sections = $query->skip($start)->take($length > 0 ? $length : 100)->get();

            $data = $sections->map(function ($section) {
                return [
                    'id' => $section->id,
                    'page_slug' => $section->page_slug,
                    'section_key' => $section->section_key,
                    'title' => $section->title,
                    'image' => $section->image_path ? '<img src="' . Storage::disk('public')->url($section->image_path) . '" width="40" height="40" style="object-fit: cover;">' : '-',
                    'visibility' => $section->visibility ? '<span class="dt-badge is-on">Visible</span>' : '<span class="dt-badge is-off">Hidden</span>',
                    'sort_order' => $section->sort_order,
                    'actions' => '<div class="dt-actions">
                        <a href="' . route('admin.page-sections.edit', $section->id) . '" class="dt-btn is-edit" title="Edit" aria-label="Edit"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M2.695 14.763l-1.262 3.155a.5.5 0 00.65.649l3.155-1.262a4 4 0 001.343-.885L17.5 5.5a2.121 2.121 0 00-3-3L3.58 13.42a4 4 0 00-.885 1.343z"/></svg></a>
                        <form action="' . route('admin.page-sections.destroy', $section->id) . '" method="POST" class="dt-del" onsubmit="return confirm(\'Are you sure you want to delete this section?\');">
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

        return view('pages.admin.cms.page-sections.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('cms.manage');
        return view('pages.admin.cms.page-sections.form', [
            'section' => null,
            'mode' => 'create'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('cms.manage');

        $validated = $request->validate([
            'page_slug' => 'required|string|max:50',
            'section_key' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image_path' => 'nullable|image|max:5120', // 5MB max
            'button_text' => 'nullable|string|max:100',
            'button_link' => 'nullable|url|max:255',
            'visibility' => 'required|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Handle image upload
        $this->applyUpload($request, $validated, 'image_path', null, 'uploads/cms', 'featured');

        PageSection::create($validated);

        return redirect()->route('admin.page-sections.index')
            ->with('success', 'Page section created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PageSection $page_section)
    {
        Gate::authorize('cms.manage');
        return view('pages.admin.cms.page-sections.form', [
            'section' => $page_section,
            'mode' => 'edit'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PageSection $page_section)
    {
        Gate::authorize('cms.manage');

        $validated = $request->validate([
            'page_slug' => 'required|string|max:50',
            'section_key' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image_path' => 'nullable|image|max:5120',
            'button_text' => 'nullable|string|max:100',
            'button_link' => 'nullable|url|max:255',
            'visibility' => 'required|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $this->applyUpload($request, $validated, 'image_path', $page_section->image_path, 'uploads/cms', 'featured');

        $page_section->update($validated);

        return redirect()->route('admin.page-sections.index')
            ->with('success', 'Page section updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PageSection $page_section)
    {
        Gate::authorize('cms.manage');

        // Delete associated image
        if ($page_section->image_path) {
            Storage::disk('public')->delete($page_section->image_path);
        }

        $page_section->delete();

        return redirect()->route('admin.page-sections.index')
            ->with('success', 'Page section deleted successfully.');
    }

    /**
     * AJAX endpoint for DataTables data
     */
    public function data(Request $request)
    {
        // This duplicates the AJAX logic above - could refactor
        // Keeping it separate as per existing patterns
        return $this->index($request);
    }
}
