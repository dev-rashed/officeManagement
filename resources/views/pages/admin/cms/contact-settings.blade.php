@extends('layouts.app')

@section('title', 'Contact Settings')

@section('content')
<div class="mx-auto max-w-4xl">
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">Contact Settings</h1>
            <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">Update the public website contact details and social links.</p>
        </div>
    </div>

    <div class="admin-form-card">
        <form action="{{ route('admin.contact-settings.update') }}" method="POST" class="space-y-5 p-6">
            @csrf
            @method('PUT')

            <div>
                <label for="email" class="block text-sm font-medium text-slate-700 dark:text-zinc-200">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $settings->email) }}" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label for="phone" class="block text-sm font-medium text-slate-700 dark:text-zinc-200">Phone</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone', $settings->phone) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                    @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="address" class="block text-sm font-medium text-slate-700 dark:text-zinc-200">Address</label>
                    <input id="address" name="address" type="text" value="{{ old('address', $settings->address) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                    @error('address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            @php($links = old('social_links', $settings->social_links ?? []))
            <div class="grid gap-5 md:grid-cols-2">
                @foreach(['facebook', 'linkedin', 'youtube', 'website'] as $network)
                    <div>
                        <label for="social_{{ $network }}" class="block text-sm font-medium text-slate-700 dark:text-zinc-200">{{ ucfirst($network) }}</label>
                        <input id="social_{{ $network }}" name="social_links[{{ $network }}]" type="url" value="{{ $links[$network] ?? '' }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                        @error("social_links.$network") <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-200 pt-5 dark:border-zinc-800">
                <button type="submit" class="rounded-xl bg-sky-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-sky-700">Save Settings</button>
            </div>
        </form>
    </div>
</div>
@endsection
