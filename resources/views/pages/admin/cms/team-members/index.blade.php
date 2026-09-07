@extends('layouts.app')

@section('title', 'Team Members Management')

@section('content')
<div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"><div><h1 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">Team Members</h1></div><a href="{{ route('admin.team-members.create') }}" class="inline-flex items-center justify-center rounded-xl bg-sky-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-sky-700">Add Team Member</a></div>

<div class="space-y-4">
    <div class="admin-table-shell">
        <div class="overflow-x-auto">
        <table class="admin-data-table" id="teamMembersTable">
            <thead>
                <tr class="border-b">
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bio</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
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

