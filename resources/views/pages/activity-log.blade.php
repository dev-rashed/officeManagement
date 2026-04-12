<x-layouts::app :title="__('Activity Log')">
    <div class="space-y-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">{{ __('Activity Log') }}</h1>
                <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Recent user actions and activity history.') }}</p>
            </div>
        </div>

        <div class="grid gap-3 md:grid-cols-3">
            <div class="page-stat-card">
                <p class="page-stat-label">{{ __('Total Logs') }}</p>
                <p class="page-stat-value">{{ number_format($totalLogs) }}</p>
                <span class="page-stat-bar is-total"></span>
            </div>
            <div class="page-stat-card">
                <p class="page-stat-label">{{ __('Today') }}</p>
                <p class="page-stat-value">{{ number_format($todayLogs) }}</p>
                <span class="page-stat-bar is-pending"></span>
            </div>
            <div class="page-stat-card">
                <p class="page-stat-label">{{ __('Active Users') }}</p>
                <p class="page-stat-value">{{ number_format($activeUsers) }}</p>
                <span class="page-stat-bar is-active"></span>
            </div>
        </div>

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table
                    id="activity-log-table"
                    data-ajax-url="{{ route('activity.log.data') }}"
                    class="min-w-full text-left"
                    style="width:100%"
                >
                    <thead>
                        <tr>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('User') }}</th>
                            <th>{{ __('Action') }}</th>
                            <th>{{ __('Route') }}</th>
                            <th>{{ __('IP') }}</th>
                            <th>{{ __('Details') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($initialLogs as $log)
                            <tr>
                                <td>
                                    <div class="leading-tight">
                                        <div class="data-table-title">{{ $log->created_at?->format('M d, Y') }}</div>
                                        <div class="mt-1 data-table-subtle">{{ $log->created_at?->format('h:i A') }}</div>
                                    </div>
                                </td>
                                <td><span class="data-table-title">{{ $log->user?->name ?? __('System') }}</span></td>
                                <td><span class="data-table-badge is-active">{{ $log->action }}</span></td>
                                <td><span class="data-table-subtle">{{ $log->route_name ?: $log->url }}</span></td>
                                <td><span class="data-table-subtle">{{ $log->ip_address ?: __('N/A') }}</span></td>
                                <td><span class="data-table-subtle">{{ $log->description ?: __('No description') }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">{{ __('No activity logs found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
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

        #activity-log-table_wrapper {
            color: #475569;
        }

        #activity-log-table_wrapper .dataTables_length,
        #activity-log-table_wrapper .dataTables_filter {
            padding: 0.8rem 0.95rem 0;
        }

        #activity-log-table_wrapper .dataTables_info,
        #activity-log-table_wrapper .dataTables_paginate {
            padding: 0.8rem 0.95rem 0.95rem;
        }

        #activity-log-table_wrapper .dataTables_length label,
        #activity-log-table_wrapper .dataTables_filter label,
        #activity-log-table_wrapper .dataTables_info {
            align-items: center;
            color: #64748b;
            display: inline-flex;
            font-size: 0.68rem;
            font-weight: 500;
            gap: 0.35rem;
        }

        #activity-log-table_wrapper .dataTables_length select,
        #activity-log-table_wrapper .dataTables_filter input {
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

        #activity-log-table_wrapper .dataTables_filter {
            display: flex;
            justify-content: flex-end;
        }

        #activity-log-table_wrapper .dataTables_filter input {
            margin-left: 0.4rem;
            width: 12rem;
        }

        #activity-log-table {
            border-collapse: separate;
            border-spacing: 0;
            color: #0f172a;
        }

        #activity-log-table thead th {
            background: #fbfdff;
            border-bottom: 1px solid #e8eef6;
            color: #8ba0c0;
            font-size: 0.64rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            padding: 0.68rem 0.9rem;
            text-transform: uppercase;
        }

        #activity-log-table tbody td {
            background: #fff;
            border-bottom: 1px solid #edf2f7;
            font-size: 0.76rem;
            padding: 0.78rem 0.9rem;
            vertical-align: middle;
        }

        #activity-log-table tbody tr:hover td {
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

        #activity-log-table_wrapper .dataTables_paginate .paginate_button {
            background: #fff !important;
            border: 1px solid #dbe4f0 !important;
            border-radius: 9999px;
            color: #475569 !important;
            font-size: 0.68rem !important;
            margin-left: 0.25rem;
            min-width: 1.85rem;
            padding: 0.24rem 0.56rem !important;
        }

        #activity-log-table_wrapper .dataTables_paginate .paginate_button.current {
            background: #0f172a !important;
            border-color: #0f172a !important;
            color: #fff !important;
        }

        #activity-log-table_wrapper .dataTables_processing {
            background: #fff;
            border: 1px solid #dbe4f0;
            border-radius: 9999px;
            box-shadow: 0 12px 30px rgba(148, 163, 184, 0.18);
            color: #0f172a;
            font-size: 0.68rem;
            padding: 0.5rem 1rem;
        }

        .dark .page-stat-card,
        .dark #activity-log-table_wrapper .dataTables_length select,
        .dark #activity-log-table_wrapper .dataTables_filter input,
        .dark #activity-log-table tbody td,
        .dark #activity-log-table_wrapper .dataTables_paginate .paginate_button,
        .dark #activity-log-table_wrapper .dataTables_processing {
            background: #09090b !important;
            border-color: #27272a !important;
            color: #f4f4f5 !important;
        }

        .dark .page-stat-label,
        .dark #activity-log-table_wrapper .dataTables_length label,
        .dark #activity-log-table_wrapper .dataTables_filter label,
        .dark #activity-log-table_wrapper .dataTables_info,
        .dark #activity-log-table thead th,
        .dark .data-table-subtle {
            color: #94a3b8;
        }

        .dark .page-stat-value,
        .dark .data-table-title,
        .dark #activity-log-table {
            color: #fafafa;
        }

        .dark #activity-log-table thead th {
            background: #111827;
            border-bottom-color: #27272a;
        }

        .dark #activity-log-table tbody td {
            border-bottom-color: #27272a;
        }
    </style>

    <script>
        const initActivityLogDataTable = () => {
            if (typeof $ === 'undefined' || typeof $.fn.DataTable === 'undefined') return;

            const table = $('#activity-log-table');
            if (!table.length) return;
            if ($.fn.DataTable.isDataTable(table)) table.DataTable().destroy();

            const renderDateCell = (value) => {
                if (!value) return '<span class="data-table-subtle">N/A</span>';

                const date = new Date(value.replace(' ', 'T'));
                if (Number.isNaN(date.getTime())) return value;

                const formattedDate = date.toLocaleDateString(undefined, {
                    month: 'short',
                    day: '2-digit',
                    year: 'numeric',
                });

                const formattedTime = date.toLocaleTimeString(undefined, {
                    hour: 'numeric',
                    minute: '2-digit',
                });

                return `
                    <div class="leading-tight">
                        <div class="data-table-title">${formattedDate}</div>
                        <div class="mt-1 data-table-subtle">${formattedTime}</div>
                    </div>
                `;
            };

            table.DataTable({
                processing: true,
                serverSide: true,
                ajax: table.data('ajax-url'),
                order: [[0, 'desc']],
                pageLength: 10,
                lengthMenu: [[10, 25, 50], [10, 25, 50]],
                autoWidth: false,
                searchDelay: 300,
                dom: '<"flex flex-col gap-2 border-b border-slate-200 px-4 py-3 md:flex-row md:items-center md:justify-between dark:border-zinc-800"lf>rt<"flex flex-col gap-2 border-t border-slate-200 px-4 py-3 md:flex-row md:items-center md:justify-between dark:border-zinc-800"ip>',
                columns: [
                    { data: 'created_at', render: (data) => renderDateCell(data) },
                    { data: 'user', render: (data) => `<span class="data-table-title">${data || 'System'}</span>` },
                    { data: 'action', render: (data) => `<span class="data-table-badge is-active">${data || 'N/A'}</span>` },
                    { data: 'route_name', render: (data) => `<span class="data-table-subtle">${data || 'N/A'}</span>` },
                    { data: 'ip_address', render: (data) => `<span class="data-table-subtle">${data || 'N/A'}</span>` },
                    { data: 'description', render: (data) => `<span class="data-table-subtle">${data || 'No description'}</span>` },
                ],
                language: {
                    emptyTable: 'No activity logs found',
                    zeroRecords: 'No matching records found',
                    search: '',
                    searchPlaceholder: 'Search...',
                    lengthMenu: 'Show _MENU_',
                    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                    infoEmpty: 'No entries available',
                    paginate: { previous: 'Prev', next: 'Next' },
                },
            });
        };

        document.addEventListener('DOMContentLoaded', initActivityLogDataTable);
        document.addEventListener('livewire:navigated', initActivityLogDataTable);
    </script>
</x-layouts::app>
