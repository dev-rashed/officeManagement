<x-layouts::app :title="__('Users')">
    <div class="space-y-5">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">{{ __('Users') }}</h1>
                <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Add staff, set their role, and switch accounts off without losing their history.') }}</p>
            </div>
            <button type="button" id="btn-add-user" class="inline-flex shrink-0 items-center gap-1.5 rounded-xl bg-sky-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-sky-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                {{ __('Add User') }}
            </button>
        </div>

        <div class="grid gap-3 sm:grid-cols-3">
            <div class="user-stat"><p class="user-stat-label">{{ __('Total accounts') }}</p><p class="user-stat-value">{{ number_format($counts['total']) }}</p></div>
            <div class="user-stat"><p class="user-stat-label">{{ __('Active') }}</p><p class="user-stat-value">{{ number_format($counts['active']) }}</p></div>
            <div class="user-stat"><p class="user-stat-label">{{ __('Disabled') }}</p><p class="user-stat-value">{{ number_format($counts['disabled']) }}</p></div>
        </div>

        @unless (auth()->user()->isSuperAdmin())
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs text-slate-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400">
                {{ __('You can manage every role except superadmin. Only a superadmin can create, edit or act on a superadmin account.') }}
            </div>
        @endunless

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table id="users-table" data-ajax-url="{{ route('admin.users.data') }}" class="min-w-full text-left" style="width:100%">
                    <thead>
                        <tr>
                            <th>{{ __('User') }}</th>
                            <th>{{ __('Role') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Added') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Add / edit modal --}}
    <div id="user-modal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4" aria-hidden="true">
        <div id="user-modal-overlay" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>
        <div class="relative w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-5 shadow-xl dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 id="um-title" class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Add User') }}</h2>
                    <p id="um-desc" class="mt-0.5 text-xs text-slate-500 dark:text-zinc-400">{{ __('They can sign in straight away — the address is treated as verified.') }}</p>
                </div>
                <button id="um-close" type="button" class="rounded-full bg-slate-100 p-1.5 text-slate-500 transition hover:bg-slate-200 dark:bg-zinc-800 dark:text-zinc-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                </button>
            </div>

            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <div>
                    <label for="um-name" class="user-label">{{ __('Full name') }}</label>
                    <input id="um-name" type="text" class="user-input">
                    <p data-err="name" class="user-err hidden"></p>
                </div>
                <div>
                    <label for="um-email" class="user-label">{{ __('Email') }}</label>
                    <input id="um-email" type="email" class="user-input" autocomplete="off">
                    <p data-err="email" class="user-err hidden"></p>
                </div>
                <div>
                    <label for="um-phone" class="user-label">{{ __('Phone') }} <span class="font-normal text-slate-400">({{ __('optional') }})</span></label>
                    <input id="um-phone" type="text" class="user-input">
                </div>
                <div>
                    <label for="um-role" class="user-label">{{ __('Role') }}</label>
                    <select id="um-role" class="user-input">
                        @foreach ($roles as $role)
                            @continue($role->is_superadmin && ! auth()->user()->isSuperAdmin())
                            <option value="{{ $role->name }}">{{ $role->label }}</option>
                        @endforeach
                    </select>
                    <p data-err="role" class="user-err hidden"></p>
                </div>
                <div>
                    <label for="um-2fa" class="user-label">{{ __('Two-factor method') }}</label>
                    <select id="um-2fa" class="user-input">
                        <option value="email">{{ __('Email code') }}</option>
                        <option value="authenticator">{{ __('Authenticator app') }}</option>
                    </select>
                    <p class="user-hint">{{ __('Email codes need no app enrolled first.') }}</p>
                </div>
                <div>
                    <label for="um-password" class="user-label">{{ __('Password') }}</label>
                    <input id="um-password" type="text" class="user-input font-mono" autocomplete="new-password">
                    <div class="mt-1 flex items-center justify-between">
                        <p id="um-password-hint" class="user-hint">{{ __('At least 12 characters.') }}</p>
                        <button type="button" id="um-generate" class="text-[0.66rem] font-semibold text-sky-600 hover:underline">{{ __('Generate') }}</button>
                    </div>
                    <p data-err="password" class="user-err hidden"></p>
                </div>
            </div>

            <div class="mt-4 flex items-center justify-end gap-2 border-t border-slate-100 pt-3 dark:border-zinc-800">
                <button id="um-cancel" type="button" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-600 transition hover:bg-slate-50 dark:border-zinc-700 dark:bg-transparent dark:text-zinc-300">{{ __('Cancel') }}</button>
                <button id="um-submit" type="button" class="rounded-lg bg-sky-600 px-3 py-2 text-xs font-medium text-white transition hover:bg-sky-700 disabled:opacity-60">{{ __('Add User') }}</button>
            </div>
        </div>
    </div>

    <style>
        .user-stat { background:#fff; border:1px solid #dbe4f0; border-radius:16px; padding:.85rem 1rem; }
        .user-stat-label { color:#64748b; font-size:.7rem; font-weight:600; }
        .user-stat-value { color:#0f172a; font-size:1.6rem; font-weight:700; font-variant-numeric:tabular-nums; line-height:1.1; margin-top:.35rem; }

        .user-identity { align-items:center; display:flex; gap:.6rem; }
        .user-avatar { align-items:center; background:#e0f2fe; border-radius:9999px; color:#0369a1; display:flex; flex-shrink:0; font-size:.68rem; font-weight:700; height:2rem; justify-content:center; width:2rem; }
        .user-identity-text { min-width:0; }
        .user-name { color:#0f172a; display:block; font-size:.8rem; font-weight:600; }
        .user-email { color:#94a3b8; display:block; font-size:.68rem; }
        .user-role { background:#f1f5f9; border-radius:9999px; color:#475569; font-size:.66rem; font-weight:600; padding:.16rem .5rem; }
        .user-chip { border-radius:9999px; display:inline-block; font-size:.58rem; font-weight:700; letter-spacing:.04em; margin-left:.25rem; padding:.12rem .42rem; text-transform:uppercase; }
        .user-chip.is-active { background:#dcfce7; color:#15803d; }
        .user-chip.is-disabled { background:#fee2e2; color:#b91c1c; }
        .user-chip.is-protected { background:#fef3c7; color:#b45309; }
        .user-sub { color:#94a3b8; display:block; font-size:.62rem; margin-top:.2rem; }
        .user-actions { display:flex; flex-wrap:wrap; gap:.3rem; }
        .user-btn { border:1px solid transparent; border-radius:7px; font-size:.66rem; font-weight:600; padding:.28rem .55rem; }
        .user-btn.is-primary { background:#0284c7; color:#fff; }
        .user-btn.is-warn { background:#fff; border-color:#fed7aa; color:#c2410c; }
        .user-btn.is-ok { background:#fff; border-color:#bbf7d0; color:#15803d; }
        .user-btn.is-danger { background:#fff; border-color:#fecaca; color:#dc2626; }
        .user-btn.is-muted { background:#f1f5f9; color:#94a3b8; cursor:not-allowed; }
        .user-label { color:#334155; display:block; font-size:.72rem; font-weight:600; margin-bottom:.3rem; }
        .user-input { background:#fff; border:1px solid #e2e8f0; border-radius:8px; font-size:.78rem; padding:.45rem .6rem; width:100%; }
        .user-input:focus { border-color:#0ea5e9; box-shadow:0 0 0 2px rgba(14,165,233,.16); outline:none; }
        .user-hint { color:#94a3b8; font-size:.66rem; }
        .user-err { color:#e11d48; font-size:.68rem; margin-top:.25rem; }

        #users-table_wrapper { color:#475569; }
        #users-table_wrapper .dataTables_length, #users-table_wrapper .dataTables_filter { padding:.8rem .95rem 0; }
        #users-table_wrapper .dataTables_info, #users-table_wrapper .dataTables_paginate { padding:.8rem .95rem .95rem; }
        #users-table thead th { background:#f8fafc; border-bottom:1px solid #e2e8f0; color:#64748b; font-size:.64rem; font-weight:700; letter-spacing:.06em; padding:.7rem .95rem; text-transform:uppercase; }
        #users-table tbody td { border-bottom:1px solid #f1f5f9; font-size:.78rem; padding:.65rem .95rem; vertical-align:middle; }

        .dark .user-stat { background:#18181b; border-color:#3f3f46; }
        .dark .user-stat-value, .dark .user-name { color:#fafafa; }
        .dark .user-label { color:#d4d4d8; }
        .dark .user-input { background:#09090b; border-color:#3f3f46; color:#fafafa; }
        .dark #users-table thead th { background:#09090b; border-color:#27272a; color:#a1a1aa; }
        .dark #users-table tbody td { border-color:#27272a; }
        .dark .user-role { background:#27272a; color:#d4d4d8; }
    </style>

    <script>
        (() => {
        const CSRF = '{{ csrf_token() }}';
        const STORE_URL = @js(route('admin.users.store'));
        const BASE = @js(url('admin/users'));
        const $id = (i) => document.getElementById(i);
        const modal = $id('user-modal');
        let table = null;
        let editingId = null;

        const initTable = () => {
            if (typeof $ === 'undefined' || !$.fn.DataTable) return;
            const el = $('#users-table');
            if (!el.length) return;
            if ($.fn.DataTable.isDataTable(el)) el.DataTable().destroy();

            table = el.DataTable({
                processing: true, serverSide: true, ajax: el.data('ajax-url'),
                ordering: false, pageLength: 20, lengthMenu: [[10, 20, 50], [10, 20, 50]],
                autoWidth: false, searchDelay: 300,
                dom: '<"flex flex-col gap-2 border-b border-slate-200 px-4 py-3 md:flex-row md:items-center md:justify-between dark:border-zinc-800"lf>rt<"flex flex-col gap-2 border-t border-slate-200 px-4 py-3 md:flex-row md:items-center md:justify-between dark:border-zinc-800"ip>',
                columns: [
                    { data: 'identity' }, { data: 'role' }, { data: 'status' },
                    { data: 'created' }, { data: 'actions' },
                ],
                language: {
                    emptyTable: 'No users yet', zeroRecords: 'No matching users',
                    search: '', searchPlaceholder: 'Search name, email or role...',
                    lengthMenu: 'Show _MENU_', info: 'Showing _START_ to _END_ of _TOTAL_',
                    infoEmpty: 'No accounts', paginate: { previous: 'Prev', next: 'Next' },
                },
            });
        };

        const clearErrors = () => document.querySelectorAll('[data-err]').forEach((e) => {
            e.textContent = ''; e.classList.add('hidden');
        });

        const showErrors = (errors) => {
            clearErrors();
            Object.entries(errors).forEach(([field, msgs]) => {
                const el = document.querySelector(`[data-err="${field}"]`);
                if (el) { el.textContent = msgs[0]; el.classList.remove('hidden'); }
            });
        };

        const generatePassword = () => {
            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
            return Array.from(crypto.getRandomValues(new Uint32Array(16)))
                .map((n) => chars[n % chars.length]).join('');
        };

        $id('um-generate').addEventListener('click', () => {
            $id('um-password').value = generatePassword();
            $id('um-password').focus();
        });

        const open = () => { modal.classList.remove('hidden'); modal.classList.add('flex'); modal.setAttribute('aria-hidden', 'false'); $id('um-name').focus(); };
        const close = () => { modal.classList.add('hidden'); modal.classList.remove('flex'); modal.setAttribute('aria-hidden', 'true'); clearErrors(); };

        const setMode = (mode, d = {}) => {
            clearErrors();
            if (mode === 'create') {
                editingId = null;
                $id('um-title').textContent = @js(__('Add User'));
                $id('um-desc').textContent = @js(__('They can sign in straight away — the address is treated as verified.'));
                $id('um-submit').textContent = @js(__('Add User'));
                $id('um-name').value = ''; $id('um-email').value = ''; $id('um-phone').value = '';
                $id('um-role').selectedIndex = 0;
                $id('um-2fa').value = 'email';
                $id('um-password').value = generatePassword();
                $id('um-password-hint').textContent = @js(__('At least 12 characters. Copy it before saving — it is not shown again.'));
            } else {
                editingId = Number(d.id);
                $id('um-title').textContent = @js(__('Edit User'));
                $id('um-desc').textContent = d.email;
                $id('um-submit').textContent = @js(__('Save Changes'));
                $id('um-name').value = d.name; $id('um-email').value = d.email; $id('um-phone').value = d.phone || '';
                $id('um-role').value = d.role;
                $id('um-2fa').value = d.twoFactor || 'email';
                $id('um-password').value = '';
                $id('um-password-hint').textContent = @js(__('Leave blank to keep the current password.'));
            }
        };

        $id('um-submit').addEventListener('click', async () => {
            const payload = {
                name: $id('um-name').value.trim(),
                email: $id('um-email').value.trim(),
                phone: $id('um-phone').value.trim() || null,
                role: $id('um-role').value,
                two_factor_type: $id('um-2fa').value,
                password: $id('um-password').value,
            };

            const btn = $id('um-submit');
            btn.disabled = true;
            const label = btn.textContent;
            btn.textContent = @js(__('Saving...'));

            try {
                const res = await fetch(editingId ? `${BASE}/${editingId}` : STORE_URL, {
                    method: editingId ? 'PUT' : 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify(payload),
                });
                const json = await res.json();

                if (res.status === 422) {
                    if (json.errors) showErrors(json.errors);
                    else iziToast.warning({ title: 'Not saved', message: json.message, position: 'topRight' });
                    return;
                }
                if (!res.ok) throw new Error(json.message ?? 'Server error');

                close();
                iziToast.success({ title: 'Saved', message: json.message, position: 'topRight' });
                table.ajax.reload(null, false);
            } catch (err) {
                iziToast.error({ title: 'Error', message: err.message, position: 'topRight' });
            } finally {
                btn.disabled = false;
                btn.textContent = label;
            }
        });

        const send = async (url, method, okTitle) => {
            try {
                const res = await fetch(url, { method, headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF } });
                const json = await res.json();
                if (!res.ok) throw new Error(json.message ?? 'Failed');
                iziToast.success({ title: okTitle, message: json.message, position: 'topRight' });
                table.ajax.reload(null, false);
            } catch (err) {
                iziToast.error({ title: 'Not allowed', message: err.message, position: 'topRight', timeout: 6000 });
            }
        };

        $(document).on('click', '.user-edit-btn', function () {
            setMode('edit', {
                id: $(this).data('id'), name: $(this).data('name'), email: $(this).data('email'),
                phone: $(this).data('phone'), role: $(this).data('role'), twoFactor: $(this).data('two-factor'),
            });
            open();
        });

        $(document).on('click', '.user-toggle-btn', function () {
            const disabling = $(this).data('action') === 'disable';
            const url = $(this).data('url');

            if (!disabling) { send(url, 'POST', 'Enabled'); return; }

            iziToast.question({
                timeout: 0, close: false, overlay: true, displayMode: 'once',
                title: 'Disable account',
                message: 'They will be signed out immediately and cannot sign back in. Their records stay intact.',
                position: 'center',
                buttons: [
                    ['<button>Yes, disable</button>', (i, t) => { i.hide({ transitionOut: 'fadeOut' }, t); send(url, 'POST', 'Disabled'); }, true],
                    ['<button>Cancel</button>', (i, t) => i.hide({ transitionOut: 'fadeOut' }, t)],
                ],
            });
        });

        $(document).on('click', '.user-delete-btn', function () {
            const url = $(this).data('url');
            const name = $(this).data('name');

            iziToast.question({
                timeout: 0, close: false, overlay: true, displayMode: 'once',
                title: 'Delete account',
                message: `Permanently remove ${name}? Disabling is usually better — it keeps their history linked.`,
                position: 'center',
                buttons: [
                    ['<button>Yes, delete</button>', (i, t) => { i.hide({ transitionOut: 'fadeOut' }, t); send(url, 'DELETE', 'Deleted'); }, true],
                    ['<button>Cancel</button>', (i, t) => i.hide({ transitionOut: 'fadeOut' }, t)],
                ],
            });
        });

        $id('btn-add-user').addEventListener('click', () => { setMode('create'); open(); });
        ['um-close', 'um-cancel'].forEach((i) => $id(i).addEventListener('click', close));
        $id('user-modal-overlay').addEventListener('click', close);
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.getAttribute('aria-hidden') === 'false') close();
        });

        document.addEventListener('DOMContentLoaded', initTable);
        document.addEventListener('livewire:navigated', initTable);
        initTable();
        })();
    </script>
</x-layouts::app>
