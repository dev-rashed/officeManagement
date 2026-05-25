@extends('layouts.app')

@section('title', 'Courses Management')

@section('content')
<x-flux::page-header title="Courses">
    <x-flux::toolbar>
        <x-flux::button href="{{ route('admin.courses.create') }}" variant="primary">
            Add New Course
        </x-flux::button>
    </x-flux::toolbar>
</x-flux::page-header>

<div class="space-y-4">
    <div class="p-4 bg-white rounded-lg shadow">
        <table class="w-full" id="coursesTable">
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
