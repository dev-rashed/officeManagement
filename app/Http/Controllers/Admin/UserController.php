<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ProtectedUserException;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:users.manage'),
        ];
    }

    public function index()
    {
        $roles = Role::ordered()->get();

        $counts = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'disabled' => User::where('is_active', false)->count(),
        ];

        return view('pages.admin.users.index', compact('roles', 'counts'));
    }

    public function data(Request $request)
    {
        $query = User::query()->with('disabledBy:id,name')->latest('id');
        $totalRecords = User::count();

        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('role', 'like', "%{$search}%");
            });
        }

        $filteredRecords = $query->count();
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 20);

        $labels = Role::pluck('label', 'name');
        $actor = $request->user();

        $data = $query->skip($start)->take($length)->get()->map(function (User $user) use ($labels, $actor) {
            $initials = e($user->initials());

            $identity = '<div class="user-identity">'
                .'<span class="user-avatar">'.$initials.'</span>'
                .'<span class="user-identity-text">'
                .'<span class="user-name">'.e($user->name).'</span>'
                .'<span class="user-email">'.e($user->email).'</span>'
                .'</span></div>';

            $badges = '<span class="user-role">'.e($labels[$user->role] ?? $user->role ?? 'No role').'</span>';

            if ($user->is_protected) {
                $badges .= '<span class="user-chip is-protected">protected</span>';
            }

            $status = $user->is_active
                ? '<span class="user-chip is-active">Active</span>'
                : '<span class="user-chip is-disabled">Disabled</span>';

            if (! $user->is_active && $user->disabled_at) {
                $status .= '<span class="user-sub">'.$user->disabled_at->format('d M Y')
                    .($user->disabledBy ? ' by '.e($user->disabledBy->name) : '').'</span>';
            }

            $actions = '<div class="user-actions">';
            $actions .= '<button type="button" class="user-btn is-primary user-edit-btn"'
                .' data-id="'.$user->id.'"'
                .' data-name="'.e($user->name).'"'
                .' data-email="'.e($user->email).'"'
                .' data-phone="'.e((string) $user->phone).'"'
                .' data-role="'.e((string) $user->role).'"'
                .' data-two-factor="'.e((string) $user->two_factor_type).'"'
                .'>Edit</button>';

            // Reasons an account cannot be switched off are explained rather
            // than the button silently missing.
            $lock = $this->lockReason($user, $actor);

            if ($lock) {
                $actions .= '<button type="button" class="user-btn is-muted" disabled title="'.e($lock).'">Locked</button>';
            } else {
                $actions .= $user->is_active
                    ? '<button type="button" class="user-btn is-warn user-toggle-btn" data-url="'.route('admin.users.toggle', $user).'" data-action="disable">Disable</button>'
                    : '<button type="button" class="user-btn is-ok user-toggle-btn" data-url="'.route('admin.users.toggle', $user).'" data-action="enable">Enable</button>';

                $actions .= '<button type="button" class="user-btn is-danger user-delete-btn" data-url="'.route('admin.users.destroy', $user).'" data-name="'.e($user->name).'">Delete</button>';
            }

            $actions .= '</div>';

            return [
                'identity' => $identity,
                'role' => $badges,
                'status' => $status,
                'created' => $user->created_at?->format('d M Y') ?? '&mdash;',
                'actions' => $actions,
            ];
        });

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $user = new User([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'phone' => $data['phone'] ?? null,
            'two_factor_type' => $data['two_factor_type'],
        ]);

        // Created by an administrator, so the address is taken as verified --
        // otherwise the new person is locked out behind a verification email
        // this application may not be able to send.
        $user->email_verified_at = now();
        $user->is_active = true;
        $user->save();

        return response()->json(['message' => "{$user->name} added."], 201);
    }

    public function update(Request $request, User $user)
    {
        $data = $this->validated($request, $user);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->phone = $data['phone'] ?? null;
        $user->two_factor_type = $data['two_factor_type'];
        $user->role = $data['role'];

        if (filled($data['password'] ?? null)) {
            $user->password = Hash::make($data['password']);
        }

        try {
            $user->save();
        } catch (ProtectedUserException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => "{$user->name} updated."]);
    }

    /** Switch an account on or off. */
    public function toggle(Request $request, User $user)
    {
        if ($reason = $this->lockReason($user, $request->user())) {
            return response()->json(['message' => $reason], 422);
        }

        $enabling = ! $user->is_active;

        $user->is_active = $enabling;
        $user->disabled_at = $enabling ? null : now();
        $user->disabled_by = $enabling ? null : $request->user()->id;

        try {
            $user->save();
        } catch (ProtectedUserException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => $enabling
                ? "{$user->name} can sign in again."
                : "{$user->name} has been disabled and signed out.",
            'is_active' => $user->is_active,
        ]);
    }

    public function destroy(Request $request, User $user)
    {
        if ($reason = $this->lockReason($user, $request->user())) {
            return response()->json(['message' => $reason], 422);
        }

        try {
            $user->delete();
        } catch (ProtectedUserException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Account removed.']);
    }

    /**
     * Why this account cannot be disabled or deleted, or null if it can.
     */
    private function lockReason(User $user, User $actor): ?string
    {
        if ($user->is_protected) {
            return 'This is a protected superadmin account.';
        }

        if ($user->id === $actor->id) {
            return 'You cannot disable or delete your own account.';
        }

        if ($user->isSuperAdmin() && ! $actor->isSuperAdmin()) {
            return 'Only a superadmin can act on another superadmin.';
        }

        if ($user->isSuperAdmin() && User::activeSuperadminCount() <= 1) {
            return 'This is the last active superadmin.';
        }

        return null;
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $actor = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone' => ['nullable', 'string', 'max:25'],
            'role' => ['required', 'string', Rule::in(User::roles())],
            'two_factor_type' => ['required', 'string', Rule::in(User::twoFactorTypes())],
            'password' => [$user ? 'nullable' : 'required', 'string', Password::min(12)],
        ], [
            'password.required' => 'A password is required for a new account.',
        ]);

        /*
         * Privilege escalation guard.
         *
         * `users.manage` is held by admin as well as superadmin. Without this,
         * an admin could create a superadmin account and sign into it, which
         * would hand them the roles and permissions screen they are otherwise
         * barred from.
         */
        if ($data['role'] === User::ROLE_SUPERADMIN && ! $actor->isSuperAdmin()) {
            abort(response()->json([
                'message' => 'Only a superadmin can assign the superadmin role.',
                'errors' => ['role' => ['Only a superadmin can assign the superadmin role.']],
            ], 422));
        }

        // Same reasoning in reverse: an admin must not be able to edit an
        // existing superadmin at all.
        if ($user && $user->isSuperAdmin() && ! $actor->isSuperAdmin()) {
            abort(response()->json([
                'message' => 'Only a superadmin can edit another superadmin.',
            ], 422));
        }

        return $data;
    }
}
