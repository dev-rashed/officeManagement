@extends('layouts.app')

@section('title', 'Portfolio Items Management')

@section('content')
<x-flux::page-header title="Portfolio Items">
    <x-flux::toolbar>
        <x-flux::button href="{{ route('admin.portfolio.create') }}" variant="primary">
            Add Portfolio Item
        </x-flux::button>
    </x-flux::toolbar>
</x-flux::page-header>

<div class="space-y-4">
    <div class="p-4 bg-white rounded-lg shadow">
        <table class="w-full" id="portfolioTable">
            <thead>
                <tr class="border-b">
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Technologies</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Link</th>
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
    const table = $('#portfolioTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.portfolio.data') }}",
        columns: [
            { data: 'title', name: 'title' },
            { data: 'image', name: 'image', orderable: false, searchable: false },
            { data: 'description', name: 'description' },
            { data: 'technologies', name: 'technologies' },
            { data: 'link', name: 'link', orderable: false, searchable: false },
            { data: 'status', name: 'status', orderable: false, searchable: false },
            { data: 'sort_order', name: 'sort_order' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        "language": {
            "emptyTable": "No portfolio items found"
        }
    });
});
</script>
@endpush
