@extends('layouts.app')

@section('title', 'Add New Asset')

@section('content')
<div class="mx-auto max-w-5xl">
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">Add Asset</h1>
            <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">Register a new office asset with assignment, purchase, and status details.</p>
        </div>
    </div>

    <div class="admin-form-card">
        <form action="{{ route('assets.store') }}" method="POST" enctype="multipart/form-data" class="admin-form">
            @csrf

            <div class="admin-form-grid">
                <div class="admin-field">
                    <label for="name">Asset Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name') <p class="admin-error">{{ $message }}</p> @enderror
                </div>

                <div class="admin-field">
                    <label for="code_tag_number">Asset Code/Tag Number</label>
                    <input type="text" id="code_tag_number" name="code_tag_number" value="{{ old('code_tag_number') }}" required>
                    @error('code_tag_number') <p class="admin-error">{{ $message }}</p> @enderror
                </div>

                <div class="admin-field">
                    <label for="category">Category</label>
                    <input type="text" id="category" name="category" value="{{ old('category') }}" required>
                    @error('category') <p class="admin-error">{{ $message }}</p> @enderror
                </div>

                <div class="admin-field">
                    <label for="brand">Brand</label>
                    <input type="text" id="brand" name="brand" value="{{ old('brand') }}">
                    @error('brand') <p class="admin-error">{{ $message }}</p> @enderror
                </div>

                <div class="admin-field">
                    <label for="model">Model</label>
                    <input type="text" id="model" name="model" value="{{ old('model') }}">
                    @error('model') <p class="admin-error">{{ $message }}</p> @enderror
                </div>

                <div class="admin-field">
                    <label for="serial_number">Serial Number</label>
                    <input type="text" id="serial_number" name="serial_number" value="{{ old('serial_number') }}">
                    @error('serial_number') <p class="admin-error">{{ $message }}</p> @enderror
                </div>

                <div class="admin-field">
                    <label for="purchase_date">Purchase Date</label>
                    <input type="date" id="purchase_date" name="purchase_date" value="{{ old('purchase_date') }}">
                    @error('purchase_date') <p class="admin-error">{{ $message }}</p> @enderror
                </div>

                <div class="admin-field">
                    <label for="purchase_cost">Purchase Cost</label>
                    <input type="number" id="purchase_cost" name="purchase_cost" step="0.01" min="0" value="{{ old('purchase_cost') }}">
                    @error('purchase_cost') <p class="admin-error">{{ $message }}</p> @enderror
                </div>

                <div class="admin-field">
                    <label for="current_value">Current Value</label>
                    <input type="number" id="current_value" name="current_value" step="0.01" min="0" value="{{ old('current_value') }}">
                    @error('current_value') <p class="admin-error">{{ $message }}</p> @enderror
                </div>

                <div class="admin-field">
                    <label for="vendor_supplier">Vendor/Supplier</label>
                    <input type="text" id="vendor_supplier" name="vendor_supplier" value="{{ old('vendor_supplier') }}">
                    @error('vendor_supplier') <p class="admin-error">{{ $message }}</p> @enderror
                </div>

                <div class="admin-field">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" value="{{ old('location') }}">
                    @error('location') <p class="admin-error">{{ $message }}</p> @enderror
                </div>

                <div class="admin-field">
                    <label for="assigned_user_id">Assigned To</label>
                    <select id="assigned_user_id" name="assigned_user_id">
                        <option value="">Unassigned</option>
                        @foreach($users as $id => $name)
                            <option value="{{ $id }}" @selected((string) old('assigned_user_id') === (string) $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('assigned_user_id') <p class="admin-error">{{ $message }}</p> @enderror
                </div>

                <div class="admin-field">
                    <label for="condition">Condition</label>
                    <select id="condition" name="condition" required>
                        @foreach(['excellent' => 'Excellent', 'good' => 'Good', 'fair' => 'Fair', 'poor' => 'Poor'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('condition', 'good') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('condition') <p class="admin-error">{{ $message }}</p> @enderror
                </div>

                <div class="admin-field">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        @foreach(['active' => 'Active', 'in_repair' => 'In Repair', 'retired' => 'Retired', 'lost' => 'Lost', 'disposed' => 'Disposed'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', 'active') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status') <p class="admin-error">{{ $message }}</p> @enderror
                </div>

                <div class="admin-field">
                    <label for="attachment_path">Attachment/Image</label>
                    <input type="file" id="attachment_path" name="attachment_path" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                    <p class="admin-help">Max size: 10MB. Supported: PDF, JPG, PNG, DOC, DOCX.</p>
                    @error('attachment_path') <p class="admin-error">{{ $message }}</p> @enderror
                </div>

                <div class="admin-field md:col-span-2">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="4">{{ old('notes') }}</textarea>
                    @error('notes') <p class="admin-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="admin-form-actions">
                <a href="{{ route('assets.index') }}" class="admin-btn admin-btn-secondary">Cancel</a>
                <button type="submit" class="admin-btn admin-btn-primary">Create Asset</button>
            </div>
        </form>
    </div>
</div>
@endsection
