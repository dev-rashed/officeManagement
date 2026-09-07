@extends('layouts.app')

@section('title', ($section ? 'Edit' : 'Add New').' Page Section')

@section('content')
<div class="max-w-4xl mx-auto p-4">
    <div class="admin-form-card">
        <div class="px-6 py-4 border-b">
            <h2 class="text-xl font-semibold">
                {{ $section ? "Edit" : "Add New" }} Page Section
            </h2>
        </div>

        <form action="{{ $section ? route('admin.page-sections.update', $section->id) : route('admin.page-sections.store') }}"
              method="POST"
              enctype="multipart/form-data"
              class="px-6 py-4">
            @csrf
            @if($section)
                @method('PUT')
            @endif

            <div class="grid gap-6 md:grid-cols-2">

                <div>
                    <label for="page_slug" class="block text-sm font-medium text-gray-700 mb-1">Page</label>
                    <select id="page_slug" name="page_slug" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        <option value="">Select Page</option>
                        <option value="home" {{ old('page_slug', $section?->page_slug ?? '') === 'home' ? 'selected' : '' }}>Home</option>
                        <option value="about" {{ old('page_slug', $section?->page_slug ?? '') === 'about' ? 'selected' : '' }}>About</option>
                        <option value="mission" {{ old('page_slug', $section?->page_slug ?? '') === 'mission' ? 'selected' : '' }}>Mission</option>
                        <option value="vision" {{ old('page_slug', $section?->page_slug ?? '') === 'vision' ? 'selected' : '' }}>Vision</option>
                        <option value="team" {{ old('page_slug', $section?->page_slug ?? '') === 'team' ? 'selected' : '' }}>Team</option>
                        <option value="contact" {{ old('page_slug', $section?->page_slug ?? '') === 'contact' ? 'selected' : '' }}>Contact</option>
                        <option value="portfolio" {{ old('page_slug', $section?->page_slug ?? '') === 'portfolio' ? 'selected' : '' }}>Portfolio</option>
                        <option value="work" {{ old('page_slug', $section?->page_slug ?? '') === 'work' ? 'selected' : '' }}>Work (Legacy)</option>
                    </select>
                    @error('page_slug')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="section_key" class="block text-sm font-medium text-gray-700 mb-1">Section Key</label>
                    <input type="text" id="section_key" name="section_key" value="{{ old('section_key', $section?->section_key ?? '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="e.g., hero, services, testimonials" required>
                    @error('section_key')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" id="title" name="title" value="{{ old('title', $section?->title ?? '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="subtitle" class="block text-sm font-medium text-gray-700 mb-1">Subtitle</label>
                    <input type="text" id="subtitle" name="subtitle" value="{{ old('subtitle', $section?->subtitle ?? '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('subtitle')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="description" name="description" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description', $section?->description ?? '') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Image</label>
                    <div class="flex space-x-3">
                        <input type="file" id="image_path" name="image_path" accept="image/*" class="w-0">
                        <label for="image_path" class="flex items-center justify-center px-4 py-2 border border-dotted border-gray-300 rounded-md text-gray-500 hover:border-indigo-500 hover:text-indigo-600 cursor-pointer">
                            @if(old('image_path') || ($section && $section->image_path))
                                <img src="{{ old('image_path') ?? Storage::disk('public')->url($section->image_path) }}" alt="Preview" class="w-16 h-16 object-cover rounded">
                            @else
                                Upload Image
                            @endif
                        </label>
                    </div>
                    @if(old('image_path') || ($section && $section->image_path))
                        <p class="mt-2 text-xs text-gray-500">Max size: 5MB</p>
                    @endif
                    @error('image_path')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="button_text" class="block text-sm font-medium text-gray-700 mb-1">Button Text</label>
                    <input type="text" id="button_text" name="button_text" value="{{ old('button_text', $section?->button_text ?? '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('button_text')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="button_link" class="block text-sm font-medium text-gray-700 mb-1">Button Link</label>
                    <input type="url" id="button_link" name="button_link" value="{{ old('button_link', $section?->button_link ?? '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="https://example.com">
                    @error('button_link')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="visibility" class="block text-sm font-medium text-gray-700 mb-1">Visibility</label>
                    <div class="flex items-center">
                        <input type="hidden" name="visibility" value="0">
                        <input type="checkbox" id="visibility" name="visibility" value="1" {{ old('visibility', $section?->visibility ?? 0) ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <span class="ml-2 text-sm font-medium text-gray-700">Visible on page</span>
                    </div>
                    @error('visibility')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                    <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $section?->sort_order ?? 0) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" min="0">
                    @error('sort_order')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <div class="px-6 py-4 border-t">
                <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                    <a href="{{ route('admin.page-sections.index') }}" class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300 text-sm font-medium">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium">
                        {{ $section ? 'Update Section' : 'Create Section' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
