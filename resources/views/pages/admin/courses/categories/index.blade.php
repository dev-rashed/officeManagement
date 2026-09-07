@extends('layouts.app')

@section('title', 'Course Categories Management')

@section('content')
<div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">Course Categories</h1>
    </div>
    <a href="{{ route('admin.course-categories.create') }}" class="inline-flex items-center justify-center rounded-xl bg-sky-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-sky-700">Add Category</a>
</div>

<div class="space-y-4">
    <div class="admin-table-shell">
        <div class="overflow-x-auto">
        <table class="admin-data-table" id="courseCategoriesTable">
            <thead>
                <tr class="border-b">
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
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
    const table = $('#courseCategoriesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.course-categories.data') }}",
        columns: [
            { data: 'name', name: 'name' },
            { data: 'slug', name: 'slug' },
            { data: 'description', name: 'description' },
            { data: 'status', name: 'status', orderable: false, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        "language": {
            "emptyTable": "No course categories found"
        }
    });
});
</script>
@endpush

