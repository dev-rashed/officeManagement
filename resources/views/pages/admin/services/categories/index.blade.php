@extends('layouts.app')

@section('title', 'Service Categories Management')

@section('content')
<x-flux::page-header title="Service Categories">
    <x-flux::toolbar>
        <x-flux::button href="{{ route('admin.service-categories.create') }}" variant="primary">
            Add Category
        </x-flux::toolbar>
</x-flux::page-header>

<div class="space-y-4">
    <div class="p-4 bg-white rounded-lg shadow">
        <table class="w-full" id="serviceCategoriesTable">
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const table = $('#serviceCategoriesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.service-categories.data') }}",
        columns: [
            { data: 'name', name: 'name' },
            { data: 'slug', name: 'slug' },
            { data: 'description', name: 'description' },
            { data: 'status', name: 'status', orderable: false, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        "language": {
            "emptyTable": "No service categories found"
        }
    });
});
</script>
@endpush
