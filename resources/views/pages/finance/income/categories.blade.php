<x-layouts::app :title="__('Income Categories')">
    <div class="space-y-6">
        <div class="rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-semibold tracking-tight text-zinc-900 dark:text-white">{{ __('Income Categories') }}</h1>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Manage reusable income categories and their current status.') }}</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <div class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 dark:border-emerald-900/70 dark:bg-emerald-950/40 dark:text-emerald-300">
                        {{ trans_choice(':count category|:count categories', $categories->total(), ['count' => $categories->total()]) }}
                    </div>

                    @if ($canManageCategories)
                        <button
                            type="button"
                            class="inline-flex items-center justify-center rounded-lg bg-sky-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-sky-700"
                            data-category-create
                        >
                            {{ __('Add Category') }}
                        </button>
                    @endif
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
                {{ session('success') }}
            </div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse text-left text-sm" style="width:100%">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">{{ __('Category') }}</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">{{ __('Entries') }}</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">{{ __('Total Amount') }}</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">{{ __('Latest Entry') }}</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $category)
                            <tr class="bg-transparent transition-colors duration-200 hover:bg-zinc-800/35">
                                <td class="px-4 py-3 text-sm font-medium text-zinc-200">{{ $category->name }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="inline-flex rounded-full border px-2.5 py-1 text-[11px] font-medium {{ $category->isActive() ? 'border-emerald-900/70 bg-emerald-950/40 text-emerald-300' : 'border-amber-900/70 bg-amber-950/40 text-amber-300' }}">
                                        {{ ucfirst($category->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-zinc-300">{{ number_format($category->entries_count) }}</td>
                                <td class="px-4 py-3 text-sm text-zinc-200">{{ number_format((float) $category->total_amount, 2) }}</td>
                                <td class="px-4 py-3 text-sm text-zinc-300">
                                    {{ $category->latest_date ? \Illuminate\Support\Carbon::parse($category->latest_date)->format('Y-m-d') : __('N/A') }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if ($canManageCategories)
                                        <div class="inline-flex overflow-hidden rounded-lg border border-zinc-700 bg-zinc-950/60 text-xs font-medium text-zinc-200">
                                            <button
                                                type="button"
                                                class="px-3 py-1 transition hover:bg-zinc-800"
                                                data-category-view
                                                data-category-id="{{ $category->id }}"
                                                data-category-name="{{ $category->name }}"
                                                data-category-status="{{ $category->status }}"
                                            >
                                                {{ __('View') }}
                                            </button>
                                            <button
                                                type="button"
                                                class="border-x border-zinc-700 px-3 py-1 transition hover:bg-zinc-800"
                                                data-category-edit
                                                data-category-id="{{ $category->id }}"
                                                data-category-name="{{ $category->name }}"
                                                data-category-status="{{ $category->status }}"
                                            >
                                                {{ __('Edit') }}
                                            </button>
                                            <form method="POST" action="{{ route('income.categories.destroy', $category) }}" onsubmit="return confirm('{{ __('Delete this category?') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-3 py-1 text-rose-300 transition hover:bg-zinc-800">
                                                    {{ __('Trash') }}
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-xs text-zinc-500">{{ __('View only') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">{{ __('No income categories found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            {{ $categories->links() }}
        </div>

        @if ($canManageCategories)
            <div
                class="fixed inset-0 z-[110] hidden items-center justify-center p-4"
                data-category-modal
                aria-hidden="true"
            >
                <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" data-category-modal-overlay></div>

                <div class="relative w-full max-w-lg rounded-2xl border border-zinc-800 bg-zinc-900 p-6 shadow-2xl">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-white" data-category-modal-title>{{ __('Add Income Category') }}</h2>
                            <p class="mt-1 text-sm text-zinc-400" data-category-modal-description>{{ __('Create a category and choose whether it is active right away.') }}</p>
                        </div>

                        <button type="button" class="rounded-full bg-zinc-800 p-2 text-zinc-400 transition hover:bg-zinc-700 hover:text-white" data-category-modal-close>
                            <span class="sr-only">{{ __('Close') }}</span>
                            &times;
                        </button>
                    </div>

                    <form method="POST" action="{{ route('income.categories.store') }}" class="mt-6 space-y-5" data-category-form>
                        @csrf
                        <input type="hidden" name="_method" value="POST" data-category-method>
                        <input type="hidden" name="category_id" value="{{ old('category_id') }}" data-category-id>

                        <div>
                            <label for="category_name" class="text-sm font-medium text-zinc-200">{{ __('Category Name') }}</label>
                            <input
                                id="category_name"
                                name="name"
                                type="text"
                                value="{{ old('name') }}"
                                class="mt-2 w-full rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-2.5 text-sm text-white outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30"
                                placeholder="{{ __('e.g. Donations') }}"
                                required
                                data-category-name
                            >
                            @error('name')
                                <p class="mt-2 text-sm text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="category_status" class="text-sm font-medium text-zinc-200">{{ __('Status') }}</label>
                            <select
                                id="category_status"
                                name="status"
                                class="mt-2 w-full rounded-xl border border-zinc-700 bg-zinc-950 px-3 py-2.5 text-sm text-white outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30"
                                required
                                data-category-status
                            >
                                <option value="active" @selected(old('status', 'active') === 'active')>{{ __('Active') }}</option>
                                <option value="inactive" @selected(old('status') === 'inactive')>{{ __('Inactive') }}</option>
                            </select>
                            @error('status')
                                <p class="mt-2 text-sm text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-2">
                            <button type="button" class="rounded-xl border border-zinc-700 bg-zinc-900 px-4 py-2 text-sm font-medium text-zinc-300 transition hover:bg-zinc-800 hover:text-white" data-category-modal-close>
                                {{ __('Cancel') }}
                            </button>
                            <button type="submit" class="rounded-xl bg-sky-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-sky-700" data-category-submit>
                                {{ __('Save Category') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <style>
            table thead th {
                background: rgba(9, 9, 11, 0.82);
                border-bottom: 1px solid rgba(39, 39, 42, 1);
            }

            table tbody td {
                border-top: 1px solid rgba(39, 39, 42, 0.9);
            }
        </style>

        @if ($canManageCategories)
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const modal = document.querySelector('[data-category-modal]');

                    if (!modal) {
                        return;
                    }

                    const form = modal.querySelector('[data-category-form]');
                    const methodInput = modal.querySelector('[data-category-method]');
                    const idInput = modal.querySelector('[data-category-id]');
                    const nameInput = modal.querySelector('[data-category-name]');
                    const statusInput = modal.querySelector('[data-category-status]');
                    const title = modal.querySelector('[data-category-modal-title]');
                    const description = modal.querySelector('[data-category-modal-description]');
                    const submit = modal.querySelector('[data-category-submit]');

                    const openModal = () => {
                        modal.classList.remove('hidden');
                        modal.classList.add('flex');
                        modal.setAttribute('aria-hidden', 'false');
                    };

                    const closeModal = () => {
                        modal.classList.add('hidden');
                        modal.classList.remove('flex');
                        modal.setAttribute('aria-hidden', 'true');
                    };

                    const setCreateMode = () => {
                        form.action = @js(route('income.categories.store'));
                        methodInput.value = 'POST';
                        idInput.value = '';
                        nameInput.value = '';
                        statusInput.value = 'active';
                        title.textContent = 'Add Income Category';
                        description.textContent = 'Create a category and choose whether it is active right away.';
                        submit.textContent = 'Save Category';
                    };

                    const setEditMode = (button) => {
                        const id = button.dataset.categoryId;
                        const name = button.dataset.categoryName;
                        const status = button.dataset.categoryStatus;

                        form.action = `/income/categories/${id}`;
                        methodInput.value = 'PUT';
                        idInput.value = id;
                        nameInput.value = name;
                        statusInput.value = status;
                        nameInput.disabled = false;
                        statusInput.disabled = false;
                        submit.classList.remove('hidden');
                        title.textContent = 'Edit Income Category';
                        description.textContent = 'Update the category name or change its status.';
                        submit.textContent = 'Update Category';
                    };

                    const setViewMode = (button) => {
                        const name = button.dataset.categoryName;
                        const status = button.dataset.categoryStatus;

                        form.action = '#';
                        methodInput.value = 'POST';
                        idInput.value = '';
                        nameInput.value = name;
                        statusInput.value = status;
                        nameInput.disabled = true;
                        statusInput.disabled = true;
                        submit.classList.add('hidden');
                        title.textContent = 'View Income Category';
                        description.textContent = 'Review the category details. Use edit to make changes.';
                    };

                    document.querySelector('[data-category-create]')?.addEventListener('click', () => {
                        setCreateMode();
                        openModal();
                    });

                    document.querySelectorAll('[data-category-edit]').forEach((button) => {
                        button.addEventListener('click', () => {
                            setEditMode(button);
                            openModal();
                        });
                    });

                    document.querySelectorAll('[data-category-view]').forEach((button) => {
                        button.addEventListener('click', () => {
                            setViewMode(button);
                            openModal();
                        });
                    });

                    modal.querySelectorAll('[data-category-modal-close], [data-category-modal-overlay]').forEach((element) => {
                        element.addEventListener('click', closeModal);
                    });

                    @if ($errors->any())
                        openModal();
                    @endif
                });
            </script>
        @endif
    </div>
</x-layouts::app>
