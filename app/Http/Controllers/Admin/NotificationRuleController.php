<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationRule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class NotificationRuleController extends Controller
{
    public function index()
    {
        Gate::authorize('settings.manage');

        $events = config('notifications.events');

        $rules = NotificationRule::query()
            ->with('user:id,name,email,role')
            ->get()
            ->groupBy('event');

        $users = User::query()->orderBy('name')->get(['id', 'name', 'email', 'role']);
        $roles = User::roles();

        $mailerIsLog = config('mail.default') === 'log';

        return view('pages.admin.notifications.index', compact(
            'events', 'rules', 'users', 'roles', 'mailerIsLog'
        ));
    }

    public function store(Request $request)
    {
        Gate::authorize('settings.manage');

        $data = $this->validated($request);

        $existing = NotificationRule::where('event', $data['event'])
            ->where('recipient_type', $data['recipient_type'])
            ->where('recipient_value', $data['recipient_value'])
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'That recipient already has a rule for this event. Edit the existing one instead.',
            ], 422);
        }

        $data['created_by'] = $request->user()->id;

        $rule = NotificationRule::create($data);

        return response()->json([
            'message' => 'Notification rule added.',
            'rule' => $rule,
        ], 201);
    }

    public function update(Request $request, NotificationRule $rule)
    {
        Gate::authorize('settings.manage');

        $rule->update($this->validated($request, $rule));

        return response()->json(['message' => 'Notification rule updated.']);
    }

    /** Quick on/off from the list without opening the modal. */
    public function toggle(NotificationRule $rule)
    {
        Gate::authorize('settings.manage');

        $rule->update(['is_active' => ! $rule->is_active]);

        return response()->json([
            'message' => $rule->is_active ? 'Rule enabled.' : 'Rule disabled.',
            'is_active' => $rule->is_active,
        ]);
    }

    public function destroy(NotificationRule $rule)
    {
        Gate::authorize('settings.manage');

        $rule->delete();

        return response()->json(['message' => 'Notification rule removed.']);
    }

    private function validated(Request $request, ?NotificationRule $rule = null): array
    {
        $data = $request->validate([
            'event' => ['required', 'string', Rule::in(array_keys(config('notifications.events')))],
            'recipient_type' => ['required', 'string', Rule::in([NotificationRule::RECIPIENT_ROLE, NotificationRule::RECIPIENT_USER])],
            'recipient_value' => ['required', 'string', 'max:100'],
            'via_database' => ['boolean'],
            'via_mail' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        // The recipient has to actually exist, whichever kind it is.
        if ($data['recipient_type'] === NotificationRule::RECIPIENT_ROLE) {
            abort_unless(in_array($data['recipient_value'], User::roles(), true), 422, 'Unknown role.');
        } else {
            abort_unless(User::whereKey((int) $data['recipient_value'])->exists(), 422, 'Unknown user.');
        }

        $data['via_database'] = $request->boolean('via_database');
        $data['via_mail'] = $request->boolean('via_mail');
        $data['is_active'] = $request->boolean('is_active');

        // A rule delivering nowhere is just a confusing disabled rule.
        if (! $data['via_database'] && ! $data['via_mail']) {
            abort(response()->json([
                'message' => 'Pick at least one delivery channel.',
                'errors' => ['channels' => ['Pick at least one delivery channel.']],
            ], 422));
        }

        return $data;
    }
}
