<x-layouts::app :title="__('Income Details')">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold">{{ __('Income Details') }}</h1>
                <p class="text-sm text-slate-500">{{ __('Review income entry details, approval history, and approval actions.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('income.index') }}" class="inline-flex items-center justify-center rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-200">{{ __('Back to List') }}</a>
                @if(auth()->user()->isAdmin() || auth()->user()->isAccountant())
                    <a href="{{ route('income.edit', $income) }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">{{ __('Edit') }}</a>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">{{ session('success') }}</div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[1.5fr_1fr]">
            <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold">{{ __('Income Information') }}</h2>
                <dl class="mt-4 grid gap-4 text-sm text-slate-700">
                    <div>
                        <dt class="font-medium">{{ __('Title') }}</dt>
                        <dd>{{ $income->title }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium">{{ __('Source / Category') }}</dt>
                        <dd>{{ $income->source_category }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium">{{ __('Amount') }}</dt>
                        <dd>{{ number_format($income->amount, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium">{{ __('Date') }}</dt>
                        <dd>{{ $income->date->format('Y-m-d') }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium">{{ __('Payment Method') }}</dt>
                        <dd>{{ $income->payment_method ?? __('N/A') }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium">{{ __('Reference Number') }}</dt>
                        <dd>{{ $income->reference_number ?? __('N/A') }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium">{{ __('Status') }}</dt>
                        <dd>{{ $income->statusLabel() }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium">{{ __('Attachment') }}</dt>
                        <dd>
                            @if($income->attachment_path)
                                <a href="{{ Storage::disk('public')->url($income->attachment_path) }}" class="text-sky-600 hover:underline" target="_blank">{{ __('Download file') }}</a>
                            @else
                                {{ __('No attachment') }}
                            @endif
                        </dd>
                    </div>
                    <div class="lg:col-span-2">
                        <dt class="font-medium">{{ __('Description / Remarks') }}</dt>
                        <dd>{{ $income->description ?? __('No comments provided.') }}</dd>
                    </div>
                </dl>
            </div>

            <div class="space-y-6">
                <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold">{{ __('Approval Workflow') }}</h2>
                    <div class="mt-4 text-sm text-slate-700">
                        <p><span class="font-medium">{{ __('Current Status:') }}</span> {{ $income->statusLabel() }}</p>
                        @if($income->nextApprovalLabel())
                            <p><span class="font-medium">{{ __('Next Review:') }}</span> {{ $income->nextApprovalLabel() }}</p>
                        @endif
                    </div>
                </div>

                <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold">{{ __('Approval History') }}</h2>
                    @if($income->approvals->isEmpty())
                        <p class="mt-4 text-sm text-slate-500">{{ __('No approval actions have been recorded yet.') }}</p>
                    @else
                        <div class="mt-4 space-y-4 text-sm text-slate-700">
                            @foreach($income->approvals as $approval)
                                <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4">
                                    <p class="font-medium">{{ $approval->stageLabel() }} — {{ ucfirst($approval->status) }}</p>
                                    <p class="mt-1">{{ $approval->comments ?? __('No comments') }}</p>
                                    <p class="mt-2 text-xs text-slate-500">{{ $approval->approver?->name ?? __('System') }} · {{ $approval->approved_at?->format('Y-m-d H:i') ?? __('Not recorded') }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if($income->canBeApprovedBy(auth()->user()))
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-6 shadow-sm">
                        <h2 class="text-lg font-semibold">{{ __('Take Action') }}</h2>
                        <form method="POST" action="{{ route('income.approve', $income) }}" class="space-y-4 mt-4">
                            @csrf
                            <label class="block text-sm font-medium text-slate-700">{{ __('Action') }}</label>
                            <select name="action" class="mt-2 w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-sm" required>
                                <option value="approve">{{ __('Approve') }}</option>
                                <option value="reject">{{ __('Reject') }}</option>
                                <option value="send_back">{{ __('Send Back for Correction') }}</option>
                            </select>

                            <label class="block text-sm font-medium text-slate-700">{{ __('Comments / Remarks') }}</label>
                            <textarea name="comments" rows="4" class="mt-2 w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-sm"></textarea>

                            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-amber-600 px-5 py-2 text-sm font-medium text-white transition hover:bg-amber-700">{{ __('Submit Decision') }}</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts::app>
