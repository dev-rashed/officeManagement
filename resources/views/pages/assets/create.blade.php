@extends('layouts.app')

@section('title', 'Add New Asset')

@section('content')
<div class="max-w-4xl mx-auto p-4">
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b">
            <h2 class="text-xl font-semibold">Add New Asset</h2>
        </div>

        <form action="{{ route('assets.store') }}" method="POST" enctype="multipart/form-data" class="px-6 py-4">
            @csrf

            <div class="grid gap-6 md:grid-cols-2">

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Asset Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="code_tag_number" class="block text-sm font-medium text-gray-700 mb-1">Asset Code/Tag Number</label>
                    <input type="text" id="code_tag_number" name="code_tag_number" value="{{ old('code_tag_number') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    @error('code_tag_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <input type="text" id="category" name="category" value="{{ old('category') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    @error('category')
                        <p class="mt-1 text-sm text-red-600>{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="brand" class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                    <input type="text" id="brand" name="brand" value="{{ old('brand') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('brand')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="model" class="block text-sm font-medium text-gray-700 mb-1">Model</label>
                    <input type="text" id="model" name="model" value="{{ old('model') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('model')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="serial_number" class="block text-sm font-medium text-gray-700 mb-1">Serial Number</label>
                    <input type="text" id="serial_number" name="serial_number" value="{{ old('serial_number') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('serial_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="purchase_date" class="block text-sm font-medium text-gray-700 mb-1">Purchase Date</label>
                    <input type="date" id="purchase_date" name="purchase_date" value="{{ old('purchase_date') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('purchase_date')
                        <p class="mt-1 text-sm text-red-600>{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="purchase_cost" class="block text-sm font-medium text-gray-700 mb-1">Purchase Cost</label>
                    <input type="number" id="purchase_cost" name="purchase_cost" step="0.01" min="0" value="{{ old('purchase_cost') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('purchase_cost')
                        <p class="mt-1 text-sm text-red-600>{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="current_value" class="block text-sm font-medium text-gray-700 mb-1">Current Value</label>
                    <input type="number" id="current_value" name="current_value" step="0.01" min="0" value="{{ old('current_value') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('current_value')
                        <p class="mt-1 text-sm text-red-600>{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="vendor_supplier" class="block text-sm font-medium text-gray-700 mb-1">Vendor/Supplier</label>
                    <input type="text" id="vendor_supplier" name="vendor_supplier" value="{{ old('vendor_supplier') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('vendor_supplier')
                        <p class="mt-1 text-sm text-red-600>{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                    <input type="text" id="location" name="location" value="{{ old('location') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('location')
                        <p class="mt-1 text-sm text-red-600>{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="assigned_user_id" class="block text-sm font-medium text-gray-700 mb-1">Assigned To</label>
                    <select id="assigned_user_id" name="assigned_user_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Unassigned</option>
                        @foreach($users as $id => $name)
                            <option value="{{ $id }}" {{ old('assigned_user_id') === $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('assigned_user_id')
                        <p class="mt-1 text-sm text-red-600>{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="condition" class="block text-sm font-medium text-gray-700 mb-1">Condition</label>
                    <select id="condition" name="condition" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        <option value="excellent">Excellent</option>
                        <option value="good">Good</option>
                        <option value="fair">Fair</option>
                        <option value="poor">Poor</option>
                    </select>
                    @error('condition')
                        <p class="mt-1 text-sm text-red-600>{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        <option value="active">Active</option>
                        <option value="in_repair">In Repair</option>
                        <option value="retired">Retired</option>
                        <option value="lost">Lost</option>
                        <option value="disposed">Disposed</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600>{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Attachment/Image</label>
                    <div class="flex space-x-3">
                        <input type="file" id="attachment_path" name="attachment_path" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" class="w-0">
                        <label for="attachment_path" class="flex items-center justify-center px-4 py-2 border border-dotted border-gray-300 rounded-md text-gray-500 hover:border-indigo-500 hover:text-indigo-600 cursor-pointer">
                            Upload File
                        </label>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">Max size: 10MB (PDF, JPG, PNG, DOC, DOCX)</p>
                    @error('attachment_path')
                        <p class="mt-1 text-sm text-red-600>{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea id="notes" name="notes" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600>{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <div class="px-6 py-4 border-t">
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('assets.index') }}" class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300 text-sm font-medium">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium">
                        Create Asset
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection