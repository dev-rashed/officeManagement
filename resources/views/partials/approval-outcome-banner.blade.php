{{--
    Shows why an entry was rejected or sent back, at the top of the entry where
    the person who submitted it will actually see it. Without this the reason is
    buried in the approval history further down the page.

    Expects: $entry -- an IncomeEntry or ExpenseEntry with approvals loaded.
--}}
@php
    $outcome = $entry->approvals
        ->whereIn('status', ['rejected', 'sent_back'])
        ->sortByDesc(fn ($a) => $a->approved_at ?? $a->created_at)
        ->first();

    $isRejected = $entry->status === 'rejected';
    $isSentBack = $entry->status === 'sent_back';
@endphp

@if(($isRejected || $isSentBack) && $outcome)
    <div class="rounded-xl border p-4 {{ $isRejected ? 'border-rose-200 bg-rose-50' : 'border-amber-200 bg-amber-50' }}">
        <div class="flex items-start gap-3">
            <span class="mt-0.5 shrink-0 {{ $isRejected ? 'text-rose-600' : 'text-amber-600' }}">
                @if($isRejected)
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/></svg>
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.793 2.232a.75.75 0 01-.025 1.06L3.622 7.25h10.003a5.375 5.375 0 010 10.75H10.75a.75.75 0 010-1.5h2.875a3.875 3.875 0 000-7.75H3.622l4.146 3.957a.75.75 0 01-1.036 1.085l-5.5-5.25a.75.75 0 010-1.085l5.5-5.25a.75.75 0 011.06.025z" clip-rule="evenodd"/></svg>
                @endif
            </span>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold {{ $isRejected ? 'text-rose-900' : 'text-amber-900' }}">
                    {{ $isRejected ? __('Rejected by :who', ['who' => $outcome->approver?->name ?? __('a reviewer')]) : __('Sent back for correction by :who', ['who' => $outcome->approver?->name ?? __('a reviewer')]) }}
                </p>

                <p class="mt-1 whitespace-pre-line text-sm {{ $isRejected ? 'text-rose-800' : 'text-amber-800' }}">
                    {{ $outcome->comments ?: __('No reason was recorded.') }}
                </p>

                <p class="mt-2 text-xs {{ $isRejected ? 'text-rose-600' : 'text-amber-700' }}">
                    {{ $outcome->stageLabel() }}
                    @if($outcome->approved_at)
                        · {{ $outcome->approved_at->format('d M Y, H:i') }}
                    @endif
                </p>
            </div>
        </div>
    </div>
@endif
