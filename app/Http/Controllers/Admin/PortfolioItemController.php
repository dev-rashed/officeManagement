<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PortfolioItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

class PortfolioItemController extends Controller
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

            $query = PortfolioItem::query();

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('technologies', 'like', "%{$search}%");
                });
            }

            $totalRecords = PortfolioItem::count();
            $filteredRecords = $query->count();

            $columns = ['id', 'title', 'status', 'sort_order'];
            $orderColumn = $columns[$orderColumnIndex] ?? 'id';
            $query->orderBy($orderColumn, $orderDirection);

            $items = $query->skip($start)->take($length)->get();

            $data = $items->map(function ($item) {
                $techList = is_array($item->technologies) ? implode(', ', $item->technologies) : ($item->technologies ?? '');
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'image' => $item->image_path ? '<img src="' . Storage::disk('public')->url($item->image_path) . '" width="40" height="40" style="object-fit: cover;">' : '-',
                    'description' => Str::limit($item->description ?? '', 100),
                    'technologies' => $techList,
                    'link' => $item->link ? '<a href="' . $item->link . '" target="_blank" class="text-indigo-600 hover:underline">Link</a>' : '-',
                    'status' => $item->status === 'published' ? '<span class="badge bg-success">Published</span>' : '<span class="badge bg-secondary">Draft</span>',
                    'sort_order' => $item->sort_order,
                    'actions' => '<div class="btn-group btn-group-sm" role="group">
                        <a href="' . route('admin.portfolio.edit', $item->id) . '" class="btn btn-outline-primary btn-sm">
                            <i class="fi fi-rr-edit"></i>
                        </a>
                        <form action="' . route('admin.portfolio.destroy', $item->id) . '" method="POST" onsubmit="return confirm(\'Are you sure you want to delete this portfolio item?\');" style="display: inline;">
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

        return view('pages.admin.cms.portfolio.index');
    }

    public function create()
    {
        Gate::authorize('cms.manage');
        return view('pages.admin.cms.portfolio.form', [
            'item' => null,
            'mode' => 'create'
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('cms.manage');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:portfolio_items',
            'description' => 'nullable|string',
            'image_path' => 'nullable|image|max:5120',
            'technologies' => 'nullable|array',
            'technologies.*' => 'nullable|string',
            'link' => 'nullable|url|max:255',
            'status' => 'required|in:draft,published',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('image_path')) {
            $path = $request->file('image_path')->store('uploads/portfolio', 'public');
            $validated['image_path'] = $path;
        }

        PortfolioItem::create($validated);

        return redirect()->route('admin.portfolio.index')
            ->with('success', 'Portfolio item created successfully.');
    }

    public function edit(PortfolioItem $item)
    {
        Gate::authorize('cms.manage');
        return view('pages.admin.cms.portfolio.form', [
            'item' => $item,
            'mode' => 'edit'
        ]);
    }

    public function update(Request $request, PortfolioItem $item)
    {
        Gate::authorize('cms.manage');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:portfolio_items,slug,' . $item->id,
            'description' => 'nullable|string',
            'image_path' => 'nullable|image|max:5120',
            'technologies' => 'nullable|array',
            'technologies.*' => 'nullable|string',
            'link' => 'nullable|url|max:255',
            'status' => 'required|in:draft,published',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('image_path')) {
            if ($item->image_path) {
                Storage::disk('public')->delete($item->image_path);
            }
            $path = $request->file('image_path')->store('uploads/portfolio', 'public');
            $validated['image_path'] = $path;
        }

        $item->update($validated);

        return redirect()->route('admin.portfolio.index')
            ->with('success', 'Portfolio item updated successfully.');
    }

    public function destroy(PortfolioItem $item)
    {
        Gate::authorize('cms.manage');

        if ($item->image_path) {
            Storage::disk('public')->delete($item->image_path);
        }

        $item->delete();

        return redirect()->route('admin.portfolio.index')
            ->with('success', 'Portfolio item deleted successfully.');
    }

    public function data(Request $request)
    {
        return $this->index($request);
    }
}
