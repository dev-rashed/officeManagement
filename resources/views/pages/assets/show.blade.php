@extends('layouts.app')

@section('title', 'Asset Details: {{ $asset->name }}')

@section('content')
<div class="max-w-3xl mx-auto p-4">
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-bold">{{ $asset->name }}</h1>
                    <p class="text-sm text-gray-500">Asset Code: {{ $asset->code_tag_number }}</p>
                </div>
                <div class="space-x-3">
                    <a href="{{ route('assets.edit', $asset->id) }}" class="btn btn-outline-primary btn-sm">
                        Edit
                    </a>
                    <form action="{{ route('assets.destroy', $asset->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this asset?');" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="px-6 py-4 border-t">
            <div class="grid gap-6 md:grid-cols-2">

                <div class="space-y-4">
                    <div class="border-b pb-2">
                        <p class="text-sm font-medium text-gray-500">Basic Information</p>
                    </div>
                    <div class="space-y-2">
                        <p><span class="font-medium">Category:</span> {{ $asset->category }}</p>
                        <p><span class="font-medium">Brand:</span> {{ $asset->brand ?? 'N/A' }}</p>
                        <p><span class="font-medium">Model:</span> {{ $asset->model ?? 'N/A' }}</p>
                        <p><span class="font-medium">Serial Number:</span> {{ $asset->serial_number ?? 'N/A' }}</p>
                    </div>

                    <div class="border-b pt-4 pb-2">
                        <p class="text-sm font-medium text-gray-500">Financial & Purchase Info</p>
                    </div>
                    <div class="space-y-2">
                        <p><span class="font-medium">Purchase Date:</span> {{ $asset->purchase_date ? $asset->purchase_date->format('F j, Y') : 'N/A' }}</p>
                        <p><span class="font-medium">Purchase Cost:</span> {{ $asset->purchase_cost ? '$' . number_format($asset->purchase_cost, 2) : 'N/A' }}</p>
                        <p><span class="font-medium">Current Value:</span> {{ $asset->current_value ? '$' . number_format($asset->current_value, 2) : 'N/A' }}</p>
                        <p><span class="font-medium">Vendor/Supplier:</span> {{ $asset->vendor_supplier ?? 'N/A' }}</p>
                    </div>

                    <div class="border-b pt-4 pb-2">
                        <p class="text-sm font-medium text-gray-500">Assignment & Location</p>
                    </div>
                    <div class="space-y-2">
                        <p><span class="font-medium">Location:</span> {{ $asset->location ?? 'N/A' }}</p>
                        <p><span class="font-medium">Assigned To:</span> {{ $asset->assignedUser ? $asset->assignedUser->name : 'Unassigned' }}</p>
                    </div>

                    <div class="border-b pt-4 pb-2">
                        <p class="text-sm font-medium text-gray-500">Status & Condition</p>
                    </div>
                    <div class="space-y-2">
                        <p><span class="font-medium">Condition:</span>
                            <span class="px-2 py-1 rounded
                                {{ $asset->condition === 'excellent' ? 'bg-green-100 text-green-800' :
                                  $asset->condition === 'good' ? 'bg-blue-100 text-blue-800' :
                                  $asset->condition === 'fair' ? 'bg-yellow-100 text-yellow-800' :
                                  'bg-red-100 text-red-800' }}">
                                {{ ucfirst($asset->condition) }}
                            </span>
                        </p>
                        <p><span class="font-medium">Status:</span>
                            <span class="px-2 py-1 rounded
                                {{ $asset->status === 'active' ? 'bg-green-100 text-green-800' :
                                  $asset->status === 'in_repair' ? 'bg-yellow-100 text-yellow-800' :
                                  $asset->status === 'retired' ? 'bg-gray-100 text-gray-800' :
                                  $asset->status === 'lost' ? 'bg-red-100 text-red-800' :
                                  'bg-purple-100 text-purple-800' }}">
                                {{ ucfirst(str_replace('_', ' ', $asset->status)) }}
                            </span>
                        </p>
                    </div>
                </div>

                <div class="space-y-4">
                    @if($asset->attachment_path)
                    <div class="border-b pb-2">
                        <p class="text-sm font-medium text-gray-500">Attachment</p>
                    </div>
                    <div class="space-y-2">
                        <div class="border rounded p-4">
                            @if(str_ends_with(strtolower($asset->attachment_path), ['.jpg', '.jpeg', '.png', '.gif']))
                            <img src="{{ Storage::disk('public')->url($asset->attachment_path) }}" alt="Asset attachment" class="max-w-full h-auto rounded">
                            @else
                            <div class="text-center py-8">
                                <div class="text-2xl mb-2">📎</div>
                                <p class="font-medium">{{ pathinfo($asset->attachment_path, PATHINFO_FILENAME) }}</p>
                                <p class="text-sm text-gray-500">{{ pathinfo($asset->attachment_path, PATHINFO_EXTENSION) | upper }} File</p>
                                <a href="{{ Storage::disk('public')->url($asset->attachment_path) }}" target="_blank" class="mt-2 inline-block px-3 py-1 bg-indigo-600 text-white rounded text-sm">Download</a>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <div class="border-b pt-4 pb-2">
                        <p class="text-sm font-medium text-gray-500">Notes</p>
                    </div>
                    <div class="space-y-2">
                        @if($asset->notes)
                        <div class="whitespace-pre-wrap bg-gray-50 p-4 rounded">
                            {{ $asset->notes }}
                        </div>
                        @else
                        <p class="text-sm text-gray-500 italic">No notes available.</p>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection