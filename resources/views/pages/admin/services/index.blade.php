@extends('layouts.app')

@section('title', 'Services Management')

@section('content')
<x-flux::page-header title="Services">
    <x-flux::toolbar>
        <x-flux::button href="{{ route('admin.services.create') }}" variant="primary">
            Add New Service
        </x-flux::button>
    </x-flux::toolbar>
</x-flux::page-header>

<div class="space-y-4">
    <div class="p-4 bg-white rounded-lg shadow">
        <table class="w-full" id="servicesTable">
            <thead>
                <tr class="border-b">
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Show on Homepage</th>
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
    const table = $('#servicesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.services.data') }}",
        columns: [
            { data: 'title', name: 'title' },
            { data: 'slug', name: 'slug' },
            { data: 'category', name: 'category.name' },
            { data: 'status', name: 'status', orderable: false, searchable: false },
            { data: 'show_on_homepage', name: 'show_on_homepage', orderable: false, searchable: false },
            { data: 'sort_order', name: 'sort_order' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        "language": {
            "emptyTable": "No services found"
        }
    });
});
</script>
@endpush
