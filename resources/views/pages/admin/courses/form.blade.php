@extends('layouts.app')

@section('title', ($course ? 'Edit' : 'Add New').' Course')

@section('content')
<div class="max-w-5xl mx-auto p-4">
    <div class="admin-form-card">
        <div class="px-6 py-4 border-b">
            <h2 class="text-xl font-semibold">
                {{ $course ? "Edit" : "Add New" }} Course
            </h2>
        </div>

        <form action="{{ $course ? route('admin.courses.update', $course->id) : route('admin.courses.store') }}"
              method="POST"
              enctype="multipart/form-data"
              class="px-6 py-4">
            @csrf
            @if($course)
                @method('PUT')
            @endif

            <div class="grid gap-6 md:grid-cols-2">

                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" id="title" name="title" value="{{ old('title', $course?->title ?? '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug (URL)</label>
                    <input type="text" id="slug" name="slug" value="{{ old('slug', $course?->slug ?? '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    @error('slug')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="short_description" class="block text-sm font-medium text-gray-700 mb-1">Short Description</label>
                    <textarea id="short_description" name="short_description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>{{ old('short_description', $course?->short_description ?? '') }}</textarea>
                    @error('short_description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="full_description" class="block text-sm font-medium text-gray-700 mb-1">Full Description</label>
                    <textarea id="full_description" name="full_description" rows="6" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('full_description', $course?->full_description ?? '') }}</textarea>
                    @error('full_description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <x-image-upload
                        name="featured_image"
                        :label="__('Featured Image')"
                        :value="$course?->featured_image"
                        hint="{{ __('Shown on the course card and detail page. Resized to 1600px and converted to WebP. Max 5 MB.') }}"
                    />
                    @error('featured_image')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select id="category_id" name="category_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">No Category</option>
                        @foreach($categories as $id => $name)
                            <option value="{{ $id }}" {{ old('category_id', $course?->category_id ?? '') === $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="duration" class="block text-sm font-medium text-gray-700 mb-1">Duration</label>
                    <input type="text" id="duration" name="duration" value="{{ old('duration', $course?->duration ?? '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('duration')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="class_type" class="block text-sm font-medium text-gray-700 mb-1">Class Type</label>
                    <select id="class_type" name="class_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        <option value="offline" {{ old('class_type', $course?->class_type ?? 'offline') === 'offline' ? 'selected' : '' }}>Offline</option>
                        <option value="online" {{ old('class_type', $course?->class_type ?? 'offline') === 'online' ? 'selected' : '' }}>Online</option>
                        <option value="hybrid" {{ old('class_type', $course?->class_type ?? 'offline') === 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                    </select>
                    @error('class_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                    <input type="text" id="location" name="location" value="{{ old('location', $course?->location ?? '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('location')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="schedule_batch" class="block text-sm font-medium text-gray-700 mb-1">Schedule/Batch Time</label>
                    <input type="text" id="schedule_batch" name="schedule_batch" value="{{ old('schedule_batch', $course?->schedule_batch ?? '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('schedule_batch')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="fee" class="block text-sm font-medium text-gray-700 mb-1">Fee/Price</label>
                    <input type="text" id="fee" name="fee" value="{{ old('fee', $course?->fee ?? '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('fee')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="instructor_name" class="block text-sm font-medium text-gray-700 mb-1">Instructor Name</label>
                    <input type="text" id="instructor_name" name="instructor_name" value="{{ old('instructor_name', $course?->instructor_name ?? '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('instructor_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="course_outline" class="block text-sm font-medium text-gray-700 mb-1">Course Outline</label>
                    <textarea id="course_outline" name="course_outline" rows="6" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('course_outline', $course?->course_outline ?? '') }}</textarea>
                    @error('course_outline')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                    <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $course?->sort_order ?? 0) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" min="0">
                    @error('sort_order')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center">
                            <input type="radio" id="status_draft" name="status" value="draft" {{ old('status', $course?->status ?? 'draft') === 'draft' ? 'checked' : '' }} class="h-4 w-4 text-gray-600 focus:ring-gray-500 border-gray-300">
                            <label for="status_draft" class="ml-2 text-sm font-medium text-gray-700">Draft</label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" id="status_published" name="status" value="published" {{ old('status', $course?->status ?? 'draft') === 'published' ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
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
                    <a href="{{ route('admin.courses.index') }}" class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300 text-sm font-medium">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium">
                        {{ $course ? 'Update Course' : 'Create Course' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
