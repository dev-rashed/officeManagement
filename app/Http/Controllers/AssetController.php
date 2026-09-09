<?php

namespace App\Http\Controllers;

use App\Concerns\HandlesImageUploads;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

class AssetController extends Controller
{
    use HandlesImageUploads;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('assets.manage');

        if ($request->ajax()) {
            $draw = $request->input('draw');
            $start = max(0, (int) $request->input('start', 0));
            // -1 is DataTables' "show all"; anything absent falls back to a page.
            $length = (int) $request->input('length', 10);
            $search = $request->input('search.value');

            $orderColumnIndex = $request->input('order.0.column');
            $orderDirection = in_array($request->input('order.0.dir'), ['asc', 'desc'], true) ? $request->input('order.0.dir') : 'asc';

            // Filter parameters
            $category = $request->input('category');
            $status = $request->input('status');
            $location = $request->input('location');
            $assignedUserId = $request->input('assigned_user_id');
            $purchaseDateFrom = $request->input('purchase_date_from');
            $purchaseDateTo = $request->input('purchase_date_to');

            $query = Asset::with('assignedUser');

            // Search
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code_tag_number', 'like', "%{$search}%")
                      ->orWhere('brand', 'like', "%{$search}%")
                      ->orWhere('model', 'like', "%{$search}%")
                      ->orWhere('serial_number', 'like', "%{$search}%");
                });
            }

            // Apply filters
            $query->filterByCategory($category);
            $query->filterByStatus($status);
            $query->filterByLocation($location);
            $query->filterByAssignedUser($assignedUserId);

            if ($purchaseDateFrom && $purchaseDateTo) {
                $query->filterByPurchaseDateRange($purchaseDateFrom, $purchaseDateTo);
            }

            $totalRecords = Asset::count();
            $filteredRecords = $query->count();

            // Ordering
            $columns = ['id', 'name', 'code_tag_number', 'category', 'brand', 'model', 'status', 'assigned_user_id'];
            $orderColumn = $columns[$orderColumnIndex] ?? 'id';
            $query->orderBy($orderColumn, $orderDirection);

            $assets = $query->skip($start)->take($length > 0 ? $length : 100)->get();

            $data = $assets->map(function ($asset) {
                return [
                    'id' => $asset->id,
                    'name' => $asset->name,
                    'code' => $asset->code_tag_number,
                    'category' => $asset->category,
                    'brand' => $asset->brand,
                    'model' => $asset->model,
                    'serial' => $asset->serial_number,
                    'purchase_date' => $asset->purchase_date ? $asset->purchase_date->format('Y-m-d') : '-',
                    'purchase_cost' => $asset->purchase_cost ? number_format($asset->purchase_cost, 2) : '-',
                    'current_value' => $asset->current_value ? number_format($asset->current_value, 2) : '-',
                    'location' => $asset->location,
                    'assigned_to' => $asset->assignedUser ? $asset->assignedUser->name : '-',
                    'condition' => $asset->condition,
                    'status' => $this->getStatusBadge($asset->status),
                    'attachment' => $asset->attachment_path ?
                        '<a href="' . Storage::disk('public')->url($asset->attachment_path) . '" target="_blank" class="text-indigo-600 hover:underline">View</a>' :
                        '-',
                    'actions' => '<div class="dt-actions">
                        <a href="' . route('assets.show', $asset->id) . '" class="dt-btn is-view" title="View" aria-label="View"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 12.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z"/><path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 010-1.186A10.004 10.004 0 0110 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0110 17c-4.257 0-7.893-2.66-9.336-6.41zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/></svg></a>
                        <a href="' . route('assets.edit', $asset->id) . '" class="dt-btn is-edit" title="Edit" aria-label="Edit"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M2.695 14.763l-1.262 3.155a.5.5 0 00.65.649l3.155-1.262a4 4 0 001.343-.885L17.5 5.5a2.121 2.121 0 00-3-3L3.58 13.42a4 4 0 00-.885 1.343z"/></svg></a>
                        <form action="' . route('assets.destroy', $asset->id) . '" method="POST" class="dt-del" onsubmit="return confirm(\'Are you sure you want to delete this asset?\');">
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

        // For filter dropdowns
        $categories = Asset::distinct()->pluck('category');
        $statuses = Asset::distinct()->pluck('status');
        $locations = Asset::distinct()->pluck('location');
        $users = \App\Models\User::pluck('name', 'id');

        return view('pages.assets.index', compact('categories', 'statuses', 'locations', 'users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('assets.manage');
        $users = \App\Models\User::pluck('name', 'id');
        return view('pages.assets.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('assets.manage');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code_tag_number' => 'required|string|max:100|unique:assets',
            'category' => 'required|string|max:100',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric|min:0',
            'current_value' => 'nullable|numeric|min:0',
            'vendor_supplier' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:100',
            'assigned_user_id' => 'nullable|exists:users,id',
            'condition' => 'required|in:excellent,good,fair,poor',
            'status' => 'required|in:active,in_repair,retired,lost,disposed',
            'notes' => 'nullable|string',
            'attachment_path' => 'nullable|file|max:10240', // 10MB max
        ]);

        $this->applyUpload($request, $validated, 'attachment_path', null, 'uploads/assets', 'document');

        Asset::create($validated);

        return redirect()->route('assets.index')
            ->with('success', 'Asset created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Asset $asset)
    {
        Gate::authorize('assets.manage');
        return view('pages.assets.show', compact('asset'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Asset $asset)
    {
        Gate::authorize('assets.manage');
        $users = \App\Models\User::pluck('name', 'id');
        return view('pages.assets.edit', compact('asset', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Asset $asset)
    {
        Gate::authorize('assets.manage');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code_tag_number' => 'required|string|max:100|unique:assets,code_tag_number,' . $asset->id,
            'category' => 'required|string|max:100',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric|min:0',
            'current_value' => 'nullable|numeric|min:0',
            'vendor_supplier' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:100',
            'assigned_user_id' => 'nullable|exists:users,id',
            'condition' => 'required|in:excellent,good,fair,poor',
            'status' => 'required|in:active,in_repair,retired,lost,disposed',
            'notes' => 'nullable|string',
            'attachment_path' => 'nullable|file|max:10240',
        ]);

        $this->applyUpload($request, $validated, 'attachment_path', $asset->attachment_path, 'uploads/assets', 'document');

        $asset->update($validated);

        return redirect()->route('assets.index')
            ->with('success', 'Asset updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Asset $asset)
    {
        Gate::authorize('assets.manage');

        if ($asset->attachment_path) {
            Storage::disk('public')->delete($asset->attachment_path);
        }

        $asset->delete();

        return redirect()->route('assets.index')
            ->with('success', 'Asset deleted successfully.');
    }

    /**
     * AJAX endpoint for DataTables data
     */
    public function data(Request $request)
    {
        return $this->index($request);
    }

    private function getStatusBadge($status)
    {
        $colors = [
            'active' => 'bg-success',
            'in_repair' => 'bg-warning',
            'retired' => 'bg-secondary',
            'lost' => 'bg-danger',
            'disposed' => 'bg-dark',
        ];

        $label = ucfirst(str_replace('_', ' ', $status));
        $color = $colors[$status] ?? 'bg-secondary';

        return '<span class="badge ' . $color . '">' . $label . '</span>';
    }
}
