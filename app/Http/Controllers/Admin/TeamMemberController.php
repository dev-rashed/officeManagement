<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\HandlesImageUploads;
use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

class TeamMemberController extends Controller
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

            $members = $query->skip($start)->take($length > 0 ? $length : 100)->get();

            $data = $members->map(function ($member) {
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'position' => $member->position,
                    'image' => $member->image_path ? '<img src="' . Storage::disk('public')->url($member->image_path) . '" width="40" height="40" class="rounded-full" style="object-fit: cover;">' : '<div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center"><span class="text-xs text-gray-500">' . Str::limit($member->name, 2) . '</span></div>',
                    'bio' => Str::limit($member->bio ?? '', 100),
                    'email' => $member->email ?? '-',
                    'phone' => $member->phone ?? '-',
                    'status' => $member->status === 'active' ? '<span class="dt-badge is-on">Active</span>' : '<span class="dt-badge is-off">Inactive</span>',
                    'sort_order' => $member->sort_order,
                    'actions' => '<div class="dt-actions">
                        <a href="' . route('admin.team-members.edit', $member->id) . '" class="dt-btn is-edit" title="Edit" aria-label="Edit"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M2.695 14.763l-1.262 3.155a.5.5 0 00.65.649l3.155-1.262a4 4 0 001.343-.885L17.5 5.5a2.121 2.121 0 00-3-3L3.58 13.42a4 4 0 00-.885 1.343z"/></svg></a>
                        <form action="' . route('admin.team-members.destroy', $member->id) . '" method="POST" class="dt-del" onsubmit="return confirm(\'Are you sure you want to delete this team member?\');">
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

        $this->applyUpload($request, $validated, 'image_path', null, 'uploads/team', 'photo');

        TeamMember::create($validated);

        return redirect()->route('admin.team-members.index')
            ->with('success', 'Team member created successfully.');
    }

    public function edit(TeamMember $team_member)
    {
        Gate::authorize('cms.manage');
        return view('pages.admin.cms.team-members.form', [
            'member' => $team_member,
            'mode' => 'edit'
        ]);
    }

    public function update(Request $request, TeamMember $team_member)
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

        $this->applyUpload($request, $validated, 'image_path', $team_member->image_path, 'uploads/team', 'photo');

        $team_member->update($validated);

        return redirect()->route('admin.team-members.index')
            ->with('success', 'Team member updated successfully.');
    }

    public function destroy(TeamMember $team_member)
    {
        Gate::authorize('cms.manage');

        if ($team_member->image_path) {
            Storage::disk('public')->delete($team_member->image_path);
        }

        $team_member->delete();

        return redirect()->route('admin.team-members.index')
            ->with('success', 'Team member deleted successfully.');
    }

    public function data(Request $request)
    {
        return $this->index($request);
    }
}
