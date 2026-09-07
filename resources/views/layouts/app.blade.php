<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main>
        {{ $slot ?? '' }}
        @yield('content')
    </flux:main>
    @stack('scripts')
</x-layouts::app.sidebar>
