<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

class ServiceController extends Controller
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

            $query = Service::with('category');

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('short_description', 'like', "%{$search}%")
                      ->orWhere('full_description', 'like', "%{$search}%");
                });
            }

            $totalRecords = Service::count();
            $filteredRecords = $query->count();

            $columns = ['id', 'title', 'category.name', 'status', 'show_on_homepage', 'sort_order'];
            $orderColumn = $columns[$orderColumnIndex] ?? 'id';
            if ($orderColumn === 'category.name') {
                $query->orderBy('category.name', $orderDirection);
            } else {
                $query->orderBy($orderColumn, $orderDirection);
            }

            $services = $query->skip($start)->take($length)->get();

            $data = $services->map(function ($service) {
                return [
                    'id' => $service->id,
                    'title' => $service->title,
                    'slug' => $service->slug,
                    'category' => $service->category ? $service->category->name : '-',
                    'status' => $service->status === 'published' ? '<span class="badge bg-success">Published</span>' : '<span class="badge bg-secondary">Draft</span>',
                    'show_on_homepage' => $service->show_on_homepage ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>',
                    'sort_order' => $service->sort_order,
                    'actions' => '<div class="btn-group btn-group-sm" role="group">
                        <a href="' . route('admin.services.edit', $service->id) . '" class="btn btn-outline-primary btn-sm">
                            <i class="fi fi-rr-edit"></i>
                        </a>
                        <form action="' . route('admin.services.destroy', $service->id) . '" method="POST" onsubmit="return confirm(\'Are you sure you want to delete this service?\');" style="display: inline;">
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

        $categories = ServiceCategory::published()->pluck('name', 'id');
        return view('pages.admin.services.index', compact('categories'));
    }

    public function create()
    {
        Gate::authorize('cms.manage');
        $categories = ServiceCategory::published()->pluck('name', 'id');
        return view('pages.admin.services.form', [
            'service' => null,
            'mode' => 'create',
            'categories' => $categories
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('cms.manage');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:services',
            'short_description' => 'required|string',
            'full_description' => 'nullable|string',
            'featured_image' => 'nullable|image|max:5120',
            'service_category_id' => 'nullable|exists:service_categories,id',
            'status' => 'required|in:draft,published',
            'show_on_homepage' => 'required|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('featured_image')) {
            $path = $request->file('featured_image')->store('uploads/services', 'public');
            $validated['featured_image'] = $path;
        }

        Service::create($validated);

        return redirect()->route('admin.services.index')
            ->with('success', 'Service created successfully.');
    }

    public function edit(Service $service)
    {
        Gate::authorize('cms.manage');
        $categories = ServiceCategory::published()->pluck('name', 'id');
        return view('pages.admin.services.form', [
            'service' => $service,
            'mode' => 'edit',
            'categories' => $categories
        ]);
    }

    public function update(Request $request, Service $service)
    {
        Gate::authorize('cms.manage');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:services,slug,' . $service->id,
            'short_description' => 'required|string',
            'full_description' => 'nullable|string',
            'featured_image' => 'nullable|image|max:5120',
            'service_category_id' => 'nullable|exists:service_categories,id',
            'status' => 'required|in:draft,published',
            'show_on_homepage' => 'required|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('featured_image')) {
            if ($service->featured_image) {
                Storage::disk('public')->delete($service->featured_image);
            }
            $path = $request->file('featured_image')->store('uploads/services', 'public');
            $validated['featured_image'] = $path;
        }

        $service->update($validated);

        return redirect()->route('admin.services.index')
            ->with('success', 'Service updated successfully.');
    }

    public function destroy(Service $service)
    {
        Gate::authorize('cms.manage');

        if ($service->featured_image) {
            Storage::disk('public')->delete($service->featured_image);
        }

        $service->delete();

        return redirect()->route('admin.services.index')
            ->with('success', 'Service deleted successfully.');
    }

    public function data(Request $request)
    {
        return $this->index($request);
    }

    // Public methods
    public function publicIndex()
    {
        $services = Service::published()->sorted()->get();
        return view('pages.public.services', compact('services'));
    }

    public function publicShow($slug)
    {
        $service = Service::where('slug', $slug)->published()->firstOrFail();
        return view('pages.public.services.detail', compact('service'));
    }
}
