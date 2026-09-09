<x-layouts::app :title="__('Roles & Permissions')">
    <div class="space-y-5">

        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">{{ __('Roles & Permissions') }}</h1>
                <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Tick a box to grant a permission. Changes take effect on the next request — no deploy needed.') }}
                </p>
            </div>
            <button type="button" id="btn-add-role" class="inline-flex shrink-0 items-center gap-1.5 rounded-xl bg-sky-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-sky-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                {{ __('New Role') }}
            </button>
        </div>

        <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 text-sm text-sky-900 dark:border-sky-900 dark:bg-sky-950/40 dark:text-sky-200">
            <p class="font-semibold">{{ __('Only a superadmin can open this screen') }}</p>
            <p class="mt-1">
                {{ __('Managing permissions is tied to the superadmin role itself, not to a permission — so it cannot be granted away from this screen. An admin cannot give themselves access to it.') }}
            </p>
        </div>

        {{-- The matrix --}}
        <div class="rp-wrap">
            <table class="rp-table">
                <thead>
                    <tr>
                        <th class="rp-corner">{{ __('Permission') }}</th>
                        @foreach ($roles as $role)
                            <th class="rp-role">
                                <span class="rp-role-label">{{ $role->label }}</span>
                                <span class="rp-role-slug">{{ $role->name }}</span>
                                <span class="rp-role-users">
                                    {{ trans_choice('{0} no users|{1} :count user|[2,*] :count users', $role->users_count, ['count' => $role->users_count]) }}
                                </span>
                                @if ($role->is_superadmin)
                                    <span class="rp-badge is-super">{{ __('all access') }}</span>
                                @elseif ($role->is_system)
                                    <span class="rp-badge is-system">{{ __('built-in') }}</span>
                                @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    @foreach ($permissions as $group => $groupPermissions)
                        <tr class="rp-group">
                            <td colspan="{{ $roles->count() + 1 }}">{{ $group }}</td>
                        </tr>

                        @foreach ($groupPermissions as $permission)
                            <tr>
                                <th scope="row" class="rp-perm">
                                    <span class="rp-perm-label">{{ $permission->label }}</span>
                                    <span class="rp-perm-name">{{ $permission->name }}</span>
                                </th>

                                @foreach ($roles as $role)
                                    <td class="rp-cell">
                                        @if ($role->is_superadmin)
                                            <span class="rp-always" title="{{ __('Superadmin always has every permission') }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            </span>
                                        @else
                                            <label class="rp-check">
                                                <input
                                                    type="checkbox"
                                                    data-role="{{ $role->id }}"
                                                    data-permission="{{ $permission->id }}"
                                                    @checked($role->permissions->contains('id', $permission->id))
                                                >
                                                <span class="sr-only">{{ $permission->label }} — {{ $role->label }}</span>
                                            </label>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <p id="rp-status" class="text-xs text-slate-400">{{ __('No unsaved changes.') }}</p>
            <div class="flex gap-2">
                <button type="button" id="rp-reset" class="rp-action is-ghost" disabled>
                    {{ __('Discard changes') }}
                </button>
                <button type="button" id="rp-save" class="rp-action is-save" disabled>
                    {{ __('Save permissions') }}
                </button>
            </div>
        </div>

        {{-- Role details --}}
        <section class="rp-card">
            <header class="rp-card-head"><h2>{{ __('Roles') }}</h2></header>
            <ul class="rp-roles">
                @foreach ($roles as $role)
                    <li class="rp-role-row" data-id="{{ $role->id }}" data-label="{{ e($role->label) }}" data-description="{{ e((string) $role->description) }}">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="rp-role-name">{{ $role->label }}</span>
                                <code class="rp-slug">{{ $role->name }}</code>
                                @if ($role->is_superadmin)
                                    <span class="rp-badge is-super">{{ __('all access') }}</span>
                                @elseif ($role->is_system)
                                    <span class="rp-badge is-system">{{ __('built-in') }}</span>
                                @endif
                            </div>
                            <p class="rp-role-desc">{{ $role->description ?: __('No description') }}</p>
                            <p class="rp-role-meta">
                                {{ $role->is_superadmin ? __('every permission') : trans_choice('{0} no permissions|{1} :count permission|[2,*] :count permissions', $role->permissions->count(), ['count' => $role->permissions->count()]) }}
                                <span class="text-slate-300">·</span>
                                {{ trans_choice('{0} no users|{1} :count user|[2,*] :count users', $role->users_count, ['count' => $role->users_count]) }}
                            </p>
                        </div>

                        <div class="flex shrink-0 gap-1.5">
                            <button type="button" class="rp-btn is-primary role-edit-btn">{{ __('Rename') }}</button>
                            @if ($role->canBeDeleted())
                                <button type="button" class="rp-btn is-danger role-delete-btn" data-url="{{ route('admin.roles.destroy', $role) }}">{{ __('Delete') }}</button>
                            @else
                                <button type="button" class="rp-btn is-muted" disabled title="{{ $role->is_superadmin || $role->is_system ? __('Built-in role') : __('Users still assigned') }}">{{ __('Locked') }}</button>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>

        @if ($protectedUsers->isNotEmpty())
            <section class="rp-card">
                <header class="rp-card-head"><h2>{{ __('Protected accounts') }}</h2></header>
                <p class="mb-3 text-xs text-slate-500 dark:text-zinc-400">
                    {{ __('These cannot be deleted or moved off superadmin, by anyone, from anywhere in the application. They are the way back in if something goes wrong.') }}
                </p>
                <ul class="rp-roles">
                    @foreach ($protectedUsers as $user)
                        <li class="rp-role-row">
                            <span class="rp-badge is-super">{{ __('protected') }}</span>
                            <div class="min-w-0 flex-1">
                                <span class="rp-role-name">{{ $user->name }}</span>
                                <p class="rp-role-desc">{{ $user->email }}</p>
                            </div>
                            <code class="rp-slug">{{ $user->role }}</code>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <p class="text-xs text-slate-400">
            {{ __('Assigning a role to a person needs the Users screen, which is not built yet — roles are currently set by seeding or the user:superadmin command.') }}
        </p>
    </div>

    {{-- Role modal --}}
    <div id="role-modal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4" aria-hidden="true">
        <div id="role-modal-overlay" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>
        <div class="relative w-full max-w-md rounded-2xl border border-slate-200 bg-white p-5 shadow-xl dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 id="rm-title" class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('New Role') }}</h2>
                    <p id="rm-desc" class="mt-0.5 text-xs text-slate-500 dark:text-zinc-400">{{ __('Give it a name, then tick its permissions in the grid.') }}</p>
                </div>
                <button id="rm-close" type="button" class="rounded-full bg-slate-100 p-1.5 text-slate-500 transition hover:bg-slate-200 dark:bg-zinc-800 dark:text-zinc-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                </button>
            </div>

            <div class="mt-4 space-y-3">
                <div>
                    <label for="rm-label" class="rp-label">{{ __('Role name') }}</label>
                    <input id="rm-label" type="text" class="rp-input" placeholder="{{ __('e.g. Training Officer') }}">
                    <p id="rm-label-error" class="rp-err hidden"></p>
                </div>
                <div>
                    <label for="rm-description" class="rp-label">{{ __('Description') }} <span class="font-normal text-slate-400">({{ __('optional') }})</span></label>
                    <textarea id="rm-description" rows="2" class="rp-input" placeholder="{{ __('What this role is for') }}"></textarea>
                </div>
                <p id="rm-slug-note" class="hidden text-xs text-amber-600"></p>
            </div>

            <div class="mt-4 flex items-center justify-end gap-2 border-t border-slate-100 pt-3 dark:border-zinc-800">
                <button id="rm-cancel" type="button" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-600 transition hover:bg-slate-50 dark:border-zinc-700 dark:bg-transparent dark:text-zinc-300">{{ __('Cancel') }}</button>
                <button id="rm-submit" type="button" class="rounded-lg bg-sky-600 px-3 py-2 text-xs font-medium text-white transition hover:bg-sky-700 disabled:opacity-60">{{ __('Create Role') }}</button>
            </div>
        </div>
    </div>

    <style>
        /*
         * isolate keeps the sticky header and first column's z-index inside
         * this box. Without it they sit in the page's root stacking context and
         * paint straight over the modal.
         */
        .rp-wrap { background:#fff; border:1px solid #dbe4f0; border-radius:16px; overflow-x:auto; isolation:isolate; position:relative; z-index:0; }
        .rp-table { border-collapse:separate; border-spacing:0; min-width:640px; width:100%; }
        .rp-corner { background:#f8fafc; border-bottom:1px solid #e2e8f0; color:#64748b; font-size:.66rem; font-weight:700; left:0; letter-spacing:.06em; padding:.7rem .9rem; position:sticky; text-align:left; text-transform:uppercase; z-index:2; }
        .rp-role { background:#f8fafc; border-bottom:1px solid #e2e8f0; border-left:1px solid #eef2f7; min-width:8.5rem; padding:.7rem .6rem; text-align:center; vertical-align:top; }
        .rp-role-label { color:#0f172a; display:block; font-size:.76rem; font-weight:700; }
        .rp-role-slug { color:#94a3b8; display:block; font-family:ui-monospace,monospace; font-size:.6rem; margin-top:.1rem; }
        .rp-role-users { color:#94a3b8; display:block; font-size:.6rem; margin-top:.2rem; }
        .rp-badge { border-radius:9999px; display:inline-block; font-size:.55rem; font-weight:700; letter-spacing:.04em; margin-top:.3rem; padding:.1rem .38rem; text-transform:uppercase; }
        .rp-badge.is-super { background:#fef3c7; color:#b45309; }
        .rp-badge.is-system { background:#e2e8f0; color:#64748b; }
        .rp-group td { background:#f1f5f9; color:#475569; font-size:.62rem; font-weight:700; letter-spacing:.08em; padding:.4rem .9rem; text-transform:uppercase; }
        .rp-perm { background:#fff; border-bottom:1px solid #f1f5f9; left:0; padding:.6rem .9rem; position:sticky; text-align:left; z-index:1; }
        .rp-perm-label { color:#0f172a; display:block; font-size:.76rem; font-weight:600; }
        .rp-perm-name { color:#94a3b8; display:block; font-family:ui-monospace,monospace; font-size:.62rem; }
        .rp-cell { border-bottom:1px solid #f1f5f9; border-left:1px solid #f8fafc; padding:.5rem; text-align:center; }
        .rp-check input { accent-color:#059669; cursor:pointer; height:1.05rem; width:1.05rem; }
        .rp-check input.is-dirty { outline:2px solid #f59e0b; outline-offset:2px; }
        .rp-always { color:#d97706; display:inline-flex; }

        .rp-card { background:#fff; border:1px solid #dbe4f0; border-radius:16px; padding:1rem 1.1rem; }
        .rp-card-head { border-bottom:1px solid #eef2f7; margin-bottom:.8rem; padding-bottom:.6rem; }
        .rp-card-head h2 { color:#0f172a; font-size:.86rem; font-weight:700; }
        .rp-roles { display:flex; flex-direction:column; gap:.5rem; list-style:none; margin:0; padding:0; }
        .rp-role-row { align-items:center; background:#f8fafc; border:1px solid #eef2f7; border-radius:12px; display:flex; gap:1rem; padding:.7rem .85rem; }
        .rp-role-name { color:#0f172a; font-size:.8rem; font-weight:600; }
        .rp-slug { background:#e2e8f0; border-radius:5px; color:#475569; font-size:.62rem; padding:.08rem .3rem; }
        .rp-role-desc { color:#64748b; font-size:.72rem; margin-top:.15rem; }
        .rp-role-meta { color:#94a3b8; font-size:.66rem; margin-top:.2rem; }
        .rp-btn { border:1px solid transparent; border-radius:7px; font-size:.66rem; font-weight:600; padding:.28rem .55rem; }
        .rp-btn.is-primary { background:#0284c7; color:#fff; }
        .rp-btn.is-danger { background:#fff; border-color:#fecaca; color:#dc2626; }
        .rp-btn.is-muted { background:#f1f5f9; color:#94a3b8; cursor:not-allowed; }
        /*
         * Written as real CSS rather than Tailwind utilities. The compiled
         * stylesheet in public/build is only rebuilt by `npm run build`, so a
         * class that was not already used somewhere in the app resolves to
         * nothing -- which is how the save button ended up as white text on a
         * white card.
         */
        .rp-action { border:1px solid transparent; border-radius:9px; font-size:.75rem; font-weight:600; padding:.5rem .9rem; transition:background-color .15s ease, opacity .15s ease; }
        .rp-action:disabled { cursor:not-allowed; opacity:.45; }
        .rp-action.is-save { background:#059669; color:#fff; }
        .rp-action.is-save:not(:disabled):hover { background:#047857; }
        .rp-action.is-ghost { background:#fff; border-color:#e2e8f0; color:#475569; }
        .rp-action.is-ghost:not(:disabled):hover { background:#f8fafc; }
        .dark .rp-action.is-ghost { background:transparent; border-color:#3f3f46; color:#d4d4d8; }
        .dark .rp-action.is-ghost:not(:disabled):hover { background:#27272a; }

        .rp-label { color:#334155; display:block; font-size:.72rem; font-weight:600; margin-bottom:.3rem; }
        .rp-input { background:#fff; border:1px solid #e2e8f0; border-radius:8px; font-size:.76rem; padding:.45rem .6rem; width:100%; }
        .rp-input:focus { border-color:#0ea5e9; box-shadow:0 0 0 2px rgba(14,165,233,.16); outline:none; }
        .rp-err { color:#e11d48; font-size:.68rem; margin-top:.3rem; }

        .dark .rp-wrap, .dark .rp-card { background:#18181b; border-color:#3f3f46; }
        .dark .rp-corner, .dark .rp-role { background:#09090b; border-color:#27272a; }
        .dark .rp-role-label, .dark .rp-perm-label, .dark .rp-role-name, .dark .rp-card-head h2 { color:#fafafa; }
        .dark .rp-group td { background:#27272a; color:#a1a1aa; }
        .dark .rp-perm { background:#18181b; border-color:#27272a; }
        .dark .rp-cell { border-color:#27272a; }
        .dark .rp-role-row { background:#09090b; border-color:#27272a; }
        .dark .rp-slug { background:#27272a; color:#a1a1aa; }
        .dark .rp-label { color:#d4d4d8; }
        .dark .rp-input { background:#09090b; border-color:#3f3f46; color:#fafafa; }
    </style>

    <script>
        (() => {
        const CSRF = '{{ csrf_token() }}';
        const BASE = @js(url('admin/roles'));
        const $id = (i) => document.getElementById(i);
        const modal = $id('role-modal');
        let editingId = null;

        // ---------- matrix ----------

        const boxes = [...document.querySelectorAll('.rp-check input')];
        const original = new Map(boxes.map((b) => [b, b.checked]));
        const status = $id('rp-status');
        const saveBtn = $id('rp-save');
        const resetBtn = $id('rp-reset');

        const dirtyBoxes = () => boxes.filter((b) => original.get(b) !== b.checked);

        const syncDirty = () => {
            const dirty = dirtyBoxes();
            boxes.forEach((b) => b.classList.toggle('is-dirty', original.get(b) !== b.checked));

            saveBtn.disabled = dirty.length === 0;
            resetBtn.disabled = dirty.length === 0;
            status.textContent = dirty.length === 0
                ? @js(__('No unsaved changes.'))
                : dirty.length + ' ' + @js(__('unsaved change(s). Nothing is applied until you save.'));
            status.className = dirty.length === 0 ? 'text-xs text-slate-400' : 'text-xs font-medium text-amber-600';
        };

        boxes.forEach((b) => b.addEventListener('change', syncDirty));

        resetBtn.addEventListener('click', () => {
            boxes.forEach((b) => { b.checked = original.get(b); });
            syncDirty();
        });

        saveBtn.addEventListener('click', async () => {
            // Only send roles that actually changed.
            const roleIds = [...new Set(dirtyBoxes().map((b) => b.dataset.role))];

            saveBtn.disabled = true;
            const label = saveBtn.textContent;
            saveBtn.textContent = @js(__('Saving...'));

            try {
                for (const roleId of roleIds) {
                    const permissions = boxes
                        .filter((b) => b.dataset.role === roleId && b.checked)
                        .map((b) => Number(b.dataset.permission));

                    const res = await fetch(`${BASE}/${roleId}/permissions`, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                        body: JSON.stringify({ permissions }),
                    });
                    const json = await res.json();
                    if (!res.ok) throw new Error(json.message ?? 'Save failed');
                }

                boxes.forEach((b) => original.set(b, b.checked));
                syncDirty();
                iziToast.success({ title: 'Saved', message: @js(__('Permissions updated.')), position: 'topRight' });
            } catch (err) {
                iziToast.error({ title: 'Error', message: err.message, position: 'topRight' });
            } finally {
                saveBtn.textContent = label;
                syncDirty();
            }
        });

        window.addEventListener('beforeunload', (e) => {
            if (dirtyBoxes().length) { e.preventDefault(); e.returnValue = ''; }
        });

        // ---------- role modal ----------

        const open = () => { modal.classList.remove('hidden'); modal.classList.add('flex'); modal.setAttribute('aria-hidden', 'false'); $id('rm-label').focus(); };
        const close = () => { modal.classList.add('hidden'); modal.classList.remove('flex'); modal.setAttribute('aria-hidden', 'true'); $id('rm-label-error').classList.add('hidden'); };

        const setMode = (mode, row = null) => {
            $id('rm-label-error').classList.add('hidden');
            $id('rm-slug-note').classList.add('hidden');

            if (mode === 'create') {
                editingId = null;
                $id('rm-title').textContent = @js(__('New Role'));
                $id('rm-desc').textContent = @js(__('Give it a name, then tick its permissions in the grid.'));
                $id('rm-submit').textContent = @js(__('Create Role'));
                $id('rm-label').value = '';
                $id('rm-description').value = '';
            } else {
                editingId = Number(row.dataset.id);
                $id('rm-title').textContent = @js(__('Rename Role'));
                $id('rm-desc').textContent = @js(__('The display name and description only.'));
                $id('rm-submit').textContent = @js(__('Save Changes'));
                $id('rm-label').value = row.dataset.label;
                $id('rm-description').value = row.dataset.description;
                const note = $id('rm-slug-note');
                note.textContent = @js(__('The internal key stays the same — code and existing user records refer to it.'));
                note.classList.remove('hidden');
            }
        };

        $id('rm-submit').addEventListener('click', async () => {
            const label = $id('rm-label').value.trim();
            if (!label) {
                const err = $id('rm-label-error');
                err.textContent = @js(__('A role name is required.'));
                err.classList.remove('hidden');
                return;
            }

            const btn = $id('rm-submit');
            btn.disabled = true;
            const text = btn.textContent;
            btn.textContent = @js(__('Saving...'));

            try {
                const res = await fetch(editingId ? `${BASE}/${editingId}` : BASE, {
                    method: editingId ? 'PUT' : 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify({ label, description: $id('rm-description').value.trim() || null }),
                });
                const json = await res.json();
                if (res.status === 422) { iziToast.warning({ title: 'Not saved', message: json.message, position: 'topRight' }); return; }
                if (!res.ok) throw new Error(json.message ?? 'Server error');

                close();
                iziToast.success({ title: 'Saved', message: json.message, position: 'topRight' });
                window.location.reload();
            } catch (err) {
                iziToast.error({ title: 'Error', message: err.message, position: 'topRight' });
            } finally {
                btn.disabled = false;
                btn.textContent = text;
            }
        });

        document.addEventListener('click', (e) => {
            const editBtn = e.target.closest('.role-edit-btn');
            if (editBtn) { setMode('edit', editBtn.closest('.rp-role-row')); open(); return; }

            const delBtn = e.target.closest('.role-delete-btn');
            if (!delBtn) return;

            iziToast.question({
                timeout: 0, close: false, overlay: true, displayMode: 'once',
                title: 'Delete role', message: 'Remove this role? This cannot be undone.', position: 'center',
                buttons: [
                    ['<button>Yes, delete</button>', async (instance, toast) => {
                        instance.hide({ transitionOut: 'fadeOut' }, toast);
                        try {
                            const res = await fetch(delBtn.dataset.url, {
                                method: 'DELETE',
                                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                            });
                            const json = await res.json();
                            if (!res.ok) throw new Error(json.message);
                            iziToast.success({ title: 'Deleted', message: json.message, position: 'topRight' });
                            window.location.reload();
                        } catch (err) {
                            iziToast.error({ title: 'Error', message: err.message, position: 'topRight' });
                        }
                    }, true],
                    ['<button>Cancel</button>', (instance, toast) => instance.hide({ transitionOut: 'fadeOut' }, toast)],
                ],
            });
        });

        $id('btn-add-role').addEventListener('click', () => { setMode('create'); open(); });
        ['rm-close', 'rm-cancel'].forEach((i) => $id(i).addEventListener('click', close));
        $id('role-modal-overlay').addEventListener('click', close);
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.getAttribute('aria-hidden') === 'false') close();
        });

        syncDirty();
        })();
    </script>
</x-layouts::app>
