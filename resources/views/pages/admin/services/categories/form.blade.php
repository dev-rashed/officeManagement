@extends('layouts.app')

@section('title', ($category ? 'Edit' : 'Add New').' Service Category')

@section('content')
<div class="max-w-lg mx-auto p-4">
    <div class="admin-form-card">
        <div class="px-6 py-4 border-b">
            <h2 class="text-xl font-semibold">
                {{ $category ? "Edit" : "Add New" }} Service Category
            </h2>
        </div>

        <form action="{{ $category ? route('admin.service-categories.update', $category->id) : route('admin.service-categories.store') }}"
              method="POST"
              class="px-6 py-4">
            @csrf
            @if($category)
                @method('PUT')
            @endif

            <div class="space-y-4">

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $category?->name ?? '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug (URL)</label>
                    <input type="text" id="slug" name="slug" value="{{ old('slug', $category?->slug ?? '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    @error('slug')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="description" name="description" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description', $category?->description ?? '') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center">
                            <input type="radio" id="status_draft" name="status" value="draft" {{ old('status', $category?->status ?? 'draft') === 'draft' ? 'checked' : '' }} class="h-4 w-4 text-gray-600 focus:ring-gray-500 border-gray-300">
                            <label for="status_draft" class="ml-2 text-sm font-medium text-gray-700">Draft</label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" id="status_published" name="status" value="published" {{ old('status', $category?->status ?? 'draft') === 'published' ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                            <label for="status_published" class="ml-2 text-sm font-medium text-gray-700">Published</label>
                        </div>
                    </div>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <div class="px-6 py-4 border-t">
                <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                    <a href="{{ route('admin.service-categories.index') }}" class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300 text-sm font-medium">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium">
                        {{ $category ? 'Update Category' : 'Create Category' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
