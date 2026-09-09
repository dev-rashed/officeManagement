<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\HandlesImageUploads;
use App\Http\Controllers\Controller;
use App\Models\PortfolioItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

class PortfolioItemController extends Controller
{
    use HandlesImageUploads;

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

            $items = $query->skip($start)->take($length > 0 ? $length : 100)->get();

            $data = $items->map(function ($item) {
                $techList = is_array($item->technologies) ? implode(', ', $item->technologies) : ($item->technologies ?? '');
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'image' => $item->image_path ? '<img src="' . Storage::disk('public')->url($item->image_path) . '" width="40" height="40" style="object-fit: cover;">' : '-',
                    'description' => Str::limit($item->description ?? '', 100),
                    'technologies' => $techList,
                    'link' => $item->link ? '<a href="' . $item->link . '" target="_blank" class="text-indigo-600 hover:underline">Link</a>' : '-',
                    'status' => $item->status === 'published' ? '<span class="dt-badge is-on">Published</span>' : '<span class="dt-badge is-off">Draft</span>',
                    'sort_order' => $item->sort_order,
                    'actions' => '<div class="dt-actions">
                        <a href="' . route('admin.portfolio.edit', $item->id) . '" class="dt-btn is-edit" title="Edit" aria-label="Edit"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M2.695 14.763l-1.262 3.155a.5.5 0 00.65.649l3.155-1.262a4 4 0 001.343-.885L17.5 5.5a2.121 2.121 0 00-3-3L3.58 13.42a4 4 0 00-.885 1.343z"/></svg></a>
                        <form action="' . route('admin.portfolio.destroy', $item->id) . '" method="POST" class="dt-del" onsubmit="return confirm(\'Are you sure you want to delete this portfolio item?\');">
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

        $this->applyUpload($request, $validated, 'image_path', null, 'uploads/portfolio', 'featured');

        PortfolioItem::create($validated);

        return redirect()->route('admin.portfolio.index')
            ->with('success', 'Portfolio item created successfully.');
    }

    public function edit(PortfolioItem $portfolio)
    {
        Gate::authorize('cms.manage');
        return view('pages.admin.cms.portfolio.form', [
            'item' => $portfolio,
            'mode' => 'edit'
        ]);
    }

    public function update(Request $request, PortfolioItem $portfolio)
    {
        Gate::authorize('cms.manage');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:portfolio_items,slug,' . $portfolio->id,
            'description' => 'nullable|string',
            'image_path' => 'nullable|image|max:5120',
            'technologies' => 'nullable|array',
            'technologies.*' => 'nullable|string',
            'link' => 'nullable|url|max:255',
            'status' => 'required|in:draft,published',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $this->applyUpload($request, $validated, 'image_path', $portfolio->image_path, 'uploads/portfolio', 'featured');

        $portfolio->update($validated);

        return redirect()->route('admin.portfolio.index')
            ->with('success', 'Portfolio item updated successfully.');
    }

    public function destroy(PortfolioItem $portfolio)
    {
        Gate::authorize('cms.manage');

        if ($portfolio->image_path) {
            Storage::disk('public')->delete($portfolio->image_path);
        }

        $portfolio->delete();

        return redirect()->route('admin.portfolio.index')
            ->with('success', 'Portfolio item deleted successfully.');
    }

    public function data(Request $request)
    {
        return $this->index($request);
    }
}
