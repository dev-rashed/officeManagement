<x-layouts::app :title="__('Projects')">
    <div class="space-y-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">{{ __('Projects') }}</h1>
                <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Track projects, dates, categories, and trainee progress in one place.') }}</p>
            </div>
            <button type="button" id="btn-add-project" class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-sky-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-sky-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                {{ __('Add Project') }}
            </button>
        </div>

        <div class="grid gap-3 md:grid-cols-3">
            <div class="page-stat-card">
                <p class="page-stat-label">{{ __('Total Projects') }}</p>
                <p class="page-stat-value">{{ number_format($totalProjects) }}</p>
                <span class="page-stat-bar is-total"></span>
            </div>
            <div class="page-stat-card">
                <p class="page-stat-label">{{ __('Active Projects') }}</p>
                <p class="page-stat-value">{{ number_format($activeProjects) }}</p>
                <span class="page-stat-bar is-active"></span>
            </div>
            <div class="page-stat-card">
                <p class="page-stat-label">{{ __('Completed Projects') }}</p>
                <p class="page-stat-value">{{ number_format($completedProjects) }}</p>
                <span class="page-stat-bar is-completed"></span>
            </div>
        </div>

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table id="projects-table" data-ajax-url="{{ route('projects.data') }}" class="min-w-full text-left" style="width:100%">
                    <thead>
                        <tr>
                            <th>{{ __('Title') }}</th>
                            <th>{{ __('Category') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Start Time') }}</th>
                            <th>{{ __('End Time') }}</th>
                            <th>{{ __('Trainees') }}</th>
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
        .page-stat-bar.is-completed { background: #2563eb; }
        #projects-table_wrapper { color: #475569; }
        #projects-table_wrapper .dataTables_length, #projects-table_wrapper .dataTables_filter { padding: 0.8rem 0.95rem 0; }
        #projects-table_wrapper .dataTables_info, #projects-table_wrapper .dataTables_paginate { padding: 0.8rem 0.95rem 0.95rem; }
        #projects-table_wrapper .dataTables_length label, #projects-table_wrapper .dataTables_filter label, #projects-table_wrapper .dataTables_info { align-items: center; color: #64748b; display: inline-flex; font-size: 0.68rem; font-weight: 500; gap: 0.35rem; }
        #projects-table_wrapper .dataTables_length select, #projects-table_wrapper .dataTables_filter input { background: #fff; border: 1px solid #dbe4f0; border-radius: 9999px; box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04); color: #0f172a; font-size: 0.7rem; min-height: 1.95rem; outline: none; padding: 0.3rem 0.75rem; }
        #projects-table_wrapper .dataTables_filter { display: flex; justify-content: flex-end; }
        #projects-table_wrapper .dataTables_filter input { margin-left: 0.4rem; width: 12rem; }
        #projects-table { border-collapse: separate; border-spacing: 0; color: #0f172a; }
        #projects-table thead th { background: #fbfdff; border-bottom: 1px solid #e8eef6; color: #8ba0c0; font-size: 0.64rem; font-weight: 700; letter-spacing: 0.18em; padding: 0.68rem 0.9rem; text-transform: uppercase; }
        #projects-table tbody td { background: #fff; border-bottom: 1px solid #edf2f7; font-size: 0.76rem; padding: 0.78rem 0.9rem; vertical-align: middle; }
        #projects-table tbody tr:hover td { background: #f8fbff; }
        .project-title-text { color: #0f172a; font-size: 0.78rem; font-weight: 600; }
        .project-subtle-text { color: #64748b; font-size: 0.72rem; margin-top: 0.2rem; }
        .project-status-badge { border: 1px solid; border-radius: 9999px; display: inline-flex; font-size: 0.64rem; font-weight: 600; line-height: 1; padding: 0.25rem 0.48rem; }
        .project-status-badge.is-active { background: #ecfdf3; border-color: #a7f3d0; color: #047857; }
        .project-status-badge.is-completed { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }
        .project-status-badge.is-pending { background: #fff7ed; border-color: #fdba74; color: #c2410c; }
        .project-status-badge.is-danger { background: #fef2f2; border-color: #fca5a5; color: #b91c1c; }
        .project-actions { display: inline-flex; gap: 0.32rem; white-space: nowrap; }
        .project-action-btn { border: 1px solid transparent; border-radius: 8px; font-size: 0.67rem; font-weight: 600; padding: 0.34rem 0.62rem; }
        .project-action-btn.is-primary { background: #059669; color: #fff; }
        .project-action-btn.is-danger { background: #ef4444; color: #fff; }
        #projects-table_wrapper .dataTables_paginate .paginate_button { background: #fff !important; border: 1px solid #dbe4f0 !important; border-radius: 9999px; color: #475569 !important; font-size: 0.68rem !important; margin-left: 0.25rem; min-width: 1.85rem; padding: 0.24rem 0.56rem !important; }
        #projects-table_wrapper .dataTables_paginate .paginate_button.current { background: #0f172a !important; border-color: #0f172a !important; color: #fff !important; }
        #projects-table_wrapper .dataTables_processing { background: #fff; border: 1px solid #dbe4f0; border-radius: 9999px; box-shadow: 0 12px 30px rgba(148, 163, 184, 0.18); color: #0f172a; font-size: 0.68rem; padding: 0.5rem 1rem; }
        .project-grid { display: grid; gap: 0.9rem; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .project-grid-full { grid-column: span 2; }
        .project-label { display: block; font-size: 0.72rem; font-weight: 600; color: #475569; margin-bottom: 0.35rem; }
        .project-input, .project-select, .project-textarea { width: 100%; border-radius: 10px; border: 1px solid #dbe4f0; background: #fff; color: #0f172a; font-size: 0.75rem; outline: none; padding: 0.65rem 0.8rem; }
        .project-textarea { min-height: 6rem; resize: vertical; }
        .project-input:focus, .project-select:focus, .project-textarea:focus { border-color: #93c5fd; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12); }
        .project-error { margin-top: 0.35rem; font-size: 0.7rem; color: #e11d48; }
        @media (max-width: 768px) { .project-grid { grid-template-columns: 1fr; } .project-grid-full { grid-column: span 1; } }
        .dark .page-stat-card, .dark #projects-table_wrapper .dataTables_length select, .dark #projects-table_wrapper .dataTables_filter input, .dark #projects-table tbody td, .dark #projects-table_wrapper .dataTables_paginate .paginate_button, .dark #projects-table_wrapper .dataTables_processing, .dark .project-input, .dark .project-select, .dark .project-textarea { background: #09090b !important; border-color: #27272a !important; color: #f4f4f5 !important; }
        .dark .page-stat-label, .dark #projects-table_wrapper .dataTables_length label, .dark #projects-table_wrapper .dataTables_filter label, .dark #projects-table_wrapper .dataTables_info, .dark #projects-table thead th, .dark .project-subtle-text, .dark .project-label { color: #94a3b8; }
        .dark .page-stat-value, .dark .project-title-text, .dark #projects-table { color: #fafafa; }
        .dark #projects-table thead th { background: #111827; border-bottom-color: #27272a; }
        .dark #projects-table tbody td { border-bottom-color: #27272a; }
    </style>

    <div id="project-modal" class="fixed inset-0 z-[110] hidden items-center justify-center p-4" aria-hidden="true">
        <div id="project-modal-overlay" class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
        <div class="relative w-full max-w-4xl rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="project-modal-title" class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Add Project') }}</h2>
                    <p id="project-modal-desc" class="mt-0.5 text-xs text-slate-500 dark:text-zinc-400">{{ __('Add a project with category, schedule, and trainee targets.') }}</p>
                </div>
                <button id="project-modal-close" type="button" class="rounded-full bg-slate-100 p-1.5 text-slate-500 transition hover:bg-slate-200 hover:text-slate-900 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700 dark:hover:text-white">×</button>
            </div>

            <div class="mt-5 project-grid">
                <div>
                    <label for="project-title" class="project-label">{{ __('Title') }}</label>
                    <input id="project-title" type="text" class="project-input">
                    <p id="project-title-error" class="project-error hidden"></p>
                </div>
                <div>
                    <label for="project-category-id" class="project-label">{{ __('Category') }}</label>
                    <select id="project-category-id" class="project-select">
                        <option value="">{{ __('Select category') }}</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="project-status" class="project-label">{{ __('Status') }}</label>
                    <select id="project-status" class="project-select">
                        <option value="planning">{{ __('Planning') }}</option>
                        <option value="active">{{ __('Active') }}</option>
                        <option value="on_hold">{{ __('On Hold') }}</option>
                        <option value="completed">{{ __('Completed') }}</option>
                        <option value="cancelled">{{ __('Cancelled') }}</option>
                    </select>
                </div>
                <div>
                    <label for="project-targeted-trainees" class="project-label">{{ __('Total Targeted Trainee') }}</label>
                    <input id="project-targeted-trainees" type="number" min="0" step="1" class="project-input" placeholder="{{ __('Optional') }}">
                </div>
                <div>
                    <label for="project-start-date" class="project-label">{{ __('Starting Time') }}</label>
                    <input id="project-start-date" type="datetime-local" class="project-input">
                    <p id="project-start-date-error" class="project-error hidden"></p>
                </div>
                <div>
                    <label for="project-end-date" class="project-label">{{ __('End Time') }}</label>
                    <input id="project-end-date" type="datetime-local" class="project-input">
                </div>
                <div>
                    <label for="project-completed-trainees" class="project-label">{{ __('Total Completed Trainee') }}</label>
                    <input id="project-completed-trainees" type="number" min="0" step="1" class="project-input" value="0">
                </div>
                <div class="project-grid-full">
                    <label for="project-description" class="project-label">{{ __('Description') }}</label>
                    <textarea id="project-description" class="project-textarea"></textarea>
                </div>
                <div class="project-grid-full">
                    <label for="project-notes" class="project-label">{{ __('Notes') }}</label>
                    <textarea id="project-notes" class="project-textarea"></textarea>
                </div>
            </div>

            <div class="mt-5 flex items-center justify-end gap-2">
                <button id="project-modal-cancel" type="button" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-600 transition hover:bg-slate-50 hover:text-slate-900 dark:border-zinc-700 dark:bg-transparent dark:text-zinc-300">{{ __('Cancel') }}</button>
                <button id="project-modal-submit" type="button" class="rounded-lg bg-sky-600 px-3 py-2 text-xs font-medium text-white transition hover:bg-sky-700 disabled:opacity-60">{{ __('Save Project') }}</button>
            </div>
        </div>
    </div>

    <script>
        (() => {
        const PROJECT_CSRF = '{{ csrf_token() }}';
        const PROJECT_STORE_URL = @js(route('projects.store'));
        let projectTable = null;
        let editingProjectId = null;
        const projectModal = document.getElementById('project-modal');
        const projectModalTitle = document.getElementById('project-modal-title');
        const projectModalDesc = document.getElementById('project-modal-desc');
        const projectTitle = document.getElementById('project-title');
        const projectCategoryId = document.getElementById('project-category-id');
        const projectStatus = document.getElementById('project-status');
        const projectStartDate = document.getElementById('project-start-date');
        const projectEndDate = document.getElementById('project-end-date');
        const projectTargetedTrainees = document.getElementById('project-targeted-trainees');
        const projectCompletedTrainees = document.getElementById('project-completed-trainees');
        const projectDescription = document.getElementById('project-description');
        const projectNotes = document.getElementById('project-notes');
        const projectTitleError = document.getElementById('project-title-error');
        const projectStartDateError = document.getElementById('project-start-date-error');
        const projectSubmitBtn = document.getElementById('project-modal-submit');

        const initProjectsTable = () => {
            if (typeof $ === 'undefined' || !$.fn.DataTable) return;
            const el = $('#projects-table');
            if (!el.length) return;
            if ($.fn.DataTable.isDataTable(el)) el.DataTable().destroy();
            projectTable = el.DataTable({
                processing: true, serverSide: true, ajax: el.data('ajax-url'), order: [[3, 'desc']], pageLength: 20, lengthMenu: [[10, 20, 50], [10, 20, 50]], autoWidth: false, searchDelay: 300,
                dom: '<"flex flex-col gap-2 border-b border-slate-200 px-4 py-3 md:flex-row md:items-center md:justify-between dark:border-zinc-800"lf>rt<"flex flex-col gap-2 border-t border-slate-200 px-4 py-3 md:flex-row md:items-center md:justify-between dark:border-zinc-800"ip>',
                columns: [{ data: 'title' }, { data: 'category', orderable: false, searchable: false }, { data: 'status', orderable: false, searchable: false }, { data: 'start_date' }, { data: 'end_date' }, { data: 'trainees', orderable: false, searchable: false }, { data: 'actions', orderable: false, searchable: false }],
                language: { emptyTable: 'No projects found', zeroRecords: 'No matching projects found', search: '', searchPlaceholder: 'Search...', lengthMenu: 'Show _MENU_', info: 'Showing _START_ to _END_ of _TOTAL_ entries', infoEmpty: 'No entries available', paginate: { previous: 'Prev', next: 'Next' } },
            });
        };
        const clearProjectErrors = () => {
            projectTitleError.textContent = ''; projectTitleError.classList.add('hidden'); projectStartDateError.textContent = ''; projectStartDateError.classList.add('hidden');
            projectTitle.classList.remove('border-rose-500'); projectStartDate.classList.remove('border-rose-500');
        };
        const setProjectError = (field, element, message) => { field.textContent = message; field.classList.remove('hidden'); element.classList.add('border-rose-500'); };
        const openProjectModal = () => { projectModal.classList.remove('hidden'); projectModal.classList.add('flex'); projectModal.setAttribute('aria-hidden', 'false'); projectTitle.focus(); };
        const closeProjectModal = () => { projectModal.classList.add('hidden'); projectModal.classList.remove('flex'); projectModal.setAttribute('aria-hidden', 'true'); clearProjectErrors(); };
        const fillProjectForm = (data = {}) => {
            projectTitle.value = data.title ?? ''; projectCategoryId.value = data.project_category_id ?? ''; projectStatus.value = data.status ?? 'planning'; projectStartDate.value = data.start_date ?? ''; projectEndDate.value = data.end_date ?? '';
            projectTargetedTrainees.value = data.targeted_trainees ?? ''; projectCompletedTrainees.value = data.completed_trainees ?? 0; projectDescription.value = data.description ?? ''; projectNotes.value = data.notes ?? '';
        };
        const setProjectMode = (mode, data = {}) => {
            clearProjectErrors();
            if (mode === 'create') {
                editingProjectId = null; projectModalTitle.textContent = 'Add Project'; projectModalDesc.textContent = 'Add a project with category, schedule, and trainee targets.'; projectSubmitBtn.textContent = 'Save Project'; fillProjectForm();
            } else {
                editingProjectId = data.id; projectModalTitle.textContent = 'Edit Project'; projectModalDesc.textContent = 'Update the project category, schedule, or trainee progress.'; projectSubmitBtn.textContent = 'Update Project'; fillProjectForm(data);
            }
        };
        projectSubmitBtn.addEventListener('click', async () => {
            clearProjectErrors();
            const payload = { title: projectTitle.value.trim(), project_category_id: projectCategoryId.value || null, status: projectStatus.value, start_date: projectStartDate.value, end_date: projectEndDate.value || null, targeted_trainees: projectTargetedTrainees.value || null, completed_trainees: projectCompletedTrainees.value || 0, description: projectDescription.value.trim() || null, notes: projectNotes.value.trim() || null };
            if (!payload.title) return setProjectError(projectTitleError, projectTitle, 'Title is required.');
            if (!payload.start_date) return setProjectError(projectStartDateError, projectStartDate, 'Start date is required.');
            const isEdit = editingProjectId !== null;
            const url = isEdit ? `/projects/${editingProjectId}` : PROJECT_STORE_URL;
            const method = isEdit ? 'PUT' : 'POST';
            projectSubmitBtn.disabled = true;
            const original = projectSubmitBtn.textContent;
            projectSubmitBtn.textContent = 'Saving...';
            try {
                const res = await fetch(url, { method, headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': PROJECT_CSRF }, body: JSON.stringify(payload) });
                const json = await res.json();
                if (res.status === 422) {
                    const errors = json.errors ?? {};
                    if (errors.title) setProjectError(projectTitleError, projectTitle, errors.title[0]);
                    if (errors.start_date) setProjectError(projectStartDateError, projectStartDate, errors.start_date[0]);
                    return;
                }
                if (!res.ok) throw new Error(json.message ?? 'Server error');
                closeProjectModal();
                iziToast.success({ title: isEdit ? 'Updated' : 'Created', message: json.message, position: 'topRight' });
                projectTable.ajax.reload(null, false);
            } catch (e) {
                iziToast.error({ title: 'Error', message: e.message || 'Something went wrong.', position: 'topRight' });
            } finally {
                projectSubmitBtn.disabled = false;
                projectSubmitBtn.textContent = original;
            }
        });
        $(document).on('click', '.project-edit-btn', function () {
            setProjectMode('edit', {
                id: $(this).data('project-id'), title: $(this).data('title'), project_category_id: $(this).data('project-category-id'), status: $(this).data('status'),
                start_date: $(this).data('start-date'), end_date: $(this).data('end-date'), targeted_trainees: $(this).data('targeted-trainees'),
                completed_trainees: $(this).data('completed-trainees'), description: $(this).data('description'), notes: $(this).data('notes'),
            });
            openProjectModal();
        });
        $(document).on('click', '.project-delete-btn', function () {
            const url = $(this).data('destroy-url');
            iziToast.question({ timeout: 0, close: false, overlay: true, displayMode: 'once', title: 'Confirm Delete', message: 'Are you sure you want to delete this project?', position: 'center', buttons: [
                ['<button>Yes, delete</button>', async function (instance, toast) {
                    instance.hide({ transitionOut: 'fadeOut' }, toast);
                    try {
                        const res = await fetch(url, { method: 'DELETE', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': PROJECT_CSRF } });
                        const json = await res.json();
                        if (!res.ok) throw new Error(json.message ?? 'Delete failed');
                        iziToast.success({ title: 'Deleted', message: json.message, position: 'topRight' });
                        projectTable.ajax.reload(null, false);
                    } catch (e) {
                        iziToast.error({ title: 'Error', message: e.message, position: 'topRight' });
                    }
                }, true],
                ['<button>Cancel</button>', function (instance, toast) { instance.hide({ transitionOut: 'fadeOut' }, toast); }],
            ]});
        });
        document.getElementById('btn-add-project').addEventListener('click', () => { setProjectMode('create'); openProjectModal(); });
        ['project-modal-close', 'project-modal-cancel'].forEach(id => document.getElementById(id).addEventListener('click', closeProjectModal));
        document.getElementById('project-modal-overlay').addEventListener('click', closeProjectModal);
        document.addEventListener('keydown', e => { if (e.key === 'Escape' && projectModal.getAttribute('aria-hidden') === 'false') closeProjectModal(); });
        document.addEventListener('DOMContentLoaded', initProjectsTable);
        document.addEventListener('livewire:navigated', initProjectsTable);
        })();
    </script>
</x-layouts::app>
