<x-layouts::app :title="__('Expense Details')">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold">{{ __('Expense Details') }}</h1>
                <p class="text-sm text-slate-500">{{ __('Review expense details, approval history, and approve or reject at the correct stage.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('expense.index') }}" class="inline-flex items-center justify-center rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-200">{{ __('Back to List') }}</a>
                @if(auth()->user()->isAdmin() || auth()->user()->isAccountant())
                    <a href="{{ route('expense.edit', $expense) }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">{{ __('Edit') }}</a>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">{{ session('success') }}</div>
        @endif

        @include('partials.approval-outcome-banner', ['entry' => $expense])

        <div class="grid gap-6 lg:grid-cols-[1.5fr_1fr]">
            <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold">{{ __('Expense Information') }}</h2>
                <dl class="mt-4 grid gap-4 text-sm text-slate-700">
                    <div>
                        <dt class="font-medium">{{ __('Title') }}</dt>
                        <dd>{{ $expense->title }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium">{{ __('Expense Category') }}</dt>
                        <dd>{{ $expense->category?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium">{{ __('Amount') }}</dt>
                        <dd>{{ number_format($expense->amount, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium">{{ __('Date') }}</dt>
                        <dd>{{ $expense->date->format('Y-m-d') }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium">{{ __('Payment Method') }}</dt>
                        <dd>{{ $expense->payment_method ?? __('N/A') }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium">{{ __('Vendor / Payee') }}</dt>
                        <dd>{{ $expense->vendor_name ?? __('N/A') }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium">{{ __('Reference Number') }}</dt>
                        <dd>{{ $expense->reference_number ?? __('N/A') }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium">{{ __('Status') }}</dt>
                        <dd>{{ $expense->statusLabel() }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium">{{ __('Attachment') }}</dt>
                        <dd>
                            @if($expense->attachment_path)
                                <a href="{{ Storage::disk('public')->url($expense->attachment_path) }}" class="text-sky-600 hover:underline" target="_blank">{{ __('Download file') }}</a>
                            @else
                                {{ __('No attachment') }}
                            @endif
                        </dd>
                    </div>
                    <div class="lg:col-span-2">
                        <dt class="font-medium">{{ __('Description / Remarks') }}</dt>
                        <dd>{{ $expense->description ?? __('No comments provided.') }}</dd>
                    </div>
                </dl>
            </div>

            <div class="space-y-6">
                <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold">{{ __('Approval Workflow') }}</h2>
                    <div class="mt-4 text-sm text-slate-700">
                        <p><span class="font-medium">{{ __('Current Status:') }}</span> {{ $expense->statusLabel() }}</p>
                        @if($expense->nextApprovalLabel())
                            <p><span class="font-medium">{{ __('Next Review:') }}</span> {{ $expense->nextApprovalLabel() }}</p>
                        @endif
                    </div>
                </div>

                <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold">{{ __('Approval History') }}</h2>
                    @if($expense->approvals->isEmpty())
                        <p class="mt-4 text-sm text-slate-500">{{ __('No approval actions have been recorded yet.') }}</p>
                    @else
                        <div class="mt-4 space-y-4 text-sm text-slate-700">
                            @foreach($expense->approvals as $approval)
                                <div @class([
                                    'rounded-2xl border p-4',
                                    'border-neutral-200 bg-neutral-50' => $approval->status === 'approved',
                                    'border-rose-200 bg-rose-50' => $approval->status === 'rejected',
                                    'border-amber-200 bg-amber-50' => $approval->status === 'sent_back',
                                ])>
                                    <p class="font-medium">{{ $approval->stageLabel() }} — {{ ucfirst(str_replace('_', ' ', $approval->status)) }}</p>
                                    <p class="mt-1 whitespace-pre-line">{{ $approval->comments ?: __('No comments') }}</p>
                                    <p class="mt-2 text-xs text-slate-500">{{ $approval->approver?->name ?? __('System') }} · {{ $approval->approved_at?->format('Y-m-d H:i') ?? __('Not recorded') }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if($expense->canBeApprovedBy(auth()->user()))
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-6 shadow-sm">
                        <h2 class="text-lg font-semibold">{{ __('Take Action') }}</h2>
                        <form method="POST" action="{{ route('expense.approve', $expense) }}" class="space-y-4 mt-4" data-approval-form>
                            @csrf
                            <label class="block text-sm font-medium text-slate-700">{{ __('Action') }}</label>
                            <select name="action" data-approval-action class="mt-2 w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-sm" required>
                                <option value="approve" @selected(old('action') === 'approve')>{{ __('Approve') }}</option>
                                <option value="reject" @selected(old('action') === 'reject')>{{ __('Reject') }}</option>
                                <option value="send_back" @selected(old('action') === 'send_back')>{{ __('Send Back for Correction') }}</option>
                            </select>

                            <label class="block text-sm font-medium text-slate-700">
                                <span data-approval-label>{{ __('Comments / Remarks') }}</span>
                                <span data-approval-required class="hidden text-rose-600">*</span>
                            </label>
                            <textarea
                                name="comments"
                                rows="4"
                                data-approval-comments
                                placeholder="{{ __('Optional for an approval. Required when rejecting or sending back.') }}"
                                class="mt-2 w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-sm @error('comments') border-rose-500 @enderror"
                            >{{ old('comments') }}</textarea>
                            @error('comments')
                                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                            @enderror

                            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-amber-600 px-5 py-2 text-sm font-medium text-white transition hover:bg-amber-700">{{ __('Submit Decision') }}</button>
                        </form>

                        @include('partials.approval-reason-script')
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts::app>
