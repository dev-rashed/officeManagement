@props([
    'sidebar' => false,
])

@php
    // Name and logo come from CMS -> SEO settings, cached, so the brand shows
    // whatever was uploaded there rather than the starter kit's placeholder.
    $branding = \App\Models\SeoSetting::branding();

    // An uploaded logo stands on its own -- the organisation name is long
    // enough to be cut off next to it, and the logo already carries it.
    // Without one, the name is all there is to show.
    $name = $branding['logo'] ? '' : $branding['name'];

    // A wordmark needs room to breathe; the placeholder mark is a square tile.
    $slotClass = $branding['logo']
        ? 'flex h-9 w-auto max-w-44 items-center justify-start overflow-hidden'
        : 'flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground';
@endphp

@if($sidebar)
    <flux:sidebar.brand :name="$name" {{ $attributes }}>
        <x-slot name="logo" :class="$slotClass">
            @if ($branding['logo'])
                <img src="{{ $branding['logo'] }}" alt="{{ $branding['name'] }}" class="h-full w-auto max-w-full object-contain object-left">
            @else
                <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
            @endif
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand :name="$name" {{ $attributes }}>
        <x-slot name="logo" :class="$slotClass">
            @if ($branding['logo'])
                <img src="{{ $branding['logo'] }}" alt="{{ $branding['name'] }}" class="h-full w-auto max-w-full object-contain object-left">
            @else
                <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
            @endif
        </x-slot>
    </flux:brand>
@endif
