<x-layouts::app :title="__('Notification Rules')">
    <div class="space-y-5">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">{{ __('Notification Rules') }}</h1>
                <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Decide who hears about what. Target a whole role, or one person.') }}</p>
            </div>
            <button type="button" id="btn-add-rule" class="inline-flex shrink-0 items-center gap-1.5 rounded-xl bg-sky-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-sky-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                {{ __('Add Rule') }}
            </button>
        </div>

        @if ($mailerIsLog)
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                <p class="font-semibold">{{ __('Email is not actually being sent') }}</p>
                <p class="mt-1 text-amber-800">
                    {{ __('MAIL_MAILER is set to "log", so email notifications are written to storage/logs and never delivered. In-app notifications work normally. Configure SMTP before relying on the email channel.') }}
                </p>
            </div>
        @endif

        @foreach ($events as $event => $meta)
            @php($eventRules = $rules->get($event, collect()))
            <section class="nr-card">
                <header class="nr-card-head">
                    <div>
                        <h2>{{ $meta['label'] }}</h2>
                        <p>{{ $meta['description'] }}</p>
                    </div>
                    <code class="nr-event">{{ $event }}</code>
                </header>

                @if ($eventRules->isEmpty())
                    <p class="nr-none">{{ __('Nobody is notified about this. Add a rule to change that.') }}</p>
                @else
                    <ul class="nr-list">
                        @foreach ($eventRules as $rule)
                            <li class="nr-row"
                                data-id="{{ $rule->id }}"
                                data-event="{{ $rule->event }}"
                                data-recipient-type="{{ $rule->recipient_type }}"
                                data-recipient-value="{{ $rule->recipient_value }}"
                                data-database="{{ $rule->via_database ? '1' : '0' }}"
                                data-mail="{{ $rule->via_mail ? '1' : '0' }}"
                                data-active="{{ $rule->is_active ? '1' : '0' }}"
                            >
                                <span @class(['nr-kind', 'is-role' => $rule->targetsRole(), 'is-user' => ! $rule->targetsRole()])>
                                    {{ $rule->targetsRole() ? __('Role') : __('User') }}
                                </span>

                                <div class="min-w-0 flex-1">
                                    <span class="nr-name">{{ $rule->recipientLabel() }}</span>
                                    @unless ($rule->targetsRole())
                                        <span class="nr-email">{{ $rule->user?->email }}</span>
                                    @endunless
                                </div>

                                <div class="nr-channels">
                                    @if ($rule->via_database)
                                        <span class="nr-chip is-db">{{ __('In-app') }}</span>
                                    @endif
                                    @if ($rule->via_mail)
                                        <span class="nr-chip is-mail">{{ __('Email') }}</span>
                                    @endif
                                </div>

                                <button type="button" @class(['nr-toggle', 'is-on' => $rule->is_active]) data-toggle-url="{{ route('admin.notification-rules.toggle', $rule) }}" title="{{ $rule->is_active ? __('Enabled') : __('Disabled') }}">
                                    <span class="nr-toggle-knob"></span>
                                </button>

                                <div class="nr-actions">
                                    <button type="button" class="nr-btn is-primary rule-edit-btn">{{ __('Edit') }}</button>
                                    <button type="button" class="nr-btn is-danger rule-delete-btn" data-destroy-url="{{ route('admin.notification-rules.destroy', $rule) }}">{{ __('Remove') }}</button>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        @endforeach
    </div>

    {{-- Add / edit modal --}}
    <div id="rule-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" aria-hidden="true">
        <div id="rule-modal-overlay" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>
        <div class="relative w-full max-w-md rounded-2xl border border-slate-200 bg-white p-5 shadow-xl dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 id="rm-title" class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Add Notification Rule') }}</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-zinc-400">{{ __('Role rules cover whoever holds that role at the time, including future staff.') }}</p>
                </div>
                <button id="rm-close" type="button" class="rounded-full bg-slate-100 p-1.5 text-slate-500 transition hover:bg-slate-200 dark:bg-zinc-800 dark:text-zinc-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                </button>
            </div>

            <div class="mt-4 space-y-3">
                <div>
                    <label for="rm-event" class="nr-label">{{ __('Notify when') }}</label>
                    <select id="rm-event" class="nr-input">
                        @foreach ($events as $value => $meta)
                            <option value="{{ $value }}">{{ $meta['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="nr-label">{{ __('Notify who') }}</label>
                    <div class="flex gap-2">
                        <label class="nr-seg"><input type="radio" name="rm-type" value="role" checked> {{ __('A role') }}</label>
                        <label class="nr-seg"><input type="radio" name="rm-type" value="user"> {{ __('One person') }}</label>
                    </div>
                </div>

                <div id="rm-role-wrap">
                    <label for="rm-role" class="nr-label">{{ __('Role') }}</label>
                    <select id="rm-role" class="nr-input">
                        @foreach ($roles as $role)
                            <option value="{{ $role }}">{{ ucwords(str_replace('_', ' ', $role)) }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="rm-user-wrap" class="hidden">
                    <label for="rm-user" class="nr-label">{{ __('Person') }}</label>
                    <select id="rm-user" class="nr-input">
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} — {{ $user->email }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="nr-label">{{ __('Deliver by') }}</label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-xs text-slate-600 dark:text-zinc-300">
                            <input id="rm-db" type="checkbox" checked class="size-3.5 rounded"> {{ __('In-app (bell)') }}
                        </label>
                        <label class="flex items-center gap-2 text-xs text-slate-600 dark:text-zinc-300">
                            <input id="rm-mail" type="checkbox" class="size-3.5 rounded"> {{ __('Email') }}
                            @if ($mailerIsLog)
                                <span class="text-[0.62rem] text-amber-600">{{ __('(not configured)') }}</span>
                            @endif
                        </label>
                    </div>
                    <p id="rm-channel-error" class="nr-err hidden"></p>
                </div>

                <div class="flex items-center justify-between gap-3 pt-1">
                    <label for="rm-active" class="nr-label mb-0">{{ __('Enabled') }}</label>
                    <input id="rm-active" type="checkbox" checked class="size-4 rounded border-slate-300 text-sky-600">
                </div>
            </div>

            <div class="mt-4 flex items-center justify-end gap-2 border-t border-slate-100 pt-3 dark:border-zinc-800">
                <button id="rm-cancel" type="button" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-600 transition hover:bg-slate-50 dark:border-zinc-700 dark:bg-transparent dark:text-zinc-300">{{ __('Cancel') }}</button>
                <button id="rm-submit" type="button" class="rounded-lg bg-sky-600 px-3 py-2 text-xs font-medium text-white transition hover:bg-sky-700 disabled:opacity-60">{{ __('Save Rule') }}</button>
            </div>
        </div>
    </div>

    <style>
        .nr-card { background:#fff; border:1px solid #dbe4f0; border-radius:18px; padding:1rem 1.1rem; }
        .nr-card-head { align-items:flex-start; border-bottom:1px solid #eef2f7; display:flex; gap:1rem; justify-content:space-between; margin-bottom:.8rem; padding-bottom:.7rem; }
        .nr-card-head h2 { color:#0f172a; font-size:.88rem; font-weight:700; }
        .nr-card-head p { color:#64748b; font-size:.73rem; margin-top:.12rem; }
        .nr-event { background:#f1f5f9; border-radius:6px; color:#64748b; flex-shrink:0; font-family:ui-monospace,monospace; font-size:.64rem; padding:.16rem .4rem; }
        .nr-none { color:#94a3b8; font-size:.75rem; font-style:italic; padding:.4rem 0; }
        .nr-list { display:flex; flex-direction:column; gap:.5rem; list-style:none; margin:0; padding:0; }
        .nr-row { align-items:center; background:#f8fafc; border:1px solid #eef2f7; border-radius:11px; display:flex; gap:.7rem; padding:.55rem .7rem; }
        .nr-kind { border-radius:6px; flex-shrink:0; font-size:.6rem; font-weight:700; letter-spacing:.04em; padding:.16rem .4rem; text-transform:uppercase; }
        .nr-kind.is-role { background:#e0e7ff; color:#4338ca; }
        .nr-kind.is-user { background:#fae8ff; color:#a21caf; }
        .nr-name { color:#0f172a; font-size:.78rem; font-weight:600; }
        .nr-email { color:#94a3b8; display:block; font-size:.65rem; }
        .nr-channels { display:flex; flex-shrink:0; gap:.25rem; }
        .nr-chip { border-radius:9999px; font-size:.58rem; font-weight:600; padding:.14rem .42rem; }
        .nr-chip.is-db { background:#dbeafe; color:#1d4ed8; }
        .nr-chip.is-mail { background:#dcfce7; color:#15803d; }
        .nr-toggle { background:#cbd5e1; border-radius:9999px; flex-shrink:0; height:1.1rem; position:relative; transition:background .15s ease; width:2rem; }
        .nr-toggle.is-on { background:#059669; }
        .nr-toggle-knob { background:#fff; border-radius:9999px; height:.85rem; left:.13rem; position:absolute; top:.125rem; transition:left .15s ease; width:.85rem; }
        .nr-toggle.is-on .nr-toggle-knob { left:1.02rem; }
        .nr-actions { display:flex; flex-shrink:0; gap:.3rem; }
        .nr-btn { border:1px solid transparent; border-radius:7px; font-size:.64rem; font-weight:600; padding:.26rem .5rem; }
        .nr-btn.is-primary { background:#059669; color:#fff; }
        .nr-btn.is-danger { background:#fff; border-color:#fecaca; color:#dc2626; }
        .nr-label { color:#334155; display:block; font-size:.72rem; font-weight:600; margin-bottom:.3rem; }
        .nr-input { background:#fff; border:1px solid #e2e8f0; border-radius:8px; font-size:.76rem; padding:.45rem .6rem; width:100%; }
        .nr-input:focus { border-color:#0ea5e9; box-shadow:0 0 0 2px rgba(14,165,233,.16); outline:none; }
        .nr-seg { align-items:center; border:1px solid #e2e8f0; border-radius:8px; cursor:pointer; display:flex; flex:1; font-size:.72rem; gap:.35rem; padding:.4rem .55rem; }
        .nr-err { color:#e11d48; font-size:.68rem; margin-top:.3rem; }
        .dark .nr-card { background:#18181b; border-color:#3f3f46; }
        .dark .nr-card-head { border-color:#27272a; }
        .dark .nr-card-head h2 { color:#fafafa; }
        .dark .nr-row { background:#09090b; border-color:#27272a; }
        .dark .nr-name { color:#fafafa; }
        .dark .nr-event { background:#27272a; }
        .dark .nr-label { color:#d4d4d8; }
        .dark .nr-input, .dark .nr-seg { background:#09090b; border-color:#3f3f46; color:#fafafa; }
    </style>

    <script>
        (() => {
        const CSRF = '{{ csrf_token() }}';
        const STORE_URL = @js(route('admin.notification-rules.store'));
        const BASE_URL = @js(url('admin/notification-rules'));
        const $id = (i) => document.getElementById(i);
        const modal = $id('rule-modal');
        let editingId = null;

        const syncType = () => {
            const type = document.querySelector('input[name="rm-type"]:checked').value;
            $id('rm-role-wrap').classList.toggle('hidden', type !== 'role');
            $id('rm-user-wrap').classList.toggle('hidden', type !== 'user');
        };
        document.querySelectorAll('input[name="rm-type"]').forEach((r) => r.addEventListener('change', syncType));

        const open = () => { modal.classList.remove('hidden'); modal.classList.add('flex'); modal.setAttribute('aria-hidden', 'false'); };
        const close = () => { modal.classList.add('hidden'); modal.classList.remove('flex'); modal.setAttribute('aria-hidden', 'true'); $id('rm-channel-error').classList.add('hidden'); };

        const setMode = (mode, row = null) => {
            $id('rm-channel-error').classList.add('hidden');

            if (mode === 'create') {
                editingId = null;
                $id('rm-title').textContent = @js(__('Add Notification Rule'));
                $id('rm-submit').textContent = @js(__('Save Rule'));
                document.querySelector('input[name="rm-type"][value="role"]').checked = true;
                $id('rm-db').checked = true;
                $id('rm-mail').checked = false;
                $id('rm-active').checked = true;
            } else {
                editingId = Number(row.dataset.id);
                $id('rm-title').textContent = @js(__('Edit Notification Rule'));
                $id('rm-submit').textContent = @js(__('Update Rule'));
                $id('rm-event').value = row.dataset.event;
                document.querySelector(`input[name="rm-type"][value="${row.dataset.recipientType}"]`).checked = true;
                if (row.dataset.recipientType === 'role') {
                    $id('rm-role').value = row.dataset.recipientValue;
                } else {
                    $id('rm-user').value = row.dataset.recipientValue;
                }
                $id('rm-db').checked = row.dataset.database === '1';
                $id('rm-mail').checked = row.dataset.mail === '1';
                $id('rm-active').checked = row.dataset.active === '1';
            }

            syncType();
        };

        $id('rm-submit').addEventListener('click', async () => {
            const type = document.querySelector('input[name="rm-type"]:checked').value;

            if (!$id('rm-db').checked && !$id('rm-mail').checked) {
                const err = $id('rm-channel-error');
                err.textContent = @js(__('Pick at least one delivery channel.'));
                err.classList.remove('hidden');
                return;
            }

            const payload = {
                event: $id('rm-event').value,
                recipient_type: type,
                recipient_value: type === 'role' ? $id('rm-role').value : $id('rm-user').value,
                via_database: $id('rm-db').checked,
                via_mail: $id('rm-mail').checked,
                is_active: $id('rm-active').checked,
            };

            const btn = $id('rm-submit');
            btn.disabled = true;
            const original = btn.textContent;
            btn.textContent = @js(__('Saving...'));

            try {
                const res = await fetch(editingId ? `${BASE_URL}/${editingId}` : STORE_URL, {
                    method: editingId ? 'PUT' : 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify(payload),
                });
                const json = await res.json();

                if (res.status === 422) {
                    iziToast.warning({ title: 'Not saved', message: json.message, position: 'topRight' });
                    return;
                }
                if (!res.ok) throw new Error(json.message ?? 'Server error');

                close();
                iziToast.success({ title: editingId ? 'Updated' : 'Added', message: json.message, position: 'topRight' });
                window.location.reload();
            } catch (err) {
                iziToast.error({ title: 'Error', message: err.message, position: 'topRight' });
            } finally {
                btn.disabled = false;
                btn.textContent = original;
            }
        });

        document.addEventListener('click', async (e) => {
            const editBtn = e.target.closest('.rule-edit-btn');
            if (editBtn) { setMode('edit', editBtn.closest('.nr-row')); open(); return; }

            const toggleBtn = e.target.closest('.nr-toggle');
            if (toggleBtn) {
                try {
                    const res = await fetch(toggleBtn.dataset.toggleUrl, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    });
                    const json = await res.json();
                    if (!res.ok) throw new Error(json.message);
                    toggleBtn.classList.toggle('is-on', json.is_active);
                    toggleBtn.closest('.nr-row').dataset.active = json.is_active ? '1' : '0';
                    iziToast.success({ title: 'Saved', message: json.message, position: 'topRight', timeout: 1500 });
                } catch (err) {
                    iziToast.error({ title: 'Error', message: err.message, position: 'topRight' });
                }
                return;
            }

            const delBtn = e.target.closest('.rule-delete-btn');
            if (!delBtn) return;

            iziToast.question({
                timeout: 0, close: false, overlay: true, displayMode: 'once',
                title: 'Remove rule', message: 'Stop notifying this recipient about this event?', position: 'center',
                buttons: [
                    ['<button>Yes, remove</button>', async (instance, toast) => {
                        instance.hide({ transitionOut: 'fadeOut' }, toast);
                        try {
                            const res = await fetch(delBtn.dataset.destroyUrl, {
                                method: 'DELETE',
                                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                            });
                            const json = await res.json();
                            if (!res.ok) throw new Error(json.message);
                            iziToast.success({ title: 'Removed', message: json.message, position: 'topRight' });
                            window.location.reload();
                        } catch (err) {
                            iziToast.error({ title: 'Error', message: err.message, position: 'topRight' });
                        }
                    }, true],
                    ['<button>Cancel</button>', (instance, toast) => instance.hide({ transitionOut: 'fadeOut' }, toast)],
                ],
            });
        });

        $id('btn-add-rule').addEventListener('click', () => { setMode('create'); open(); });
        ['rm-close', 'rm-cancel'].forEach((i) => $id(i).addEventListener('click', close));
        $id('rule-modal-overlay').addEventListener('click', close);
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.getAttribute('aria-hidden') === 'false') close();
        });
        })();
    </script>
</x-layouts::app>
