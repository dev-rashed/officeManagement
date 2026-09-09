@extends('layouts.app')

@section('title', 'Page Sections Management')

@section('content')
<div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"><div><h1 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">Page Sections</h1></div><a href="{{ route('admin.page-sections.create') }}" class="inline-flex items-center justify-center rounded-xl bg-sky-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-sky-700">Add New Section</a></div>

<div class="space-y-4">
    <div class="admin-table-shell">
        <div class="overflow-x-auto">
        <table class="admin-data-table" id="pageSectionsTable">
            <thead>
                <tr class="border-b">
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Page</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Section</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Visible</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sort</th>
                    <th class="p-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data will be loaded via AJAX -->
            </tbody>
        </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
const TABLE_ID = "pageSectionsTable";
const __initPage = function () {
    // Fires for every wire:navigate, so ignore other pages.
    if (!document.getElementById(TABLE_ID) || typeof $ === 'undefined' || !$.fn.DataTable) return;

    const table = (function(){ const $el = $('#pageSectionsTable'); if ($.fn.DataTable.isDataTable($el)) { $el.DataTable().destroy(); } return $el; })().DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.page-sections.data') }}",
        columns: [
            { data: 'page_slug', name: 'page_slug' },
            { data: 'section_key', name: 'section_key' },
            { data: 'title', name: 'title' },
            { data: 'image', name: 'image', orderable: false, searchable: false },
            { data: 'visibility', name: 'visibility', orderable: false, searchable: false },
            { data: 'sort_order', name: 'sort_order' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        "language": {
            "emptyTable": "No page sections found"
        }
    });
};

/*
 * The sidebar navigates with wire:navigate, which swaps the body without a
 * reload -- DOMContentLoaded will not fire again, so the table has to be built
 * on each swap. The handler is kept on window under a per-table key and the
 * previous one removed first, otherwise every visit would stack another
 * listener.
 */
const HANDLER_KEY = '__dtInit_' + TABLE_ID;

if (window[HANDLER_KEY]) {
    document.removeEventListener('livewire:navigated', window[HANDLER_KEY]);
}

window[HANDLER_KEY] = __initPage;
document.addEventListener('livewire:navigated', __initPage);

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', __initPage);
} else {
    __initPage();
}
})();
</script>
@endpush

