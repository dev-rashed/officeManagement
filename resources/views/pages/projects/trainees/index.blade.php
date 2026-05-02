<x-layouts::app :title="__('Trainees')">
    <div class="space-y-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">{{ __('Trainees') }}</h1>
                <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Register and manage project trainee records.') }}</p>
            </div>
            <button type="button" id="btn-add-trainee" class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-sky-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-sky-700">
                {{ __('Add Trainee') }}
            </button>
        </div>

        <div class="grid gap-3 md:grid-cols-3">
            <div class="page-stat-card">
                <p class="page-stat-label">{{ __('Total Trainees') }}</p>
                <p class="page-stat-value">{{ number_format($totalTrainees) }}</p>
                <span class="page-stat-bar is-total"></span>
            </div>
            <div class="page-stat-card">
                <p class="page-stat-label">{{ __('With Photo') }}</p>
                <p class="page-stat-value">{{ number_format($withPhoto) }}</p>
                <span class="page-stat-bar is-active"></span>
            </div>
            <div class="page-stat-card">
                <p class="page-stat-label">{{ __('With Email') }}</p>
                <p class="page-stat-value">{{ number_format($withEmail) }}</p>
                <span class="page-stat-bar is-completed"></span>
            </div>
        </div>

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table id="trainees-table" data-ajax-url="{{ route('projects.trainees.data') }}" class="min-w-full text-left" style="width:100%">
                    <thead>
                        <tr>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Contact') }}</th>
                            <th>{{ __('Date of Birth') }}</th>
                            <th>{{ __('Parents') }}</th>
                            <th>{{ __('Emergency') }}</th>
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
        #trainees-table_wrapper { color: #475569; }
        #trainees-table_wrapper .dataTables_length, #trainees-table_wrapper .dataTables_filter { padding: 0.8rem 0.95rem 0; }
        #trainees-table_wrapper .dataTables_info, #trainees-table_wrapper .dataTables_paginate { padding: 0.8rem 0.95rem 0.95rem; }
        #trainees-table_wrapper .dataTables_length label, #trainees-table_wrapper .dataTables_filter label, #trainees-table_wrapper .dataTables_info { align-items: center; color: #64748b; display: inline-flex; font-size: 0.68rem; font-weight: 500; gap: 0.35rem; }
        #trainees-table_wrapper .dataTables_length select, #trainees-table_wrapper .dataTables_filter input { background: #fff; border: 1px solid #dbe4f0; border-radius: 9999px; color: #0f172a; font-size: 0.7rem; min-height: 1.95rem; outline: none; padding: 0.3rem 0.75rem; }
        #trainees-table { border-collapse: separate; border-spacing: 0; color: #0f172a; }
        #trainees-table thead th { background: #fbfdff; border-bottom: 1px solid #e8eef6; color: #8ba0c0; font-size: 0.64rem; font-weight: 700; letter-spacing: 0.18em; padding: 0.68rem 0.9rem; text-transform: uppercase; }
        #trainees-table tbody td { background: #fff; border-bottom: 1px solid #edf2f7; font-size: 0.76rem; padding: 0.78rem 0.9rem; vertical-align: middle; }
        .trainee-name-cell { align-items: center; display: flex; gap: 0.65rem; min-width: 13rem; }
        .trainee-photo { border-radius: 9999px; height: 2.35rem; object-fit: cover; width: 2.35rem; }
        .trainee-photo.is-empty { align-items: center; background: #e0f2fe; color: #0369a1; display: inline-flex; font-size: 0.8rem; font-weight: 700; justify-content: center; }
        .trainee-name-text { color: #0f172a; font-size: 0.78rem; font-weight: 600; }
        .trainee-subtle-text { color: #64748b; font-size: 0.72rem; line-height: 1.5; }
        .trainee-actions { display: inline-flex; gap: 0.32rem; white-space: nowrap; }
        .trainee-action-btn { border: 1px solid transparent; border-radius: 8px; font-size: 0.67rem; font-weight: 600; padding: 0.34rem 0.62rem; }
        .trainee-action-btn.is-primary { background: #059669; color: #fff; }
        .trainee-action-btn.is-danger { background: #ef4444; color: #fff; }
        .trainee-grid { display: grid; gap: 0.9rem; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .trainee-label { display: block; font-size: 0.72rem; font-weight: 600; color: #475569; margin-bottom: 0.35rem; }
        .trainee-input { width: 100%; border-radius: 10px; border: 1px solid #dbe4f0; background: #fff; color: #0f172a; font-size: 0.75rem; outline: none; padding: 0.65rem 0.8rem; }
        .trainee-error { margin-top: 0.35rem; font-size: 0.7rem; color: #e11d48; }
        @media (max-width: 768px) { .trainee-grid { grid-template-columns: 1fr; } }
        .dark .page-stat-card, .dark #trainees-table_wrapper .dataTables_length select, .dark #trainees-table_wrapper .dataTables_filter input, .dark #trainees-table tbody td, .dark .trainee-input { background: #09090b !important; border-color: #27272a !important; color: #f4f4f5 !important; }
        .dark .page-stat-label, .dark #trainees-table_wrapper .dataTables_length label, .dark #trainees-table_wrapper .dataTables_filter label, .dark #trainees-table_wrapper .dataTables_info, .dark #trainees-table thead th, .dark .trainee-subtle-text, .dark .trainee-label { color: #94a3b8; }
        .dark .page-stat-value, .dark .trainee-name-text, .dark #trainees-table { color: #fafafa; }
        .dark #trainees-table thead th { background: #111827; border-bottom-color: #27272a; }
        .dark #trainees-table tbody td { border-bottom-color: #27272a; }
    </style>

    <div id="trainee-modal" class="fixed inset-0 z-[110] hidden items-center justify-center p-4" aria-hidden="true">
        <div id="trainee-modal-overlay" class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
        <div class="relative w-full max-w-4xl rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="trainee-modal-title" class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Add Trainee') }}</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-zinc-400">{{ __('Enter student identity, contact, guardian, and emergency details.') }}</p>
                </div>
                <button id="trainee-modal-close" type="button" class="rounded-full bg-slate-100 px-2 py-1 text-slate-500 transition hover:bg-slate-200 hover:text-slate-900 dark:bg-zinc-800 dark:text-zinc-400">x</button>
            </div>

            <form id="trainee-form" class="mt-5 trainee-grid" enctype="multipart/form-data">
                <div>
                    <label for="first-name" class="trainee-label">{{ __('First Name') }}</label>
                    <input id="first-name" name="first_name" type="text" class="trainee-input">
                    <p data-error-for="first_name" class="trainee-error hidden"></p>
                </div>
                <div>
                    <label for="last-name" class="trainee-label">{{ __('Last Name') }}</label>
                    <input id="last-name" name="last_name" type="text" class="trainee-input">
                </div>
                <div>
                    <label for="nid" class="trainee-label">{{ __('NID') }}</label>
                    <input id="nid" name="nid" type="text" class="trainee-input">
                    <p data-error-for="nid" class="trainee-error hidden"></p>
                </div>
                <div>
                    <label for="email" class="trainee-label">{{ __('Email') }}</label>
                    <input id="email" name="email" type="email" class="trainee-input">
                    <p data-error-for="email" class="trainee-error hidden"></p>
                </div>
                <div>
                    <label for="phone" class="trainee-label">{{ __('Phone') }}</label>
                    <input id="phone" name="phone" type="text" class="trainee-input">
                </div>
                <div>
                    <label for="date-of-birth" class="trainee-label">{{ __('Date of Birth') }}</label>
                    <input id="date-of-birth" name="date_of_birth" type="date" class="trainee-input">
                </div>
                <div>
                    <label for="photo" class="trainee-label">{{ __('Photo') }}</label>
                    <input id="photo" name="photo" type="file" accept="image/*" class="trainee-input">
                    <p data-error-for="photo" class="trainee-error hidden"></p>
                </div>
                <div>
                    <label for="father-name" class="trainee-label">{{ __("Father's Name") }}</label>
                    <input id="father-name" name="father_name" type="text" class="trainee-input">
                </div>
                <div>
                    <label for="mother-name" class="trainee-label">{{ __("Mother's Name") }}</label>
                    <input id="mother-name" name="mother_name" type="text" class="trainee-input">
                </div>
                <div>
                    <label for="emergency-contact-number" class="trainee-label">{{ __('Emergency Contact Number') }}</label>
                    <input id="emergency-contact-number" name="emergency_contact_number" type="text" class="trainee-input">
                </div>
            </form>

            <div class="mt-5 flex items-center justify-end gap-2">
                <button id="trainee-modal-cancel" type="button" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-600 transition hover:bg-slate-50 hover:text-slate-900 dark:border-zinc-700 dark:bg-transparent dark:text-zinc-300">{{ __('Cancel') }}</button>
                <button id="trainee-modal-submit" type="button" class="rounded-lg bg-sky-600 px-3 py-2 text-xs font-medium text-white transition hover:bg-sky-700 disabled:opacity-60">{{ __('Save Trainee') }}</button>
            </div>
        </div>
    </div>

    <script>
        (() => {
        const CSRF = '{{ csrf_token() }}';
        const STORE_URL = @js(route('projects.trainees.store'));
        let traineeTable = null;
        let editingTraineeId = null;

        const modal = document.getElementById('trainee-modal');
        const form = document.getElementById('trainee-form');
        const submitBtn = document.getElementById('trainee-modal-submit');
        const title = document.getElementById('trainee-modal-title');

        const initTraineesTable = () => {
            if (typeof $ === 'undefined' || !$.fn.DataTable) return;
            const el = $('#trainees-table');
            if (!el.length) return;
            if ($.fn.DataTable.isDataTable(el)) el.DataTable().destroy();
            traineeTable = el.DataTable({
                processing: true,
                serverSide: true,
                ajax: el.data('ajax-url'),
                order: [[0, 'asc']],
                pageLength: 20,
                lengthMenu: [[10, 20, 50], [10, 20, 50]],
                autoWidth: false,
                searchDelay: 300,
                dom: '<"flex flex-col gap-2 border-b border-slate-200 px-4 py-3 md:flex-row md:items-center md:justify-between dark:border-zinc-800"lf>rt<"flex flex-col gap-2 border-t border-slate-200 px-4 py-3 md:flex-row md:items-center md:justify-between dark:border-zinc-800"ip>',
                columns: [
                    { data: 'name' },
                    { data: 'contact', orderable: false },
                    { data: 'date_of_birth' },
                    { data: 'parents', orderable: false, searchable: false },
                    { data: 'emergency_contact', orderable: false, searchable: false },
                    { data: 'actions', orderable: false, searchable: false },
                ],
                language: { emptyTable: 'No trainees found', zeroRecords: 'No matching trainees found', search: '', searchPlaceholder: 'Search...', lengthMenu: 'Show _MENU_', info: 'Showing _START_ to _END_ of _TOTAL_ trainees', infoEmpty: 'No trainees available', paginate: { previous: 'Prev', next: 'Next' } },
            });
        };

        const openModal = () => { modal.classList.remove('hidden'); modal.classList.add('flex'); modal.setAttribute('aria-hidden', 'false'); document.getElementById('first-name').focus(); };
        const closeModal = () => { modal.classList.add('hidden'); modal.classList.remove('flex'); modal.setAttribute('aria-hidden', 'true'); clearErrors(); };
        const clearErrors = () => {
            form.querySelectorAll('[data-error-for]').forEach(el => { el.textContent = ''; el.classList.add('hidden'); });
            form.querySelectorAll('.border-rose-500').forEach(el => el.classList.remove('border-rose-500'));
        };
        const showErrors = (errors) => {
            Object.entries(errors).forEach(([field, messages]) => {
                const error = form.querySelector(`[data-error-for="${field}"]`);
                const input = form.querySelector(`[name="${field}"]`);
                if (error) { error.textContent = messages[0]; error.classList.remove('hidden'); }
                if (input) input.classList.add('border-rose-500');
            });
        };
        const setMode = (mode, data = {}) => {
            clearErrors();
            form.reset();
            if (mode === 'create') {
                editingTraineeId = null;
                title.textContent = 'Add Trainee';
                submitBtn.textContent = 'Save Trainee';
                return;
            }

            editingTraineeId = data.id;
            title.textContent = 'Edit Trainee';
            submitBtn.textContent = 'Update Trainee';
            Object.entries(data).forEach(([key, value]) => {
                const input = form.querySelector(`[name="${key}"]`);
                if (input && key !== 'photo') input.value = value ?? '';
            });
        };

        submitBtn.addEventListener('click', async () => {
            clearErrors();
            const formData = new FormData(form);
            const url = editingTraineeId ? `/projects/trainees/${editingTraineeId}` : STORE_URL;
            submitBtn.disabled = true;
            const original = submitBtn.textContent;
            submitBtn.textContent = 'Saving...';

            try {
                const res = await fetch(url, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }, body: formData });
                const json = await res.json();
                if (res.status === 422) { showErrors(json.errors ?? {}); return; }
                if (!res.ok) throw new Error(json.message ?? 'Server error');
                closeModal();
                iziToast.success({ title: editingTraineeId ? 'Updated' : 'Created', message: json.message, position: 'topRight' });
                traineeTable.ajax.reload(null, false);
            } catch (e) {
                iziToast.error({ title: 'Error', message: e.message || 'Something went wrong.', position: 'topRight' });
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = original;
            }
        });

        $(document).on('click', '.trainee-edit-btn', function () {
            setMode('edit', {
                id: $(this).data('trainee-id'),
                first_name: $(this).data('first-name'),
                last_name: $(this).data('last-name'),
                nid: $(this).data('nid'),
                email: $(this).data('email'),
                phone: $(this).data('phone'),
                date_of_birth: $(this).data('date-of-birth'),
                father_name: $(this).data('father-name'),
                mother_name: $(this).data('mother-name'),
                emergency_contact_number: $(this).data('emergency-contact-number'),
            });
            openModal();
        });

        $(document).on('click', '.trainee-delete-btn', function () {
            const url = $(this).data('destroy-url');
            iziToast.question({ timeout: 0, close: false, overlay: true, displayMode: 'once', title: 'Confirm Delete', message: 'Are you sure you want to delete this trainee?', position: 'center', buttons: [
                ['<button>Yes, delete</button>', async function (instance, toast) {
                    instance.hide({ transitionOut: 'fadeOut' }, toast);
                    try {
                        const res = await fetch(url, { method: 'DELETE', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF } });
                        const json = await res.json();
                        if (!res.ok) throw new Error(json.message ?? 'Delete failed');
                        iziToast.success({ title: 'Deleted', message: json.message, position: 'topRight' });
                        traineeTable.ajax.reload(null, false);
                    } catch (e) {
                        iziToast.error({ title: 'Error', message: e.message, position: 'topRight' });
                    }
                }, true],
                ['<button>Cancel</button>', function (instance, toast) { instance.hide({ transitionOut: 'fadeOut' }, toast); }],
            ]});
        });

        document.getElementById('btn-add-trainee').addEventListener('click', () => { setMode('create'); openModal(); });
        ['trainee-modal-close', 'trainee-modal-cancel'].forEach(id => document.getElementById(id).addEventListener('click', closeModal));
        document.getElementById('trainee-modal-overlay').addEventListener('click', closeModal);
        document.addEventListener('keydown', e => { if (e.key === 'Escape' && modal.getAttribute('aria-hidden') === 'false') closeModal(); });
        document.addEventListener('DOMContentLoaded', initTraineesTable);
        document.addEventListener('livewire:navigated', initTraineesTable);
        })();
    </script>
</x-layouts::app>
