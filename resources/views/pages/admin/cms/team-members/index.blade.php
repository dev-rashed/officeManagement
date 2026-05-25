@extends('layouts.app')

@section('title', 'Team Members Management')

@section('content')
<x-flux::page-header title="Team Members">
    <x-flux::toolbar>
        <x-flux::button href="{{ route('admin.team-members.create') }}" variant="primary">
            Add Team Member
        </x-flux::button>
    </x-flux::toolbar>
</x-flux::page-header>

<div class="space-y-4">
    <div class="p-4 bg-white rounded-lg shadow">
        <table class="w-full" id="teamMembersTable">
            <thead>
                <tr class="border-b">
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bio</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
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
    const table = $('#teamMembersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.team-members.data') }}",
        columns: [
            { data: 'name', name: 'name' },
            { data: 'position', name: 'position' },
            { data: 'image', name: 'image', orderable: false, searchable: false },
            { data: 'bio', name: 'bio' },
            { data: 'email', name: 'email' },
            { data: 'phone', name: 'phone' },
            { data: 'status', name: 'status', orderable: false, searchable: false },
            { data: 'sort_order', name: 'sort_order' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        "language": {
            "emptyTable": "No team members found"
        }
    });
});
</script>
@endpush
