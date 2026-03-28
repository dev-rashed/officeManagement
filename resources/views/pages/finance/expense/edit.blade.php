<x-layouts::app :title="__('Edit Expense')">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold">{{ __('Edit Expense Entry') }}</h1>
                <p class="text-sm text-slate-500">{{ __('Update expense details before approvals continue.') }}</p>
            </div>
            <a href="{{ route('expense.index') }}" class="inline-flex items-center justify-center rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-200">
                {{ __('Back to Expense List') }}
            </a>
        </div>

        @if($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-900">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('expense.update', $expense) }}" enctype="multipart/form-data" class="space-y-6 rounded-xl border border-neutral-200 bg-white p-6 shadow-sm">
            @csrf
            @method('PUT')

            <div class="grid gap-6 lg:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">{{ __('Expense Title') }}</span>
                    <input type="text" name="title" value="{{ old('title', $expense->title) }}" class="mt-2 w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-sm" required>
                </label>

                <label class="block">
                    <span class="text-sm font-medium text-slate-700">{{ __('Expense Category') }}</span>
                    <input type="text" name="expense_category" value="{{ old('expense_category', $expense->expense_category) }}" class="mt-2 w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-sm" required>
                </label>

                <label class="block">
                    <span class="text-sm font-medium text-slate-700">{{ __('Amount') }}</span>
                    <input type="number" step="0.01" name="amount" value="{{ old('amount', $expense->amount) }}" class="mt-2 w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-sm" required>
                </label>

                <label class="block">
                    <span class="text-sm font-medium text-slate-700">{{ __('Date') }}</span>
                    <input type="date" name="date" value="{{ old('date', $expense->date->format('Y-m-d')) }}" class="mt-2 w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-sm" required>
                </label>

                <label class="block">
                    <span class="text-sm font-medium text-slate-700">{{ __('Payment Method') }}</span>
                    <input type="text" name="payment_method" value="{{ old('payment_method', $expense->payment_method) }}" class="mt-2 w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-sm">
                </label>

                <label class="block">
                    <span class="text-sm font-medium text-slate-700">{{ __('Vendor / Payee Name') }}</span>
                    <input type="text" name="vendor_name" value="{{ old('vendor_name', $expense->vendor_name) }}" class="mt-2 w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-sm">
                </label>

                <label class="block">
                    <span class="text-sm font-medium text-slate-700">{{ __('Reference Number') }}</span>
                    <input type="text" name="reference_number" value="{{ old('reference_number', $expense->reference_number) }}" class="mt-2 w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-sm">
                </label>

                <label class="block lg:col-span-2">
                    <span class="text-sm font-medium text-slate-700">{{ __('Attachment') }}</span>
                    <input type="file" name="attachment" class="mt-2 w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-sm">
                    @if($expense->attachment_path)
                        <p class="mt-2 text-sm text-slate-500">{{ __('Current file:') }} <a href="{{ Storage::disk('public')->url($expense->attachment_path) }}" class="text-sky-600 hover:underline" target="_blank">{{ __('Download') }}</a></p>
                    @endif
                </label>

                <label class="block lg:col-span-2">
                    <span class="text-sm font-medium text-slate-700">{{ __('Description / Remarks') }}</span>
                    <textarea name="description" rows="4" class="mt-2 w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-sm">{{ old('description', $expense->description) }}</textarea>
                </label>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('expense.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-5 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">{{ __('Cancel') }}</a>
                <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-sky-600 px-5 py-2 text-sm font-medium text-white transition hover:bg-sky-700">{{ __('Save Changes') }}</button>
            </div>
        </form>
    </div>
</x-layouts::app>
