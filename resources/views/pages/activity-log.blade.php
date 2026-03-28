<x-layouts::app :title="__('Activity Log')">
    <div class="space-y-5">
        <div class="rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-semibold tracking-tight text-zinc-900 dark:text-white">{{ __('Activity Log') }}</h1>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Recent user actions and activity history.') }}</p>
                </div>
                <div class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 dark:border-emerald-900/70 dark:bg-emerald-950/40 dark:text-emerald-300">
                    {{ trans_choice(':count record|:count records', $totalLogs, ['count' => $totalLogs]) }}
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table
                    id="activity-log-table"
                    data-ajax-url="{{ route('activity.log.data') }}"
                    class="min-w-full border-collapse text-left text-sm"
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
                                        <div class="font-medium text-zinc-100">{{ $log->created_at?->format('M d, Y') }}</div>
                                        <div class="mt-1 text-[11px] text-zinc-500">{{ $log->created_at?->format('h:i A') }}</div>
                                    </div>
                                </td>
                                <td>
                                    <span class="font-medium text-zinc-100">{{ $log->user?->name ?? __('System') }}</span>
                                </td>
                                <td>
                                    <span class="inline-flex rounded-full border border-sky-900/80 bg-sky-950/50 px-2.5 py-1 text-[11px] font-medium text-sky-300">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td>
                                    <span class="inline-flex rounded-full border border-zinc-800 bg-zinc-800/90 px-2.5 py-1 text-[11px] font-medium text-zinc-200">
                                        {{ $log->route_name ?: $log->url }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-zinc-200">{{ $log->ip_address ?: __('N/A') }}</span>
                                </td>
                                <td>
                                    <span class="text-zinc-300">{{ $log->description ?: __('No description') }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ __('No activity logs found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <style>
        #activity-log-table_wrapper {
            color: #d4d4d8;
        }

        #activity-log-table_wrapper .dataTables_length,
        #activity-log-table_wrapper .dataTables_filter {
            padding: 0.85rem 1rem 0;
        }

        #activity-log-table_wrapper .dataTables_info,
        #activity-log-table_wrapper .dataTables_paginate {
            padding: 0.85rem 1rem 1rem;
        }

        #activity-log-table_wrapper .dataTables_length label,
        #activity-log-table_wrapper .dataTables_filter label,
        #activity-log-table_wrapper .dataTables_info {
            color: #a1a1aa;
            font-size: 0.8125rem;
        }

        #activity-log-table_wrapper .dataTables_length select,
        #activity-log-table_wrapper .dataTables_filter input {
            border: 1px solid rgba(63, 63, 70, 0.9);
            border-radius: 9999px;
            background: rgba(24, 24, 27, 0.95);
            color: #f4f4f5;
            min-height: 2.15rem;
            padding: 0.35rem 0.85rem;
            outline: none;
        }

        #activity-log-table_wrapper .dataTables_filter input {
            margin-left: 0.5rem;
            width: 15rem;
        }

        #activity-log-table {
            color: #e4e4e7;
        }

        #activity-log-table thead th {
            background: rgba(9, 9, 11, 0.82);
            border-bottom: 1px solid rgba(39, 39, 42, 1);
            color: #a1a1aa;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            padding: 0.85rem 1.1rem;
            text-transform: uppercase;
        }

        #activity-log-table tbody td {
            border-top: 1px solid rgba(39, 39, 42, 0.9);
            padding: 0.85rem 1.1rem;
            vertical-align: top;
        }

        #activity-log-table tbody tr {
            background: transparent;
            transition: background-color 0.2s ease;
        }

        #activity-log-table tbody tr:hover {
            background: rgba(39, 39, 42, 0.34);
        }

        #activity-log-table_wrapper .dataTables_paginate .paginate_button {
            border: 0 !important;
            border-radius: 9999px;
            color: #d4d4d8 !important;
            margin-left: 0.25rem;
            min-width: 2rem;
            padding: 0.35rem 0.7rem !important;
        }

        #activity-log-table_wrapper .dataTables_paginate .paginate_button:hover {
            background: rgba(39, 39, 42, 0.9) !important;
            color: #fff !important;
        }

        #activity-log-table_wrapper .dataTables_paginate .paginate_button.current {
            background: #f4f4f5 !important;
            color: #111827 !important;
        }

        #activity-log-table_wrapper .dataTables_processing {
            background: rgba(9, 9, 11, 0.95);
            border: 1px solid rgba(63, 63, 70, 0.9);
            border-radius: 9999px;
            box-shadow: none;
            color: #f4f4f5;
            padding: 0.5rem 1rem;
        }
    </style>

    <script>
        const initActivityLogDataTable = () => {
            if (typeof $ === 'undefined' || typeof $.fn.DataTable === 'undefined') {
                return;
            }

            const table = $('#activity-log-table');

            if (!table.length) {
                return;
            }

            if ($.fn.DataTable.isDataTable(table)) {
                table.DataTable().destroy();
            }

            const renderDateCell = (value) => {
                if (!value) {
                    return '<span class="text-zinc-500">N/A</span>';
                }

                const date = new Date(value.replace(' ', 'T'));

                if (Number.isNaN(date.getTime())) {
                    return value;
                }

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
                        <div class="font-medium text-zinc-100">${formattedDate}</div>
                        <div class="mt-1 text-[11px] text-zinc-500">${formattedTime}</div>
                    </div>
                `;
            };

            const renderBadge = (value, classes) => `
                <span class="inline-flex rounded-full border px-2.5 py-1 text-[11px] font-medium ${classes}">
                    ${value || 'N/A'}
                </span>
            `;

            table.DataTable({
                processing: true,
                serverSide: true,
                ajax: table.data('ajax-url'),
                order: [[0, 'desc']],
                pageLength: 10,
                lengthMenu: [[10, 25, 50], [10, 25, 50]],
                autoWidth: false,
                searchDelay: 300,
                dom: '<"flex flex-col gap-3 border-b border-zinc-800 px-4 py-3 md:flex-row md:items-center md:justify-between"lf>rt<"flex flex-col gap-3 border-t border-zinc-800 px-4 py-3 md:flex-row md:items-center md:justify-between"ip>',
                columns: [
                    {
                        data: 'created_at',
                        render: (data) => renderDateCell(data),
                    },
                    {
                        data: 'user',
                        render: (data) => `<span class="font-medium text-zinc-100">${data || 'System'}</span>`,
                    },
                    {
                        data: 'action',
                        render: (data) => renderBadge(data, 'border-sky-900/80 bg-sky-950/50 text-sky-300'),
                    },
                    {
                        data: 'route_name',
                        render: (data) => renderBadge(data, 'border-zinc-800 bg-zinc-800/90 text-zinc-200'),
                    },
                    {
                        data: 'ip_address',
                        render: (data) => `<span class="text-zinc-200">${data || 'N/A'}</span>`,
                    },
                    {
                        data: 'description',
                        render: (data) => `<span class="text-zinc-300">${data || 'No description'}</span>`,
                    },
                ],
                language: {
                    emptyTable: 'No activity logs found',
                    zeroRecords: 'No matching records found',
                    search: '',
                    searchPlaceholder: 'Search logs',
                    lengthMenu: 'Show _MENU_',
                    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                    infoEmpty: 'No entries available',
                    paginate: {
                        previous: 'Prev',
                        next: 'Next',
                    },
                },
            });
        };

        document.addEventListener('DOMContentLoaded', initActivityLogDataTable);
        document.addEventListener('livewire:navigated', initActivityLogDataTable);
    </script>
</x-layouts::app>
