<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

class TeamMemberController extends Controller
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

            $query = TeamMember::query();

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('position', 'like', "%{$search}%")
                      ->orWhere('bio', 'like', "%{$search}%");
                });
            }

            $totalRecords = TeamMember::count();
            $filteredRecords = $query->count();

            $columns = ['id', 'name', 'position', 'status', 'sort_order'];
            $orderColumn = $columns[$orderColumnIndex] ?? 'id';
            $query->orderBy($orderColumn, $orderDirection);

            $members = $query->skip($start)->take($length)->get();

            $data = $members->map(function ($member) {
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'position' => $member->position,
                    'image' => $member->image_path ? '<img src="' . Storage::disk('public')->url($member->image_path) . '" width="40" height="40" class="rounded-full" style="object-fit: cover;">' : '<div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center"><span class="text-xs text-gray-500">' . Str::limit($member->name, 2) . '</span></div>',
                    'bio' => Str::limit($member->bio ?? '', 100),
                    'email' => $member->email ?? '-',
                    'phone' => $member->phone ?? '-',
                    'status' => $member->status === 'active' ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>',
                    'sort_order' => $member->sort_order,
                    'actions' => '<div class="btn-group btn-group-sm" role="group">
                        <a href="' . route('admin.team-members.edit', $member->id) . '" class="btn btn-outline-primary btn-sm">
                            <i class="fi fi-rr-edit"></i>
                        </a>
                        <form action="' . route('admin.team-members.destroy', $member->id) . '" method="POST" onsubmit="return confirm(\'Are you sure you want to delete this team member?\');" style="display: inline;">
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

        return view('pages.admin.cms.team-members.index');
    }

    public function create()
    {
        Gate::authorize('cms.manage');
        return view('pages.admin.cms.team-members.form', [
            'member' => null,
            'mode' => 'create'
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('cms.manage');

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'position' => 'required|string|max:100',
            'bio' => 'nullable|string',
            'image_path' => 'nullable|image|max:5120',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:20',
            'social_links' => 'nullable|array',
            'social_links.*' => 'nullable|url',
            'status' => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('image_path')) {
            $path = $request->file('image_path')->store('uploads/team', 'public');
            $validated['image_path'] = $path;
        }

        TeamMember::create($validated);

        return redirect()->route('admin.team-members.index')
            ->with('success', 'Team member created successfully.');
    }

    public function edit(TeamMember $member)
    {
        Gate::authorize('cms.manage');
        return view('pages.admin.cms.team-members.form', [
            'member' => $member,
            'mode' => 'edit'
        ]);
    }

    public function update(Request $request, TeamMember $member)
    {
        Gate::authorize('cms.manage');

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'position' => 'required|string|max:100',
            'bio' => 'nullable|string',
            'image_path' => 'nullable|image|max:5120',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:20',
            'social_links' => 'nullable|array',
            'social_links.*' => 'nullable|url',
            'status' => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('image_path')) {
            if ($member->image_path) {
                Storage::disk('public')->delete($member->image_path);
            }
            $path = $request->file('image_path')->store('uploads/team', 'public');
            $validated['image_path'] = $path;
        }

        $member->update($validated);

        return redirect()->route('admin.team-members.index')
            ->with('success', 'Team member updated successfully.');
    }

    public function destroy(TeamMember $member)
    {
        Gate::authorize('cms.manage');

        if ($member->image_path) {
            Storage::disk('public')->delete($member->image_path);
        }

        $member->delete();

        return redirect()->route('admin.team-members.index')
            ->with('success', 'Team member deleted successfully.');
    }

    public function data(Request $request)
    {
        return $this->index($request);
    }
}
