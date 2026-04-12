<x-layouts::app :title="__('Income Management')">
    <div class="space-y-5">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">{{ __('Income Management') }}</h1>
                <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Create, review and approve incoming revenue records.') }}</p>
            </div>
            <a href="{{ route('income.create') }}" class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-sky-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-sky-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                {{ __('Add New Income') }}
            </a>
        </div>

        <div class="grid gap-3 md:grid-cols-3">
            <div class="page-stat-card">
                <p class="page-stat-label">{{ __('Total Entries') }}</p>
                <p class="page-stat-value">{{ number_format($totalEntries) }}</p>
                <span class="page-stat-bar is-total"></span>
            </div>
            <div class="page-stat-card">
                <p class="page-stat-label">{{ __('Pending Approval') }}</p>
                <p class="page-stat-value">{{ number_format($pendingEntries) }}</p>
                <span class="page-stat-bar is-pending"></span>
            </div>
            <div class="page-stat-card">
                <p class="page-stat-label">{{ __('Approved') }}</p>
                <p class="page-stat-value">{{ number_format($approvedEntries) }}</p>
                <span class="page-stat-bar is-active"></span>
            </div>
        </div>

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table
                    id="income-table"
                    data-ajax-url="{{ route('income.data') }}"
                    class="min-w-full text-left"
                    style="width:100%"
                >
                    <thead>
                        <tr>
                            <th>{{ __('Title') }}</th>
                            <th>{{ __('Source / Category') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Next Approval') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

    </div>

    <style>
        .page-stat-card {
            background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
            border: 1px solid #dbe4f0;
            border-radius: 18px;
            box-shadow: 0 10px 24px rgba(148, 163, 184, 0.08);
            padding: 0.95rem 1rem 0.85rem;
        }

        .page-stat-label {
            color: #64748b;
            font-size: 0.72rem;
            font-weight: 600;
        }

        .page-stat-value {
            color: #0f172a;
            font-size: 1.7rem;
            font-weight: 700;
            line-height: 1;
            margin-top: 0.8rem;
        }

        .page-stat-bar {
            border-radius: 9999px;
            display: inline-block;
            height: 2px;
            margin-top: 0.9rem;
            width: 1.7rem;
        }

        .page-stat-bar.is-total { background: #64748b; }
        .page-stat-bar.is-pending { background: #f59e0b; }
        .page-stat-bar.is-active { background: #10b981; }

        #income-table_wrapper {
            color: #475569;
        }

        #income-table_wrapper .dataTables_length,
        #income-table_wrapper .dataTables_filter {
            padding: 0.8rem 0.95rem 0;
        }

        #income-table_wrapper .dataTables_info,
        #income-table_wrapper .dataTables_paginate {
            padding: 0.8rem 0.95rem 0.95rem;
        }

        #income-table_wrapper .dataTables_length label,
        #income-table_wrapper .dataTables_filter label,
        #income-table_wrapper .dataTables_info {
            align-items: center;
            color: #64748b;
            display: inline-flex;
            font-size: 0.68rem;
            font-weight: 500;
            gap: 0.35rem;
        }

        #income-table_wrapper .dataTables_length select,
        #income-table_wrapper .dataTables_filter input {
            background: #fff;
            border: 1px solid #dbe4f0;
            border-radius: 9999px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            color: #0f172a;
            font-size: 0.7rem;
            min-height: 1.95rem;
            outline: none;
            padding: 0.3rem 0.75rem;
        }

        #income-table_wrapper .dataTables_length select {
            min-width: 4.1rem;
            padding-right: 1.7rem;
        }

        #income-table_wrapper .dataTables_filter {
            display: flex;
            justify-content: flex-end;
        }

        #income-table_wrapper .dataTables_filter input {
            margin-left: 0.4rem;
            width: 12rem;
        }

        #income-table_wrapper .dataTables_filter input:focus,
        #income-table_wrapper .dataTables_length select:focus {
            border-color: #93c5fd;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
        }

        #income-table {
            border-collapse: separate;
            border-spacing: 0;
            color: #0f172a;
        }

        #income-table thead th {
            background: #fbfdff;
            border-bottom: 1px solid #e8eef6;
            color: #8ba0c0;
            font-size: 0.64rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            padding: 0.68rem 0.9rem;
            text-transform: uppercase;
        }

        #income-table tbody td {
            background: #fff;
            border-bottom: 1px solid #edf2f7;
            font-size: 0.76rem;
            padding: 0.78rem 0.9rem;
            vertical-align: middle;
        }

        #income-table tbody tr:hover td {
            background: #f8fbff;
        }

        .data-table-title {
            color: #0f172a;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .data-table-subtle {
            color: #64748b;
            font-size: 0.74rem;
        }

        .data-table-badge {
            border: 1px solid;
            border-radius: 9999px;
            display: inline-flex;
            font-size: 0.64rem;
            font-weight: 600;
            line-height: 1;
            padding: 0.25rem 0.48rem;
        }

        .data-table-badge.is-active {
            background: #ecfdf3;
            border-color: #a7f3d0;
            color: #047857;
        }

        .data-table-badge.is-pending {
            background: #fff7ed;
            border-color: #fdba74;
            color: #c2410c;
        }

        .data-table-badge.is-danger {
            background: #fef2f2;
            border-color: #fca5a5;
            color: #b91c1c;
        }

        .data-table-actions {
            display: inline-flex;
            gap: 0.32rem;
            white-space: nowrap;
        }

        .data-table-action-btn {
            border: 1px solid transparent;
            border-radius: 8px;
            font-size: 0.67rem;
            font-weight: 600;
            padding: 0.34rem 0.62rem;
        }

        .data-table-action-btn.is-neutral {
            background: #fff;
            border-color: #dbe4f0;
            color: #334155;
        }

        .data-table-action-btn.is-primary {
            background: #059669;
            color: #fff;
        }

        .data-table-action-btn.is-danger {
            background: #ef4444;
            color: #fff;
        }

        #income-table_wrapper .dataTables_paginate .paginate_button {
            background: #fff !important;
            border: 1px solid #dbe4f0 !important;
            border-radius: 9999px;
            color: #475569 !important;
            font-size: 0.68rem !important;
            margin-left: 0.25rem;
            min-width: 1.85rem;
            padding: 0.24rem 0.56rem !important;
        }

        #income-table_wrapper .dataTables_paginate .paginate_button:hover {
            background: #eff6ff !important;
            border-color: #bfdbfe !important;
            color: #1d4ed8 !important;
        }

        #income-table_wrapper .dataTables_paginate .paginate_button.current {
            background: #0f172a !important;
            border-color: #0f172a !important;
            color: #fff !important;
        }

        #income-table_wrapper .dataTables_processing {
            background: #fff;
            border: 1px solid #dbe4f0;
            border-radius: 9999px;
            box-shadow: 0 12px 30px rgba(148, 163, 184, 0.18);
            color: #0f172a;
            font-size: 0.68rem;
            padding: 0.5rem 1rem;
        }

        @media (max-width: 1024px) {
            #income-table_wrapper .dataTables_filter {
                justify-content: flex-start;
            }

            .data-table-actions {
                flex-wrap: wrap;
            }
        }

        .dark .page-stat-card,
        .dark #income-table_wrapper .dataTables_length select,
        .dark #income-table_wrapper .dataTables_filter input,
        .dark #income-table tbody td,
        .dark #income-table_wrapper .dataTables_paginate .paginate_button,
        .dark #income-table_wrapper .dataTables_processing,
        .dark .data-table-action-btn.is-neutral {
            background: #09090b !important;
            border-color: #27272a !important;
            color: #f4f4f5 !important;
        }

        .dark .page-stat-label,
        .dark #income-table_wrapper .dataTables_length label,
        .dark #income-table_wrapper .dataTables_filter label,
        .dark #income-table_wrapper .dataTables_info,
        .dark #income-table thead th,
        .dark .data-table-subtle {
            color: #94a3b8;
        }

        .dark .page-stat-value,
        .dark .data-table-title,
        .dark #income-table {
            color: #fafafa;
        }

        .dark #income-table thead th {
            background: #111827;
            border-bottom-color: #27272a;
        }

        .dark #income-table tbody td {
            border-bottom-color: #27272a;
        }

        .dark #income-table tbody tr:hover td {
            background: #111827;
        }
    </style>

    <script>
        const initIncomeTable = () => {
            if (typeof $ === 'undefined' || typeof $.fn.DataTable === 'undefined') return;

            const table = $('#income-table');
            if (!table.length) return;
            if ($.fn.DataTable.isDataTable(table)) table.DataTable().destroy();

            table.DataTable({
                processing: true,
                serverSide: true,
                ajax: table.data('ajax-url'),
                order: [[3, 'desc']],
                pageLength: 20,
                lengthMenu: [[10, 20, 50], [10, 20, 50]],
                autoWidth: false,
                searchDelay: 300,
                dom: '<"flex flex-col gap-2 border-b border-slate-200 px-4 py-3 md:flex-row md:items-center md:justify-between dark:border-zinc-800"lf>rt<"flex flex-col gap-2 border-t border-slate-200 px-4 py-3 md:flex-row md:items-center md:justify-between dark:border-zinc-800"ip>',
                columns: [
                    { data: 'title' },
                    { data: 'source_category' },
                    { data: 'amount' },
                    { data: 'date' },
                    { data: 'status' },
                    { data: 'next_approval', orderable: false, searchable: false },
                    { data: 'actions', orderable: false, searchable: false },
                ],
                language: {
                    emptyTable: 'No income entries found',
                    zeroRecords: 'No matching entries found',
                    search: '',
                    searchPlaceholder: 'Search...',
                    lengthMenu: 'Show _MENU_',
                    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                    infoEmpty: 'No entries available',
                    paginate: { previous: 'Prev', next: 'Next' },
                },
            });
        };

        document.addEventListener('DOMContentLoaded', initIncomeTable);
        document.addEventListener('livewire:navigated', initIncomeTable);

        $(document).on('click', '.income-delete-btn', function () {
            const url = $(this).data('destroy-url');
            const btn = $(this);

            iziToast.question({
                timeout: 0,
                close: false,
                overlay: true,
                displayMode: 'once',
                title: 'Confirm Delete',
                message: 'Are you sure you want to delete this income entry?',
                position: 'center',
                buttons: [
                    ['<button>Yes, delete</button>', function (instance, toast) {
                        instance.hide({ transitionOut: 'fadeOut' }, toast);
                        btn.prop('disabled', true).text('Deleting...');

                        $.ajax({
                            url: url,
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            success: function (res) {
                                iziToast.success({ title: 'Deleted', message: res.message, position: 'topRight' });
                                $('#income-table').DataTable().ajax.reload(null, false);
                            },
                            error: function () {
                                iziToast.error({ title: 'Error', message: 'Could not delete this entry.', position: 'topRight' });
                                btn.prop('disabled', false).text('Delete');
                            },
                        });
                    }, true],
                    ['<button>Cancel</button>', function (instance, toast) {
                        instance.hide({ transitionOut: 'fadeOut' }, toast);
                    }],
                ],
            });
        });
    </script>
</x-layouts::app>
