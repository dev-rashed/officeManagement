@extends('layouts.app')

@section('title', '{{ $member ? "Edit" : "Add New" }} Team Member')

@section('content')
<div class="max-w-3xl mx-auto p-4">
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b">
            <h2 class="text-xl font-semibold">
                {{ $member ? "Edit" : "Add New" }} Team Member
            </h2>
        </div>

        <form action="{{ $member ? route('admin.team-members.update', $member->id) : route('admin.team-members.store') }}"
              method="POST"
              enctype="multipart/form-data"
              class="px-6 py-4">
            @csrf
            @if($member)
                @method('PUT')
            @endif

            <div class="grid gap-6 md:grid-cols-2">

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $member?->name ?? '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="position" class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                    <input type="text" id="position" name="position" value="{{ old('position', $member?->position ?? '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    @error('position')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="bio" class="block text-sm font-medium text-gray-700 mb-1">Bio</label>
                    <textarea id="bio" name="bio" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('bio', $member?->bio ?? '') }}</textarea>
                    @error('bio')
                        <p class="mt-1 text-sm text-red-600>{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Photo</label>
                    <div class="flex space-x-3">
                        <input type="file" id="image_path" name="image_path" accept="image/*" class="w-0">
                        <label for="image_path" class="flex items-center justify-center px-4 py-2 border border-dotted border-gray-300 rounded-md text-gray-500 hover:border-indigo-500 hover:text-indigo-600 cursor-pointer">
                            @if(old('image_path') || ($member && $member->image_path))
                                <img src="{{ old('image_path') ?? Storage::disk('public')->url($member->image_path) }}" alt="Preview" class="w-16 h-16 object-cover rounded">
                            @else
                                Upload Photo
                            @endif
                        </label>
                    </div>
                    @if(old('image_path') || ($member && $member->image_path))
                        <p class="mt-2 text-xs text-gray-500">Max size: 5MB</p>
                    @endif
                    @error('image_path')
                        <p class="mt-1 text-sm text-red-600>{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $member?->email ?? '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone', $member?->phone ?? '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600>{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Social Links</label>
                    <div id="social-links-container">
                        @if(old('social_links') && is_array(old('social_links')))
                            @foreach(old('social_links') as $index => $link)
                                <div class="flex items-center gap-2 social-link-item">
                                    <input type="url" name="social_links[]" value="{{ $link }}" class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="https://example.com">
                                    <button type="button" class="remove-social-link px-2 py-2 bg-red-100 text-red-500 rounded hover:bg-red-200">×</button>
                                </div>
                            @endforeach
                        @else
                            <div class="flex items-center gap-2 social-link-item">
                                <input type="url" name="social_links[]" value="" class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="https://example.com">
                                <button type="button" class="remove-social-link px-2 py-2 bg-red-100 text-red-500 rounded hover:bg-red-200">×</button>
                            </div>
                        @endif
                    </div>
                    <button type="button" id="add-social-link" class="mt-2 px-4 py-2 bg-indigo-100 text-indigo-800 rounded hover:bg-indigo-200">
                        + Add Social Link
                    </div>
                    @error('social_links')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center">
                            <input type="radio" id="status_active" name="status" value="active" {{ old('status', $member?->status ?? 'active') === 'active' ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                            <label for="status_active" class="ml-2 text-sm font-medium text-gray-700">Active</label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" id="status_inactive" name="status" value="inactive" {{ old('status', $member?->status ?? 'active') === 'inactive' ? 'checked' : '' }} class="h-4 w-4 text-gray-600 focus:ring-gray-500 border-gray-300">
                            <label for="status_inactive" class="ml-2 text-sm font-medium text-gray-700">Inactive</label>
                        </div>
                    </div>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600>{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                    <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $member?->sort_order ?? 0) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" min="0">
                    @error('sort_order')
                        <p class="mt-1 text-sm text-red-600>{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <div class="px-6 py-4 border-t">
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.team-members.index') }}" class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300 text-sm font-medium">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium">
                        {{ $member ? 'Update Member' : 'Create Member' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Social links dynamic add/remove
    const container = document.getElementById('social-links-container');
    const addButton = document.getElementById('add-social-link');

    if (addButton && container) {
        addButton.addEventListener('click', function() {
            const item = document.createElement('div');
            item.className = 'flex items-center gap-2 social-link-item';
            item.innerHTML = `
                <input type="url" name="social_links[]" value="" class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="https://example.com">
                <button type="button" class="remove-social-link px-2 py-2 bg-red-100 text-red-500 rounded hover:bg-red-200">×</button>
            `;
            container.appendChild(item);

            // Add remove event listener to the new button
            const removeBtn = item.querySelector('.remove-social-link');
            removeBtn.addEventListener('click', function() {
                item.remove();
            });
        });
    }

    // Handle removal of social links
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-social-link')) {
            e.target.closest('.social-link-item').remove();
        }
    });
});
</script>
@endpush
