@extends('layouts.app')

@section('title', '{{ $service ? "Edit" : "Add New" }} Service')

@section('content')
<div class="max-w-4xl mx-auto p-4">
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b">
            <h2 class="text-xl font-semibold">
                {{ $service ? "Edit" : "Add New" }} Service
            </h2>
        </div>

        <form action="{{ $service ? route('admin.services.update', $service->id) : route('admin.services.store') }}"
              method="POST"
              enctype="multipart/form-data"
              class="px-6 py-4">
            @csrf
            @if($service)
                @method('PUT')
            @endif

            <div class="grid gap-6 md:grid-cols-2">

                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" id="title" name="title" value="{{ old('title', $service?->title ?? '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug (URL)</label>
                    <input type="text" id="slug" name="slug" value="{{ old('slug', $service?->slug ?? '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    @error('slug')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="short_description" class="block text-sm font-medium text-gray-700 mb-1">Short Description</label>
                    <textarea id="short_description" name="short_description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>{{ old('short_description', $service?->short_description ?? '') }}</textarea>
                    @error('short_description')
                        <p class="mt-1 text-sm text-red-600>{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="full_description" class="block text-sm font-medium text-gray-700 mb-1">Full Description</label>
                    <textarea id="full_description" name="full_description" rows="6" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('full_description', $service?->full_description ?? '') }}</textarea>
                    @error('full_description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="featured_image" class="block text-sm font-medium text-gray-700 mb-1">Featured Image</label>
                    <div class="flex space-x-3">
                        <input type="file" id="featured_image" name="featured_image" accept="image/*" class="w-0">
                        <label for="featured_image" class="flex items-center justify-center px-4 py-2 border border-dotted border-gray-300 rounded-md text-gray-500 hover:border-indigo-500 hover:text-indigo-600 cursor-pointer">
                            @if(old('featured_image') || ($service && $service->featured_image))
                                <img src="{{ old('featured_image') ?? Storage::disk('public')->url($service->featured_image) }}" alt="Preview" class="w-16 h-16 object-cover rounded">
                            @else
                                Upload Image
                            @endif
                        </label>
                    </div>
                    @if(old('featured_image') || ($service && $service->featured_image))
                        <p class="mt-2 text-xs text-gray-500">Max size: 5MB</p>
                    @endif
                    @error('featured_image')
                        <p class="mt-1 text-sm text-red-600>{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="service_category_id" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select id="service_category_id" name="service_category_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">No Category</option>
                        @foreach($categories as $id => $name)
                            <option value="{{ $id }}" {{ old('service_category_id', $service?->service_category_id ?? '') === $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('service_category_id')
                        <p class="mt-1 text-sm text-red-600>{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center">
                            <input type="radio" id="status_draft" name="status" value="draft" {{ old('status', $service?->status ?? 'draft') === 'draft' ? 'checked' : '' }} class="h-4 w-4 text-gray-600 focus:ring-gray-500 border-gray-300">
                            <label for="status_draft" class="ml-2 text-sm font-medium text-gray-700">Draft</label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" id="status_published" name="status" value="published" {{ old('status', $service?->status ?? 'draft') === 'published' ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                            <label for="status_published" class="ml-2 text-sm font-medium text-gray-700">Published</label>
                        </div>
                    </div>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600>{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="show_on_homepage" class="block text-sm font-medium text-gray-700 mb-1">Show on Homepage</label>
                    <div class="flex items-center">
                        <input type="checkbox" id="show_on_homepage" name="show_on_homepage" value="1" {{ old('show_on_homepage', $service?->show_on_homepage ?? false) ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                        <span class="ml-2 text-sm font-medium text-gray-700">Display on homepage</span>
                    </div>
                    @error('show_on_homepage')
                        <p class="mt-1 text-sm text-red-600>{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                    <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $service?->sort_order ?? 0) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" min="0">
                    @error('sort_order')
                        <p class="mt-1 text-sm text-red-600>{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <div class="px-6 py-4 border-t">
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.services.index') }}" class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300 text-sm font-medium">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium">
                        {{ $service ? 'Update Service' : 'Create Service' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
