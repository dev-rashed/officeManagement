<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageSection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

class PageSectionController extends Controller
{
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
            $start = $request->input('start');
            $length = $request->input('length');
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

            $sections = $query->skip($start)->take($length)->get();

            $data = $sections->map(function ($section) {
                return [
                    'id' => $section->id,
                    'page_slug' => $section->page_slug,
                    'section_key' => $section->section_key,
                    'title' => $section->title,
                    'image' => $section->image_path ? '<img src="' . Storage::disk('public')->url($section->image_path) . '" width="40" height="40" style="object-fit: cover;">' : '-',
                    'visibility' => $section->visibility ? '<span class="badge bg-success">Visible</span>' : '<span class="badge bg-secondary">Hidden</span>',
                    'sort_order' => $section->sort_order,
                    'actions' => '<div class="btn-group btn-group-sm" role="group">
                        <a href="' . route('admin.page-sections.edit', $section->id) . '" class="btn btn-outline-primary btn-sm">
                            <i class="fi fi-rr-edit"></i>
                        </a>
                        <form action="' . route('admin.page-sections.destroy', $section->id) . '" method="POST" onsubmit="return confirm(\'Are you sure you want to delete this section?\');" style="display: inline;">
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
        if ($request->hasFile('image_path')) {
            $path = $request->file('image_path')->store('uploads/cms', 'public');
            $validated['image_path'] = $path;
        }

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

        // Handle image upload
        if ($request->hasFile('image_path')) {
            // Delete old image if exists
            if ($page_section->image_path) {
                Storage::disk('public')->delete($page_section->image_path);
            }
            $path = $request->file('image_path')->store('uploads/cms', 'public');
            $validated['image_path'] = $path;
        }

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
