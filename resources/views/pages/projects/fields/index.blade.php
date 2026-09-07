<x-layouts::app :title="$project->title . ' — Registration Fields'">
    <div class="space-y-5">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-start gap-3">
                <a href="{{ route('projects.index') }}" class="mt-0.5 rounded-lg border border-slate-200 p-1.5 text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 dark:border-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    <span class="sr-only">{{ __('Back to projects') }}</span>
                </a>
                <div>
                    <h1 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">{{ __('Registration Fields') }}</h1>
                    <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Extra questions the registration form asks for') }} <span class="font-medium text-zinc-700 dark:text-zinc-200">{{ $project->title }}</span>.
                    </p>
                </div>
            </div>
            <button type="button" id="btn-add-field" class="inline-flex shrink-0 items-center justify-center gap-1.5 rounded-xl bg-sky-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-sky-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                {{ __('Add Field') }}
            </button>
        </div>

        <div class="grid gap-5 lg:grid-cols-[1.35fr_1fr]">

            {{-- The field list --}}
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="fb-section-title">{{ __('Fields') }}</h2>
                    <span class="text-xs text-slate-400">{{ __('Drag to reorder') }}</span>
                </div>

                <ul id="field-list" class="space-y-2">
                    @forelse ($definitions as $definition)
                        <li class="fb-row" draggable="true"
                            data-id="{{ $definition->id }}"
                            data-label="{{ e($definition->label) }}"
                            data-key="{{ e($definition->key) }}"
                            data-type="{{ $definition->type }}"
                            data-options="{{ e(json_encode($definition->options ?? [])) }}"
                            data-required="{{ $definition->is_required ? '1' : '0' }}"
                            data-help="{{ e((string) $definition->help_text) }}"
                            data-placeholder="{{ e((string) $definition->placeholder) }}"
                            data-status="{{ $definition->status }}"
                            data-answers="{{ $definition->values_count }}"
                        >
                            <span class="fb-grip" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor"><path d="M7 4a1 1 0 100 2 1 1 0 000-2zM7 9a1 1 0 100 2 1 1 0 000-2zM7 14a1 1 0 100 2 1 1 0 000-2zM13 4a1 1 0 100 2 1 1 0 000-2zM13 9a1 1 0 100 2 1 1 0 000-2zM13 14a1 1 0 100 2 1 1 0 000-2z"/></svg>
                            </span>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="fb-label">{{ $definition->label }}</span>
                                    @if ($definition->is_required)
                                        <span class="fb-chip is-required">{{ __('required') }}</span>
                                    @endif
                                    @unless ($definition->isActive())
                                        <span class="fb-chip is-inactive">{{ __('inactive') }}</span>
                                    @endunless
                                </div>
                                <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-500">
                                    <span class="fb-key">{{ $definition->key }}</span>
                                    <span>·</span>
                                    <span>{{ $definition->typeLabel() }}</span>
                                    @if ($definition->values_count > 0)
                                        <span>·</span>
                                        <span class="text-slate-400">{{ trans_choice('{1} :count answer|[2,*] :count answers', $definition->values_count, ['count' => number_format($definition->values_count)]) }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="fb-actions">
                                <button type="button" class="fb-btn is-primary field-edit-btn">{{ __('Edit') }}</button>
                                @if ($definition->values_count > 0)
                                    <button type="button" class="fb-btn is-muted" disabled title="{{ __('Has stored answers — set it inactive instead') }}">{{ __('In use') }}</button>
                                @else
                                    <button type="button" class="fb-btn is-danger field-delete-btn" data-destroy-url="{{ route('projects.fields.destroy', [$project, $definition]) }}">{{ __('Delete') }}</button>
                                @endif
                            </div>
                        </li>
                    @empty
                        <li id="fb-empty" class="fb-empty">
                            <p class="font-medium text-slate-700 dark:text-zinc-200">{{ __('No extra fields yet') }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ __('This project uses only the core registration fields. Add a field to ask for more.') }}</p>
                        </li>
                    @endforelse
                </ul>

                @if ($shared->isNotEmpty())
                    <div class="pt-2">
                        <h2 class="fb-section-title">{{ __('Shared across every project') }}</h2>
                        <ul class="mt-2 space-y-2">
                            @foreach ($shared as $definition)
                                <li class="fb-row is-shared">
                                    <div class="min-w-0 flex-1">
                                        <span class="fb-label">{{ $definition->label }}</span>
                                        <div class="mt-1 text-xs text-slate-500">
                                            <span class="fb-key">{{ $definition->key }}</span> · {{ $definition->typeLabel() }}
                                        </div>
                                    </div>
                                    <span class="fb-chip is-shared">{{ __('all projects') }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            {{-- Live preview --}}
            <div>
                <h2 class="fb-section-title">{{ __('Form preview') }}</h2>
                <div class="fb-preview mt-2">
                    <p class="fb-preview-note">{{ __('Core fields') }}</p>
                    <div class="fb-preview-core">
                        <span>{{ __('First name') }}</span><span>{{ __('Last name') }}</span>
                        <span>{{ __('NID') }}</span><span>{{ __('Email') }}</span>
                        <span>{{ __('Phone') }}</span><span>{{ __('Date of birth') }}</span>
                        <span>{{ __('Photo') }}</span><span>{{ __('Father / Mother') }}</span>
                    </div>
                    <p class="fb-preview-note mt-4">{{ __('This project asks for') }}</p>
                    <div id="fb-preview-custom" class="mt-2 space-y-3"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Add / edit modal --}}
    <div id="field-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" aria-hidden="true">
        <div id="field-modal-overlay" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>
        <div class="relative w-full max-w-md rounded-2xl border border-slate-200 bg-white p-5 shadow-xl dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 id="fm-title" class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Add Field') }}</h2>
                    <p id="fm-desc" class="mt-0.5 text-xs text-slate-500 dark:text-zinc-400">{{ __('This question is added to the registration form for this project.') }}</p>
                </div>
                <button id="fm-close" type="button" class="rounded-full bg-slate-100 p-1.5 text-slate-500 transition hover:bg-slate-200 dark:bg-zinc-800 dark:text-zinc-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    <span class="sr-only">{{ __('Close') }}</span>
                </button>
            </div>

            <div class="mt-4 max-h-[65vh] space-y-3 overflow-y-auto pr-1">
                <div>
                    <label for="fm-label" class="fb-form-label">{{ __('Question') }}</label>
                    <input id="fm-label" type="text" class="fb-input" placeholder="{{ __('e.g. Household monthly income') }}">
                    <p id="fm-label-error" class="fb-error hidden"></p>
                </div>

                <div>
                    <label for="fm-type" class="fb-form-label">{{ __('Answer type') }}</label>
                    <select id="fm-type" class="fb-input">
                        @foreach ($types as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="fm-options-wrap" class="hidden">
                    <label class="fb-form-label">{{ __('Choices') }}</label>
                    <div id="fm-options" class="space-y-2"></div>
                    <button type="button" id="fm-add-option" class="mt-2 text-xs font-medium text-sky-600 hover:text-sky-700">+ {{ __('Add choice') }}</button>
                    <p id="fm-options-error" class="fb-error hidden"></p>
                </div>

                <div>
                    <label for="fm-key" class="fb-form-label">{{ __('Key') }} <span class="font-normal text-slate-400">({{ __('used in imports and exports') }})</span></label>
                    <input id="fm-key" type="text" class="fb-input font-mono" placeholder="{{ __('auto-generated from the question') }}">
                    <p id="fm-key-note" class="mt-1 hidden text-xs text-amber-600"></p>
                    <p id="fm-key-error" class="fb-error hidden"></p>
                </div>

                <div>
                    <label for="fm-help" class="fb-form-label">{{ __('Help text') }} <span class="font-normal text-slate-400">({{ __('optional') }})</span></label>
                    <input id="fm-help" type="text" class="fb-input" placeholder="{{ __('Shown under the input') }}">
                </div>

                <div>
                    <label for="fm-placeholder" class="fb-form-label">{{ __('Placeholder') }} <span class="font-normal text-slate-400">({{ __('optional') }})</span></label>
                    <input id="fm-placeholder" type="text" class="fb-input">
                </div>

                <div class="flex items-center justify-between gap-3 pt-1">
                    <label for="fm-required" class="fb-form-label mb-0">{{ __('Required') }}</label>
                    <input id="fm-required" type="checkbox" class="size-4 rounded border-slate-300 text-sky-600">
                </div>

                <div class="flex items-center justify-between gap-3">
                    <label for="fm-status" class="fb-form-label mb-0">{{ __('Active') }}</label>
                    <input id="fm-status" type="checkbox" class="size-4 rounded border-slate-300 text-sky-600" checked>
                </div>
            </div>

            <div class="mt-4 flex items-center justify-end gap-2 border-t border-slate-100 pt-3 dark:border-zinc-800">
                <button id="fm-cancel" type="button" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-600 transition hover:bg-slate-50 dark:border-zinc-700 dark:bg-transparent dark:text-zinc-300">{{ __('Cancel') }}</button>
                <button id="fm-submit" type="button" class="rounded-lg bg-sky-600 px-3 py-2 text-xs font-medium text-white transition hover:bg-sky-700 disabled:opacity-60">{{ __('Save Field') }}</button>
            </div>
        </div>
    </div>

    <style>
        .fb-section-title { color:#64748b; font-size:.7rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; }
        .fb-row { align-items:center; background:#fff; border:1px solid #dbe4f0; border-radius:14px; display:flex; gap:.7rem; padding:.7rem .85rem; transition:border-color .15s ease, box-shadow .15s ease; }
        .fb-row:hover { border-color:#bfd3ea; box-shadow:0 4px 14px rgba(148,163,184,.1); }
        .fb-row.is-dragging { opacity:.45; }
        .fb-row.is-over { border-color:#0284c7; box-shadow:0 0 0 2px rgba(2,132,199,.14); }
        .fb-row.is-shared { background:#f8fafc; }
        .fb-grip { color:#cbd5e1; cursor:grab; flex-shrink:0; }
        .fb-grip:active { cursor:grabbing; }
        .fb-label { color:#0f172a; font-size:.82rem; font-weight:600; }
        .fb-key { background:#f1f5f9; border-radius:4px; color:#475569; font-family:ui-monospace,SFMono-Regular,Consolas,monospace; font-size:.66rem; padding:.1rem .3rem; }
        .fb-chip { border-radius:9999px; font-size:.6rem; font-weight:700; letter-spacing:.04em; padding:.14rem .45rem; text-transform:uppercase; }
        .fb-chip.is-required { background:#fee2e2; color:#b91c1c; }
        .fb-chip.is-inactive { background:#e2e8f0; color:#64748b; }
        .fb-chip.is-shared { background:#e0f2fe; color:#0369a1; }
        .fb-actions { display:flex; flex-shrink:0; gap:.35rem; }
        .fb-btn { border:1px solid transparent; border-radius:8px; font-size:.66rem; font-weight:600; padding:.3rem .55rem; }
        .fb-btn.is-primary { background:#059669; color:#fff; }
        .fb-btn.is-danger { background:#ef4444; color:#fff; }
        .fb-btn.is-muted { background:#f1f5f9; border-color:#dbe4f0; color:#94a3b8; cursor:not-allowed; }
        .fb-empty { background:#fff; border:1px dashed #cbd5e1; border-radius:14px; padding:1.4rem; text-align:center; }
        .fb-preview { background:#fff; border:1px solid #dbe4f0; border-radius:16px; padding:1rem; }
        .fb-preview-note { color:#94a3b8; font-size:.64rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; }
        .fb-preview-core { color:#94a3b8; display:grid; font-size:.7rem; gap:.3rem .8rem; grid-template-columns:1fr 1fr; margin-top:.5rem; }
        .fb-preview-field label { color:#334155; display:block; font-size:.72rem; font-weight:600; margin-bottom:.25rem; }
        .fb-preview-field .req { color:#dc2626; }
        .fb-preview-field .box { background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; color:#94a3b8; font-size:.7rem; padding:.4rem .55rem; }
        .fb-preview-field .hint { color:#94a3b8; font-size:.64rem; margin-top:.2rem; }
        .fb-preview-empty { color:#cbd5e1; font-size:.72rem; font-style:italic; }
        .fb-form-label { color:#334155; display:block; font-size:.72rem; font-weight:600; margin-bottom:.3rem; }
        .fb-input { background:#fff; border:1px solid #e2e8f0; border-radius:8px; font-size:.75rem; padding:.45rem .6rem; width:100%; }
        .fb-input:focus { border-color:#0ea5e9; box-shadow:0 0 0 2px rgba(14,165,233,.16); outline:none; }
        .fb-error { color:#e11d48; font-size:.68rem; margin-top:.3rem; }
        .fb-opt-row { display:flex; gap:.4rem; }
        .fb-opt-row button { color:#94a3b8; flex-shrink:0; font-size:.9rem; padding:0 .3rem; }
        .fb-opt-row button:hover { color:#ef4444; }
        .dark .fb-row, .dark .fb-preview, .dark .fb-empty { background:#18181b; border-color:#3f3f46; }
        .dark .fb-row.is-shared { background:#1c1c1f; }
        .dark .fb-label, .dark .fb-preview-field label { color:#f4f4f5; }
        .dark .fb-key { background:#27272a; color:#a1a1aa; }
        .dark .fb-input { background:#09090b; border-color:#3f3f46; color:#fafafa; }
        .dark .fb-preview-field .box { background:#09090b; border-color:#3f3f46; }
        .dark .fb-form-label { color:#d4d4d8; }
    </style>

    <script>
        (() => {
        const CSRF = '{{ csrf_token() }}';
        const STORE_URL = @js(route('projects.fields.store', $project));
        const REORDER_URL = @js(route('projects.fields.reorder', $project));
        const UPDATE_BASE = @js(url('projects/'.$project->id.'/fields'));
        const CHOICE_TYPES = @js(\App\Models\FieldDefinition::CHOICE_TYPES);

        let editingId = null;

        const $id = (id) => document.getElementById(id);
        const modal = $id('field-modal');
        const list = $id('field-list');

        // ---------- preview ----------

        const renderPreview = () => {
            const target = $id('fb-preview-custom');
            const rows = [...list.querySelectorAll('.fb-row[data-id]')]
                .filter((r) => r.dataset.status === 'active');

            if (!rows.length) {
                target.innerHTML = '<p class="fb-preview-empty">' + @js(__('Nothing extra — the core fields only.')) + '</p>';
                return;
            }

            target.innerHTML = rows.map((r) => {
                const type = r.dataset.type;
                const required = r.dataset.required === '1';
                const options = JSON.parse(r.dataset.options || '[]');
                const placeholder = r.dataset.placeholder || '';

                let control;
                if (type === 'textarea') {
                    control = '<div class="box" style="min-height:2.6rem">' + (placeholder || '&nbsp;') + '</div>';
                } else if (CHOICE_TYPES.includes(type)) {
                    control = '<div class="box">' + (options[0] ?? '—') + ' ▾</div>';
                } else if (type === 'checkbox') {
                    control = '<div class="box">☐ ' + @js(__('Yes')) + '</div>';
                } else {
                    control = '<div class="box">' + (placeholder || '&nbsp;') + '</div>';
                }

                return '<div class="fb-preview-field">'
                    + '<label>' + escapeHtml(r.dataset.label) + (required ? ' <span class="req">*</span>' : '') + '</label>'
                    + control
                    + (r.dataset.help ? '<p class="hint">' + escapeHtml(r.dataset.help) + '</p>' : '')
                    + '</div>';
            }).join('');
        };

        const escapeHtml = (s) => String(s).replace(/[&<>"']/g, (c) => (
            { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]
        ));

        // ---------- drag to reorder ----------

        let dragged = null;

        list.addEventListener('dragstart', (e) => {
            const row = e.target.closest('.fb-row[data-id]');
            if (!row) return;
            dragged = row;
            row.classList.add('is-dragging');
            e.dataTransfer.effectAllowed = 'move';
        });

        list.addEventListener('dragend', () => {
            if (dragged) dragged.classList.remove('is-dragging');
            list.querySelectorAll('.is-over').forEach((r) => r.classList.remove('is-over'));
            dragged = null;
        });

        list.addEventListener('dragover', (e) => {
            e.preventDefault();
            const row = e.target.closest('.fb-row[data-id]');
            if (!row || row === dragged || !dragged) return;
            list.querySelectorAll('.is-over').forEach((r) => r.classList.remove('is-over'));
            row.classList.add('is-over');
        });

        list.addEventListener('drop', (e) => {
            e.preventDefault();
            const row = e.target.closest('.fb-row[data-id]');
            if (!row || !dragged || row === dragged) return;

            const rows = [...list.querySelectorAll('.fb-row[data-id]')];
            const from = rows.indexOf(dragged);
            const to = rows.indexOf(row);
            row.parentNode.insertBefore(dragged, from < to ? row.nextSibling : row);

            row.classList.remove('is-over');
            saveOrder();
            renderPreview();
        });

        const saveOrder = async () => {
            const order = [...list.querySelectorAll('.fb-row[data-id]')].map((r) => Number(r.dataset.id));
            try {
                const res = await fetch(REORDER_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify({ order }),
                });
                if (!res.ok) throw new Error('Could not save the new order');
                iziToast.success({ title: 'Saved', message: 'Field order updated.', position: 'topRight', timeout: 1500 });
            } catch (err) {
                iziToast.error({ title: 'Error', message: err.message, position: 'topRight' });
            }
        };

        // ---------- modal ----------

        const optionsWrap = $id('fm-options');

        const addOptionRow = (value = '') => {
            const row = document.createElement('div');
            row.className = 'fb-opt-row';
            row.innerHTML = '<input type="text" class="fb-input fm-option" value="' + escapeHtml(value) + '">'
                + '<button type="button" title="Remove">&times;</button>';
            row.querySelector('button').addEventListener('click', () => row.remove());
            optionsWrap.appendChild(row);
        };

        const syncTypeUi = () => {
            const needsOptions = CHOICE_TYPES.includes($id('fm-type').value);
            $id('fm-options-wrap').classList.toggle('hidden', !needsOptions);
            if (needsOptions && !optionsWrap.children.length) addOptionRow();
        };

        $id('fm-type').addEventListener('change', syncTypeUi);
        $id('fm-add-option').addEventListener('click', () => addOptionRow());

        const clearErrors = () => {
            ['fm-label-error', 'fm-key-error', 'fm-options-error'].forEach((id) => {
                const el = $id(id);
                el.textContent = '';
                el.classList.add('hidden');
            });
        };

        const showError = (id, msg) => {
            const el = $id(id);
            el.textContent = msg;
            el.classList.remove('hidden');
        };

        const openModal = () => {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modal.setAttribute('aria-hidden', 'false');
            $id('fm-label').focus();
        };

        const closeModal = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            modal.setAttribute('aria-hidden', 'true');
            clearErrors();
        };

        const setMode = (mode, row = null) => {
            clearErrors();
            optionsWrap.innerHTML = '';
            $id('fm-key-note').classList.add('hidden');
            $id('fm-key').disabled = false;

            if (mode === 'create') {
                editingId = null;
                $id('fm-title').textContent = @js(__('Add Field'));
                $id('fm-submit').textContent = @js(__('Save Field'));
                $id('fm-label').value = '';
                $id('fm-key').value = '';
                $id('fm-type').value = 'text';
                $id('fm-help').value = '';
                $id('fm-placeholder').value = '';
                $id('fm-required').checked = false;
                $id('fm-status').checked = true;
            } else {
                editingId = Number(row.dataset.id);
                $id('fm-title').textContent = @js(__('Edit Field'));
                $id('fm-submit').textContent = @js(__('Update Field'));
                $id('fm-label').value = row.dataset.label;
                $id('fm-key').value = row.dataset.key;
                $id('fm-type').value = row.dataset.type;
                $id('fm-help').value = row.dataset.help;
                $id('fm-placeholder').value = row.dataset.placeholder;
                $id('fm-required').checked = row.dataset.required === '1';
                $id('fm-status').checked = row.dataset.status === 'active';

                JSON.parse(row.dataset.options || '[]').forEach(addOptionRow);

                // Once answers exist the key is the only link back to them.
                if (Number(row.dataset.answers) > 0) {
                    $id('fm-key').disabled = true;
                    const note = $id('fm-key-note');
                    note.textContent = @js(__('Locked — this field already has stored answers.'));
                    note.classList.remove('hidden');
                }
            }

            syncTypeUi();
        };

        $id('fm-submit').addEventListener('click', async () => {
            clearErrors();

            const label = $id('fm-label').value.trim();
            if (!label) { showError('fm-label-error', @js(__('A question is required.'))); return; }

            const type = $id('fm-type').value;
            const options = [...document.querySelectorAll('.fm-option')].map((i) => i.value.trim()).filter(Boolean);

            if (CHOICE_TYPES.includes(type) && !options.length) {
                showError('fm-options-error', @js(__('Add at least one choice.')));
                return;
            }

            const payload = {
                label,
                key: $id('fm-key').value.trim() || null,
                type,
                options,
                is_required: $id('fm-required').checked,
                help_text: $id('fm-help').value.trim() || null,
                placeholder: $id('fm-placeholder').value.trim() || null,
                status: $id('fm-status').checked ? 'active' : 'inactive',
            };

            const btn = $id('fm-submit');
            btn.disabled = true;
            const original = btn.textContent;
            btn.textContent = @js(__('Saving...'));

            try {
                const res = await fetch(editingId ? `${UPDATE_BASE}/${editingId}` : STORE_URL, {
                    method: editingId ? 'PUT' : 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify(payload),
                });
                const json = await res.json();

                if (res.status === 422) {
                    const errs = json.errors ?? {};
                    if (errs.label) showError('fm-label-error', errs.label[0]);
                    if (errs.key) showError('fm-key-error', errs.key[0]);
                    if (errs.options) showError('fm-options-error', errs.options[0]);
                    if (!errs.label && !errs.key && !errs.options) {
                        iziToast.warning({ title: 'Not saved', message: json.message, position: 'topRight' });
                    }
                    return;
                }

                if (!res.ok) throw new Error(json.message ?? 'Server error');

                closeModal();
                iziToast.success({ title: editingId ? 'Updated' : 'Added', message: json.message, position: 'topRight' });
                window.location.reload();
            } catch (err) {
                iziToast.error({ title: 'Error', message: err.message, position: 'topRight' });
            } finally {
                btn.disabled = false;
                btn.textContent = original;
            }
        });

        $id('btn-add-field').addEventListener('click', () => { setMode('create'); openModal(); });
        ['fm-close', 'fm-cancel'].forEach((id) => $id(id).addEventListener('click', closeModal));
        $id('field-modal-overlay').addEventListener('click', closeModal);
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.getAttribute('aria-hidden') === 'false') closeModal();
        });

        list.addEventListener('click', async (e) => {
            const editBtn = e.target.closest('.field-edit-btn');
            if (editBtn) { setMode('edit', editBtn.closest('.fb-row')); openModal(); return; }

            const delBtn = e.target.closest('.field-delete-btn');
            if (!delBtn) return;

            const url = delBtn.dataset.destroyUrl;
            iziToast.question({
                timeout: 0, close: false, overlay: true, displayMode: 'once',
                title: 'Confirm Delete',
                message: 'Delete this field? Any answers would be lost.',
                position: 'center',
                buttons: [
                    ['<button>Yes, delete</button>', async (instance, toast) => {
                        instance.hide({ transitionOut: 'fadeOut' }, toast);
                        try {
                            const res = await fetch(url, { method: 'DELETE', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF } });
                            const json = await res.json();
                            if (!res.ok) throw new Error(json.message ?? 'Delete failed');
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

        renderPreview();
        })();
    </script>
</x-layouts::app>
