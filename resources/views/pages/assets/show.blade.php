@extends('layouts.app')

@section('title', 'Asset Details: '.$asset->name)

@section('content')
@php
    $attachmentUrl = $asset->attachment_path ? Storage::disk('public')->url($asset->attachment_path) : null;
    $attachmentExtension = $asset->attachment_path ? strtolower(pathinfo($asset->attachment_path, PATHINFO_EXTENSION)) : null;
    $isImageAttachment = in_array($attachmentExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
@endphp

<div class="mx-auto max-w-5xl">
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">{{ $asset->name }}</h1>
            <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">Asset Code: {{ $asset->code_tag_number }}</p>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row">
            <a href="{{ route('assets.edit', $asset->id) }}" class="admin-btn admin-btn-secondary">Edit</a>
            <form action="{{ route('assets.destroy', $asset->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this asset?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="admin-btn admin-btn-danger">Delete</button>
            </form>
        </div>
    </div>

    <div class="admin-detail-card">
        <div class="admin-detail-grid">
            <section>
                <h2>Basic Information</h2>
                <dl>
                    <div><dt>Category</dt><dd>{{ $asset->category }}</dd></div>
                    <div><dt>Brand</dt><dd>{{ $asset->brand ?? 'N/A' }}</dd></div>
                    <div><dt>Model</dt><dd>{{ $asset->model ?? 'N/A' }}</dd></div>
                    <div><dt>Serial Number</dt><dd>{{ $asset->serial_number ?? 'N/A' }}</dd></div>
                </dl>
            </section>

            <section>
                <h2>Purchase Information</h2>
                <dl>
                    <div><dt>Purchase Date</dt><dd>{{ $asset->purchase_date ? $asset->purchase_date->format('F j, Y') : 'N/A' }}</dd></div>
                    <div><dt>Purchase Cost</dt><dd>{{ $asset->purchase_cost ? '$'.number_format((float) $asset->purchase_cost, 2) : 'N/A' }}</dd></div>
                    <div><dt>Current Value</dt><dd>{{ $asset->current_value ? '$'.number_format((float) $asset->current_value, 2) : 'N/A' }}</dd></div>
                    <div><dt>Vendor/Supplier</dt><dd>{{ $asset->vendor_supplier ?? 'N/A' }}</dd></div>
                </dl>
            </section>

            <section>
                <h2>Assignment & Status</h2>
                <dl>
                    <div><dt>Location</dt><dd>{{ $asset->location ?? 'N/A' }}</dd></div>
                    <div><dt>Assigned To</dt><dd>{{ $asset->assignedUser ? $asset->assignedUser->name : 'Unassigned' }}</dd></div>
                    <div><dt>Condition</dt><dd><span class="badge bg-secondary">{{ ucfirst($asset->condition) }}</span></dd></div>
                    <div><dt>Status</dt><dd><span class="badge {{ $asset->status === 'active' ? 'bg-success' : ($asset->status === 'lost' ? 'bg-danger' : 'bg-secondary') }}">{{ ucfirst(str_replace('_', ' ', $asset->status)) }}</span></dd></div>
                </dl>
            </section>

            <section>
                <h2>Attachment</h2>
                @if($attachmentUrl)
                    <div class="admin-attachment">
                        @if($isImageAttachment)
                            <img src="{{ $attachmentUrl }}" alt="Asset attachment">
                        @else
                            <div>
                                <p>{{ pathinfo($asset->attachment_path, PATHINFO_FILENAME) }}</p>
                                <span>{{ strtoupper((string) $attachmentExtension) }} file</span>
                            </div>
                        @endif
                        <a href="{{ $attachmentUrl }}" target="_blank" class="admin-btn admin-btn-primary">Open Attachment</a>
                    </div>
                @else
                    <p class="admin-empty">No attachment uploaded.</p>
                @endif
            </section>

            <section class="md:col-span-2">
                <h2>Notes</h2>
                @if($asset->notes)
                    <p class="admin-note">{{ $asset->notes }}</p>
                @else
                    <p class="admin-empty">No notes available.</p>
                @endif
            </section>
        </div>
    </div>
</div>
@endsection
