<x-layouts::app :title="__('Project Categories')">
    <div class="space-y-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">{{ __('Project Categories') }}</h1>
                <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Manage reusable project categories like training, software, and services.') }}</p>
            </div>
            <button type="button" id="btn-add-category" class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-sky-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-sky-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                {{ __('Add Category') }}
            </button>
        </div>

        <div class="grid gap-3 md:grid-cols-3">
            <div class="page-stat-card">
                <p class="page-stat-label">{{ __('Total Categories') }}</p>
                <p class="page-stat-value">{{ number_format($totalCategories) }}</p>
                <span class="page-stat-bar is-total"></span>
            </div>
            <div class="page-stat-card">
                <p class="page-stat-label">{{ __('Active Categories') }}</p>
                <p class="page-stat-value">{{ number_format($activeCategories) }}</p>
                <span class="page-stat-bar is-active"></span>
            </div>
            <div class="page-stat-card">
                <p class="page-stat-label">{{ __('Inactive Categories') }}</p>
                <p class="page-stat-value">{{ number_format($inactiveCategories) }}</p>
                <span class="page-stat-bar is-inactive"></span>
            </div>
        </div>

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table id="project-categories-table" data-ajax-url="{{ route('projects.categories.data') }}" class="min-w-full text-left" style="width:100%">
                    <thead>
                        <tr>
                            <th>{{ __('Category') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Projects') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <style>
        .page-stat-card { background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%); border: 1px solid #dbe4f0; border-radius: 18px; box-shadow: 0 10px 24px rgba(148, 163, 184, 0.08); padding: 0.95rem 1rem 0.85rem; }
        .page-stat-label { color: #64748b; font-size: 0.72rem; font-weight: 600; }
        .page-stat-value { color: #0f172a; font-size: 1.7rem; font-weight: 700; line-height: 1; margin-top: 0.8rem; }
        .page-stat-bar { border-radius: 9999px; display: inline-block; height: 2px; margin-top: 0.9rem; width: 1.7rem; }
        .page-stat-bar.is-total { background: #64748b; }
        .page-stat-bar.is-active { background: #10b981; }
        .page-stat-bar.is-inactive { background: #ef4444; }
        #project-categories-table_wrapper { color: #475569; }
        #project-categories-table_wrapper .dataTables_length, #project-categories-table_wrapper .dataTables_filter { padding: 0.8rem 0.95rem 0; }
        #project-categories-table_wrapper .dataTables_info, #project-categories-table_wrapper .dataTables_paginate { padding: 0.8rem 0.95rem 0.95rem; }
        #project-categories-table_wrapper .dataTables_length label, #project-categories-table_wrapper .dataTables_filter label, #project-categories-table_wrapper .dataTables_info { align-items: center; color: #64748b; display: inline-flex; font-size: 0.68rem; font-weight: 500; gap: 0.35rem; }
        #project-categories-table_wrapper .dataTables_length select, #project-categories-table_wrapper .dataTables_filter input { background: #fff; border: 1px solid #dbe4f0; border-radius: 9999px; box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04); color: #0f172a; font-size: 0.7rem; min-height: 1.95rem; outline: none; padding: 0.3rem 0.75rem; }
        #project-categories-table_wrapper .dataTables_filter { display: flex; justify-content: flex-end; }
        #project-categories-table_wrapper .dataTables_filter input { margin-left: 0.4rem; width: 12rem; }
        #project-categories-table { border-collapse: separate; border-spacing: 0; color: #0f172a; }
        #project-categories-table thead th { background: #fbfdff; border-bottom: 1px solid #e8eef6; color: #8ba0c0; font-size: 0.64rem; font-weight: 700; letter-spacing: 0.18em; padding: 0.68rem 0.9rem; text-transform: uppercase; }
        #project-categories-table tbody td { background: #fff; border-bottom: 1px solid #edf2f7; font-size: 0.76rem; padding: 0.78rem 0.9rem; vertical-align: middle; }
        #project-categories-table tbody tr:hover td { background: #f8fbff; }
        .category-name-text { color: #0f172a; font-size: 0.78rem; font-weight: 600; }
        .category-status-badge { align-items: center; border: 1px solid; border-radius: 9999px; display: inline-flex; font-size: 0.64rem; font-weight: 600; gap: 0.28rem; line-height: 1; padding: 0.25rem 0.48rem; text-transform: lowercase; }
        .category-status-badge.is-active { background: #ecfdf3; border-color: #a7f3d0; color: #047857; }
        .category-status-badge.is-inactive { background: #fff7ed; border-color: #fdba74; color: #c2410c; }
        .category-status-dot { background: currentColor; border-radius: 9999px; height: 0.34rem; width: 0.34rem; }
        .category-actions { display: inline-flex; gap: 0.32rem; white-space: nowrap; }
        .category-action-btn { border: 1px solid transparent; border-radius: 8px; font-size: 0.67rem; font-weight: 600; padding: 0.34rem 0.62rem; }
        .category-action-btn.is-primary { background: #059669; color: #fff; }
        .category-action-btn.is-danger { background: #ef4444; color: #fff; }
        #project-categories-table_wrapper .dataTables_paginate .paginate_button { background: #fff !important; border: 1px solid #dbe4f0 !important; border-radius: 9999px; color: #475569 !important; font-size: 0.68rem !important; margin-left: 0.25rem; min-width: 1.85rem; padding: 0.24rem 0.56rem !important; }
        #project-categories-table_wrapper .dataTables_paginate .paginate_button.current { background: #0f172a !important; border-color: #0f172a !important; color: #fff !important; }
        #project-categories-table_wrapper .dataTables_processing { background: #fff; border: 1px solid #dbe4f0; border-radius: 9999px; box-shadow: 0 12px 30px rgba(148, 163, 184, 0.18); color: #0f172a; font-size: 0.68rem; padding: 0.5rem 1rem; }
        .dark .page-stat-card, .dark #project-categories-table_wrapper .dataTables_length select, .dark #project-categories-table_wrapper .dataTables_filter input, .dark #project-categories-table tbody td, .dark #project-categories-table_wrapper .dataTables_paginate .paginate_button, .dark #project-categories-table_wrapper .dataTables_processing { background: #09090b !important; border-color: #27272a !important; color: #f4f4f5 !important; }
        .dark .page-stat-label, .dark #project-categories-table_wrapper .dataTables_length label, .dark #project-categories-table_wrapper .dataTables_filter label, .dark #project-categories-table_wrapper .dataTables_info, .dark #project-categories-table thead th { color: #94a3b8; }
        .dark .page-stat-value, .dark .category-name-text, .dark #project-categories-table { color: #fafafa; }
        .dark #project-categories-table thead th { background: #111827; border-bottom-color: #27272a; }
        .dark #project-categories-table tbody td { border-bottom-color: #27272a; }
    </style>

    <div id="category-modal" class="fixed inset-0 z-[110] hidden items-center justify-center p-4" aria-hidden="true">
        <div id="category-modal-overlay" class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
        <div class="relative w-full max-w-md rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="modal-title" class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Add Project Category') }}</h2>
                    <p id="modal-desc" class="mt-0.5 text-xs text-slate-500 dark:text-zinc-400">{{ __('Create a project category and choose its current status.') }}</p>
                </div>
                <button id="modal-close" type="button" class="rounded-full bg-slate-100 p-1.5 text-slate-500 transition hover:bg-slate-200 hover:text-slate-900 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700 dark:hover:text-white">×</button>
            </div>
            <div class="mt-5 space-y-4">
                <div>
                    <label for="cat-name" class="text-xs font-medium text-slate-700 dark:text-zinc-300">{{ __('Category Name') }}</label>
                    <input id="cat-name" type="text" class="mt-1.5 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-900 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white" placeholder="{{ __('e.g. Training') }}">
                    <p id="cat-name-error" class="mt-1.5 hidden text-xs text-rose-400"></p>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <label for="cat-status" class="text-xs font-medium text-slate-700 dark:text-zinc-300">{{ __('Status') }}</label>
                    <label for="cat-status" class="status-switch">
                        <input id="cat-status" type="checkbox" class="status-switch__input" checked>
                        <span class="status-switch__track"><span class="status-switch__thumb"></span></span>
                        <span id="cat-status-label" class="status-switch__text">{{ __('Active') }}</span>
                    </label>
                </div>
                <div class="flex items-center justify-end gap-2 pt-1">
                    <button id="modal-cancel" type="button" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-600 transition hover:bg-slate-50 hover:text-slate-900 dark:border-zinc-700 dark:bg-transparent dark:text-zinc-300">{{ __('Cancel') }}</button>
                    <button id="modal-submit" type="button" class="rounded-lg bg-sky-600 px-3 py-2 text-xs font-medium text-white transition hover:bg-sky-700 disabled:opacity-60">{{ __('Save Category') }}</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (() => {
        const CSRF = '{{ csrf_token() }}';
        const STORE_URL = @js(route('projects.categories.store'));
        let categoryTable = null;
        let editingId = null;
        const modal = document.getElementById('category-modal');
        const modalTitle = document.getElementById('modal-title');
        const modalDesc = document.getElementById('modal-desc');
        const catName = document.getElementById('cat-name');
        const catStatus = document.getElementById('cat-status');
        const catStatusLabel = document.getElementById('cat-status-label');
        const catNameErr = document.getElementById('cat-name-error');
        const submitBtn = document.getElementById('modal-submit');
        const syncStatusSwitch = (isActive) => { catStatus.checked = isActive; catStatusLabel.textContent = isActive ? 'Active' : 'Inactive'; };

        const initTable = () => {
            if (typeof $ === 'undefined' || !$.fn.DataTable) return;
            const el = $('#project-categories-table');
            if (!el.length) return;
            if ($.fn.DataTable.isDataTable(el)) el.DataTable().destroy();
            categoryTable = el.DataTable({
                processing: true, serverSide: true, ajax: el.data('ajax-url'), order: [[0, 'asc']], pageLength: 20, lengthMenu: [[10, 20, 50], [10, 20, 50]], autoWidth: false, searchDelay: 300,
                dom: '<"flex flex-col gap-2 border-b border-slate-200 px-4 py-3 md:flex-row md:items-center md:justify-between dark:border-zinc-800"lf>rt<"flex flex-col gap-2 border-t border-slate-200 px-4 py-3 md:flex-row md:items-center md:justify-between dark:border-zinc-800"ip>',
                columns: [{ data: 'name' }, { data: 'status', orderable: false, searchable: false }, { data: 'projects_count', orderable: false, searchable: false }, { data: 'actions', orderable: false, searchable: false }],
                language: { emptyTable: 'No project categories found', zeroRecords: 'No matching categories found', search: '', searchPlaceholder: 'Search...', lengthMenu: 'Show _MENU_', info: 'Showing _START_ to _END_ of _TOTAL_ entries', infoEmpty: 'No entries available', paginate: { previous: 'Prev', next: 'Next' } },
            });
        };
        const openModal = () => { modal.classList.remove('hidden'); modal.classList.add('flex'); modal.setAttribute('aria-hidden', 'false'); catName.focus(); };
        const closeModal = () => { modal.classList.add('hidden'); modal.classList.remove('flex'); modal.setAttribute('aria-hidden', 'true'); clearErrors(); };
        const clearErrors = () => { catNameErr.textContent = ''; catNameErr.classList.add('hidden'); catName.classList.remove('border-rose-500'); };
        const showNameError = (msg) => { catNameErr.textContent = msg; catNameErr.classList.remove('hidden'); catName.classList.add('border-rose-500'); };
        const setMode = (mode, data = {}) => {
            clearErrors();
            if (mode === 'create') {
                editingId = null; modalTitle.textContent = 'Add Project Category'; modalDesc.textContent = 'Create a project category and choose its current status.'; submitBtn.textContent = 'Save Category'; catName.value = ''; syncStatusSwitch(true);
            } else {
                editingId = data.id; modalTitle.textContent = 'Edit Project Category'; modalDesc.textContent = 'Update the category name or change its status.'; submitBtn.textContent = 'Update Category'; catName.value = data.name; syncStatusSwitch(data.status === 'active');
            }
        };
        catStatus.addEventListener('change', () => syncStatusSwitch(catStatus.checked));
        submitBtn.addEventListener('click', async () => {
            clearErrors();
            const name = catName.value.trim();
            const status = catStatus.checked ? 'active' : 'inactive';
            if (!name) return showNameError('Category name is required.');
            const isEdit = editingId !== null;
            const url = isEdit ? `/projects/categories/${editingId}` : STORE_URL;
            const method = isEdit ? 'PUT' : 'POST';
            submitBtn.disabled = true;
            const original = submitBtn.textContent;
            submitBtn.textContent = 'Saving...';
            try {
                const res = await fetch(url, { method, headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }, body: JSON.stringify({ name, status }) });
                const json = await res.json();
                if (res.status === 422) { if (json.errors?.name) showNameError(json.errors.name[0]); return; }
                if (!res.ok) throw new Error(json.message ?? 'Server error');
                closeModal();
                iziToast.success({ title: isEdit ? 'Updated' : 'Created', message: json.message, position: 'topRight' });
                categoryTable.ajax.reload(null, false);
            } catch (e) {
                iziToast.error({ title: 'Error', message: e.message || 'Something went wrong.', position: 'topRight' });
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = original;
            }
        });
        $(document).on('click', '.category-edit-btn', function () { setMode('edit', { id: $(this).data('category-id'), name: $(this).data('category-name'), status: $(this).data('category-status') }); openModal(); });
        $(document).on('click', '.category-delete-btn', function () {
            const url = $(this).data('destroy-url');
            iziToast.question({ timeout: 0, close: false, overlay: true, displayMode: 'once', title: 'Confirm Delete', message: 'Are you sure you want to delete this category?', position: 'center', buttons: [
                ['<button>Yes, delete</button>', async function (instance, toast) {
                    instance.hide({ transitionOut: 'fadeOut' }, toast);
                    try {
                        const res = await fetch(url, { method: 'DELETE', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF } });
                        const json = await res.json();
                        if (!res.ok) throw new Error(json.message ?? 'Delete failed');
                        iziToast.success({ title: 'Deleted', message: json.message, position: 'topRight' });
                        categoryTable.ajax.reload(null, false);
                    } catch (e) {
                        iziToast.error({ title: 'Error', message: e.message, position: 'topRight' });
                    }
                }, true],
                ['<button>Cancel</button>', function (instance, toast) { instance.hide({ transitionOut: 'fadeOut' }, toast); }],
            ]});
        });
        document.getElementById('btn-add-category').addEventListener('click', () => { setMode('create'); openModal(); });
        ['modal-close', 'modal-cancel'].forEach(id => document.getElementById(id).addEventListener('click', closeModal));
        document.getElementById('category-modal-overlay').addEventListener('click', closeModal);
        document.addEventListener('keydown', e => { if (e.key === 'Escape' && modal.getAttribute('aria-hidden') === 'false') closeModal(); });
        document.addEventListener('DOMContentLoaded', initTable);
        document.addEventListener('livewire:navigated', initTable);
        })();
    </script>
</x-layouts::app>
