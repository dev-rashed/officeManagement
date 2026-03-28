<div
    id="global-popup"
    class="pointer-events-none fixed inset-0 z-[120] hidden items-center justify-center p-4 sm:p-6"
    aria-hidden="true"
>
    <div class="absolute inset-0 bg-black/55 backdrop-blur-sm transition-opacity duration-200" data-popup-overlay></div>

    <div
        class="relative w-full max-w-sm overflow-hidden rounded-[2rem] border border-white/10 bg-white/95 p-6 text-center text-zinc-900 shadow-[0_24px_80px_rgba(0,0,0,0.32)] transition duration-200 dark:border-white/8 dark:bg-zinc-900/95 dark:text-white sm:p-8"
        role="dialog"
        aria-modal="true"
        aria-labelledby="global-popup-title"
        aria-describedby="global-popup-message"
        tabindex="-1"
        data-popup-panel
    >
        <button
            type="button"
            class="absolute right-4 top-4 inline-flex h-9 w-9 items-center justify-center rounded-full bg-zinc-100 text-zinc-500 transition hover:bg-zinc-200 hover:text-zinc-800 dark:bg-white/8 dark:text-zinc-400 dark:hover:bg-white/14 dark:hover:text-white"
            aria-label="{{ __('Close popup') }}"
            data-popup-close
        >
            <span aria-hidden="true" class="text-lg leading-none">&times;</span>
        </button>

        <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-[1.75rem] bg-linear-to-br from-sky-50 via-white to-blue-100 shadow-inner shadow-white/80 ring-1 ring-black/5 dark:from-sky-500/15 dark:via-zinc-900 dark:to-blue-500/15 dark:ring-white/10" data-popup-icon-wrap>
            <img
                src="{{ asset('favicon.svg') }}"
                alt="{{ config('app.name', 'Application') }}"
                class="h-14 w-14 object-contain"
                data-popup-icon
            />
        </div>

        <h2 id="global-popup-title" class="mt-6 text-[1.75rem] font-semibold tracking-tight" data-popup-title>
            {{ __('Welcome to our website!') }}
        </h2>

        <p id="global-popup-message" class="mt-3 text-sm leading-6 text-zinc-600 dark:text-zinc-300" data-popup-message>
            {{ __('Have fun navigating through the demos.') }}
        </p>

        <div class="mt-7 flex items-center justify-center gap-3" data-popup-actions>
            <button
                type="button"
                class="hidden min-w-32 rounded-full bg-zinc-100 px-5 py-3 text-sm font-medium text-zinc-700 transition hover:bg-zinc-200 dark:bg-white/8 dark:text-zinc-200 dark:hover:bg-white/12"
                data-popup-secondary
            >
                {{ __('Cancel') }}
            </button>

            <button
                type="button"
                class="min-w-40 rounded-full bg-zinc-900 px-6 py-3 text-sm font-medium text-white transition hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
                data-popup-primary
            >
                {{ __('Close') }}
            </button>
        </div>
    </div>
</div>
