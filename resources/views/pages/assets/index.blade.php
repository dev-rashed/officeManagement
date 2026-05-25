@extends('layouts.app')

@section('title', 'Assets Management')

@section('content')
<x-flux::page-header title="Assets">
    <x-flux::toolbar>
        <x-flux::button href="{{ route('assets.create') }}" variant="primary">
            Add Asset
        </x-flux::button>
    </x-flux::toolbar>
</x-flux::page-header>

<div class="space-y-4">
    <div class="p-4 bg-white rounded-lg shadow">
        <div class="mb-4">
            <form id="assetFilterForm" class="space-y-4">
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-6">
                    <div>
                        <label for="filterCategory" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select id="filterCategory" name="category" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}">{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="filterStatus" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="filterStatus" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">All Status</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="filterLocation" class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                        <select id="filterLocation" name="location" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">All Locations</option>
                            @foreach($locations as $location)
                                <option value="{{ $location }}">{{ $location }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="filterAssignedUser" class="block text-sm font-medium text-gray-700 mb-1">Assigned To</label>
                        <select id="filterAssignedUser" name="assigned_user_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">All Users</option>
                            @foreach($users as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2 lg:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Date Range</label>
                        <div class="grid gap-2 grid-cols-2">
                            <div>
                                <input type="date" id="filterPurchaseDateFrom" name="purchase_date_from" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <input type="date" id="filterPurchaseDateTo" name="purchase_date_to" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-2 lg:col-span-3">
                        <div class="flex items-end space-x-3">
                            <button type="button" id="applyFilters" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                Apply Filters
                            </button>
                            <button type="button" id="resetFilters" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                                Reset
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="p-4 bg-white rounded-lg shadow">
            <table class="w-full" id="assetsTable">
                <thead>
                    <tr class="border-b">
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Brand</th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Model</th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Serial</th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purchase Date</th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Value</th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Condition</th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attachment</th>
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
    // Initialize DataTable
    let table = $('#assetsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('assets.data') }}",
            type: 'POST',
            data: function (d) {
                // Add filter data
                const form = $('#assetFilterForm');
                if (form.length) {
                    const formData = form.serializeArray();
                    formData.forEach(function (item) {
                        d[item.name] = item.value;
                    });
                }
                return d;
            }
        },
        columns: [
            { data: 'name', name: 'name' },
            { data: 'code', name: 'code_tag_number' },
            { data: 'category', name: 'category' },
            { data: 'brand', name: 'brand' },
            { data: 'model', name: 'model' },
            { data: 'serial', name: 'serial_number' },
            { data: 'purchase_date', name: 'purchase_date' },
            { data: 'purchase_cost', name: 'purchase_cost' },
            { data: 'current_value', name: 'current_value' },
            { data: 'location', name: 'location' },
            { data: 'assigned_to', name: 'assigned_user_id' },
            { data: 'condition', name: 'condition' },
            { data: 'status', name: 'status', orderable: false, searchable: false },
            { data: 'attachment', name: 'attachment_path', orderable: false, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        "language": {
            "emptyTable": "No assets found"
        }
    });

    // Filter functionality
    const applyFiltersBtn = document.getElementById('applyFilters');
    const resetFiltersBtn = document.getElementById('resetFilters');

    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', function() {
            table.draw();
        });
    }

    if (resetFiltersBtn) {
        resetFiltersBtn.addEventListener('click', function() {
            $('#assetFilterForm')[0].reset();
            table.draw();
        });
    }

    // Auto-apply filters on change (optional)
    $('#assetFilterForm select, #assetFilterForm input[type="date"]').on('change', function() {
        table.draw();
    });
});
</script>
@endpush
