@extends('layouts.app')

@section('title', 'Page Sections Management')

@section('content')
<x-flux::page-header title="Page Sections">
    <x-flux::toolbar>
        <x-flux::button href="{{ route('admin.page-sections.create') }}" variant="primary">
            Add New Section
        </x-flux::button>
    </x-flux::toolbar>
</x-flux::page-header>

<div class="space-y-4">
    <div class="p-4 bg-white rounded-lg shadow">
        <table class="w-full" id="pageSectionsTable">
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
