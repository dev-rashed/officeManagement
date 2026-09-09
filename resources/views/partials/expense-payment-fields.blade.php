{{--
    Who paid for this expense.

    Expects: $payers (may be empty), and optionally $expense when editing.
--}}
@php
    $current = old('payment_source', $expense->payment_source ?? \App\Models\ExpenseEntry::SOURCE_OFFICE);
    $currentPayer = old('paid_by', $expense->paid_by ?? auth()->id());
    $canNameOthers = $payers->isNotEmpty();
@endphp

<div class="lg:col-span-2">
    <span class="ps-label">{{ __('Who paid for this?') }}</span>

    <div class="ps-choices" data-payment-source>
        <label class="ps-choice {{ $current === 'office' ? 'is-on' : '' }}">
            <input type="radio" name="payment_source" value="office" @checked($current === 'office')>
            <span class="ps-choice-body">
                <span class="ps-choice-title">{{ __('The office paid') }}</span>
                <span class="ps-choice-note">{{ __('Company account, card or petty cash. Nothing to pay back.') }}</span>
            </span>
        </label>

        <label class="ps-choice {{ $current === 'personal' ? 'is-on' : '' }}">
            <input type="radio" name="payment_source" value="personal" @checked($current === 'personal')>
            <span class="ps-choice-body">
                <span class="ps-choice-title">{{ __('I paid from my own pocket') }}</span>
                <span class="ps-choice-note">{{ __('The office owes this back. It appears as awaiting reimbursement.') }}</span>
            </span>
        </label>
    </div>

    @error('payment_source') <p class="ps-err">{{ $message }}</p> @enderror

    <div class="ps-payer {{ $current === 'personal' ? '' : 'hidden' }}" data-payer-wrap>
        @if ($canNameOthers)
            <label for="paid_by" class="ps-label">{{ __('Who is owed the money?') }}</label>
            <select id="paid_by" name="paid_by" class="ps-input">
                @foreach ($payers as $payer)
                    <option value="{{ $payer->id }}" @selected((int) $currentPayer === $payer->id)>
                        {{ $payer->name }}{{ $payer->role ? ' — ' . ucwords(str_replace('_', ' ', $payer->role)) : '' }}
                    </option>
                @endforeach
            </select>
            <p class="ps-hint">{{ __('Defaults to you. Change it if you are recording a claim on somebody else’s behalf.') }}</p>
        @else
            <p class="ps-hint">
                {{ __('This will be recorded as owed to you (:name).', ['name' => auth()->user()->name]) }}
            </p>
        @endif
        @error('paid_by') <p class="ps-err">{{ $message }}</p> @enderror
    </div>
</div>

@once
    <style>
        .ps-label { color:#334155; display:block; font-size:.82rem; font-weight:600; margin-bottom:.45rem; }
        .ps-choices { display:grid; gap:.6rem; grid-template-columns:1fr; }
        @media (min-width:640px){ .ps-choices{grid-template-columns:repeat(2,minmax(0,1fr))} }
        .ps-choice { align-items:flex-start; background:#fff; border:1px solid #e2e8f0; border-radius:11px; cursor:pointer; display:flex; gap:.6rem; padding:.7rem .8rem; transition:border-color .15s ease, background .15s ease; }
        .ps-choice:hover { border-color:#cbd5e1; }
        .ps-choice.is-on { background:#f0f9ff; border-color:#7dd3fc; box-shadow:0 0 0 2px rgba(14,165,233,.12); }
        .ps-choice input { accent-color:#0284c7; margin-top:.15rem; }
        .ps-choice-body { display:flex; flex-direction:column; gap:.15rem; }
        .ps-choice-title { color:#0f172a; font-size:.8rem; font-weight:600; }
        .ps-choice-note { color:#64748b; font-size:.7rem; line-height:1.35; }
        .ps-payer { margin-top:.7rem; }
        .ps-payer.hidden { display:none; }
        .ps-input { background:#fff; border:1px solid #e2e8f0; border-radius:9px; font-size:.82rem; padding:.5rem .65rem; width:100%; }
        .ps-input:focus { border-color:#0ea5e9; box-shadow:0 0 0 2px rgba(14,165,233,.16); outline:none; }
        .ps-hint { color:#94a3b8; font-size:.7rem; margin-top:.35rem; }
        .ps-err { color:#e11d48; font-size:.72rem; margin-top:.35rem; }
        .dark .ps-label, .dark .ps-choice-title { color:#e4e4e7; }
        .dark .ps-choice, .dark .ps-input { background:#18181b; border-color:#3f3f46; color:#fafafa; }
        .dark .ps-choice.is-on { background:#0c1f2e; border-color:#075985; }
    </style>

    <script>
        (() => {
            const init = () => {
                document.querySelectorAll('[data-payment-source]').forEach((group) => {
                    if (group.dataset.bound === '1') return;
                    group.dataset.bound = '1';

                    const wrap = group.closest('div').querySelector('[data-payer-wrap]');

                    group.addEventListener('change', () => {
                        group.querySelectorAll('.ps-choice').forEach((c) => {
                            c.classList.toggle('is-on', c.querySelector('input').checked);
                        });

                        const personal = group.querySelector('input[value="personal"]').checked;
                        if (wrap) wrap.classList.toggle('hidden', !personal);
                    });
                });
            };

            document.addEventListener('DOMContentLoaded', init);
            document.addEventListener('livewire:navigated', init);
            init();
        })();
    </script>
@endonce
