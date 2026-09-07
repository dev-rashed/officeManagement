@extends('layouts.app')

@section('title', 'Courses Management')

@section('content')
<div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"><div><h1 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">Courses</h1></div><a href="{{ route('admin.courses.create') }}" class="inline-flex items-center justify-center rounded-xl bg-sky-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-sky-700">Add New Course</a></div>

<div class="space-y-4">
    <div class="admin-table-shell">
        <div class="overflow-x-auto">
        <table class="admin-data-table" id="coursesTable">
            <thead>
                <tr class="border-b">
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fee</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
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
    const table = $('#coursesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.courses.data') }}",
        columns: [
            { data: 'title', name: 'title' },
            { data: 'slug', name: 'slug' },
            { data: 'category', name: 'category.name' },
            { data: 'duration', name: 'duration' },
            { data: 'fee', name: 'fee' },
            { data: 'status', name: 'status', orderable: false, searchable: false },
            { data: 'sort_order', name: 'sort_order' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        "language": {
            "emptyTable": "No courses found"
        }
    });
});
</script>
@endpush

