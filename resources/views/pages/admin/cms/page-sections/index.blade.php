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
document.addEventListener('DOMContentLoaded', function() {
    const table = $('#pageSectionsTable').DataTable({
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
});
</script>
@endpush

