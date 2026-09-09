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

        {{-- Who paid, and whether the office has settled up --}}
        @if ($expense->isPersonal())
            <div class="rb-panel {{ $expense->isReimbursed() ? 'is-done' : 'is-owed' }}">
                <div class="rb-main">
                    <p class="rb-title">
                        @if ($expense->isReimbursed())
                            {{ __('Reimbursed to :name', ['name' => $expense->payer?->name ?? __('the payer')]) }}
                        @else
                            {{ __('Owed to :name', ['name' => $expense->payer?->name ?? __('the payer')]) }}
                        @endif
                    </p>
                    <p class="rb-note">
                        @if ($expense->isReimbursed())
                            {{ __('Paid back on :date', ['date' => $expense->reimbursed_at?->format('d M Y')]) }}
                            @if ($expense->reimburser) · {{ __('recorded by :who', ['who' => $expense->reimburser->name]) }} @endif
                        @else
                            {{ __('Paid from their own pocket. The office has not paid this back yet.') }}
                        @endif
                    </p>
                    @if ($expense->reimbursement_note)
                        <p class="rb-note">{{ $expense->reimbursement_note }}</p>
                    @endif
                </div>

                <span class="rb-amount">৳ {{ number_format((float) $expense->amount, 2) }}</span>

                @can('finance.reimburse')
                    <form method="POST" action="{{ route('expense.reimburse', $expense) }}" class="rb-form">
                        @csrf
                        @if ($expense->isReimbursed())
                            <input type="hidden" name="action" value="mark_unpaid">
                            <button type="submit" class="rb-btn is-undo">{{ __('Mark as not paid') }}</button>
                        @else
                            <input type="hidden" name="action" value="mark_paid">
                            <input type="text" name="reimbursement_note" class="rb-input" placeholder="{{ __('Reference (optional)') }}" maxlength="255">
                            <button type="submit" class="rb-btn is-pay"
                                @disabled($expense->status !== \App\Models\ExpenseEntry::STATUS_FULLY_APPROVED)
                                title="{{ $expense->status !== \App\Models\ExpenseEntry::STATUS_FULLY_APPROVED ? __('Only fully approved expenses can be reimbursed') : '' }}">
                                {{ __('Mark as reimbursed') }}
                            </button>
                        @endif
                    </form>
                @endcan
            </div>

            <style>
                .rb-panel { align-items:center; border:1px solid; border-radius:14px; display:flex; flex-wrap:wrap; gap:1rem; padding:.9rem 1.1rem; }
                .rb-panel.is-owed { background:#fffbeb; border-color:#fde68a; }
                .rb-panel.is-done { background:#ecfdf5; border-color:#a7f3d0; }
                .rb-main { flex:1 1 16rem; min-width:0; }
                .rb-title { font-size:.88rem; font-weight:700; }
                .rb-panel.is-owed .rb-title { color:#92400e; }
                .rb-panel.is-done .rb-title { color:#065f46; }
                .rb-note { font-size:.76rem; margin-top:.2rem; }
                .rb-panel.is-owed .rb-note { color:#a16207; }
                .rb-panel.is-done .rb-note { color:#047857; }
                .rb-amount { font-size:1.15rem; font-weight:700; font-variant-numeric:tabular-nums; }
                .rb-panel.is-owed .rb-amount { color:#92400e; }
                .rb-panel.is-done .rb-amount { color:#065f46; }
                .rb-form { align-items:center; display:flex; gap:.4rem; }
                .rb-input { background:#fff; border:1px solid #e2e8f0; border-radius:8px; font-size:.75rem; padding:.4rem .55rem; width:11rem; }
                .rb-btn { border:1px solid transparent; border-radius:8px; font-size:.75rem; font-weight:600; padding:.45rem .8rem; }
                .rb-btn.is-pay { background:#059669; color:#fff; }
                .rb-btn.is-pay:hover:not(:disabled) { background:#047857; }
                .rb-btn.is-pay:disabled { cursor:not-allowed; opacity:.5; }
                .rb-btn.is-undo { background:#fff; border-color:#d1d5db; color:#4b5563; }
                .rb-btn.is-undo:hover { background:#f9fafb; }
            </style>
        @endif

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
