{{--
    Makes the comments box required whenever the chosen action is a rejection or
    a send-back. Mirrors the required_if rule on the server -- the server is the
    authority, this is only so the user finds out before submitting.
--}}
<script>
    (() => {
        const init = () => {
            document.querySelectorAll('[data-approval-form]').forEach((form) => {
                if (form.dataset.approvalBound === '1') return;
                form.dataset.approvalBound = '1';

                const action = form.querySelector('[data-approval-action]');
                const comments = form.querySelector('[data-approval-comments]');
                const label = form.querySelector('[data-approval-label]');
                const star = form.querySelector('[data-approval-required]');

                if (!action || !comments) return;

                const LABELS = {
                    approve: @js(__('Comments / Remarks')),
                    reject: @js(__('Reason for rejection')),
                    send_back: @js(__('What needs correcting')),
                };

                const sync = () => {
                    const needsReason = action.value === 'reject' || action.value === 'send_back';

                    comments.required = needsReason;
                    comments.setAttribute('aria-required', needsReason ? 'true' : 'false');

                    if (label) label.textContent = LABELS[action.value] ?? LABELS.approve;
                    if (star) star.classList.toggle('hidden', !needsReason);

                    comments.placeholder = needsReason
                        ? @js(__('Explain what is wrong so it can be corrected.'))
                        : @js(__('Optional for an approval.'));
                };

                action.addEventListener('change', sync);

                form.addEventListener('submit', (e) => {
                    const needsReason = action.value === 'reject' || action.value === 'send_back';

                    if (needsReason && comments.value.trim() === '') {
                        e.preventDefault();
                        comments.classList.add('border-rose-500');
                        comments.focus();

                        if (typeof iziToast !== 'undefined') {
                            iziToast.warning({
                                title: @js(__('Reason required')),
                                message: @js(__('Please say why before rejecting or sending back.')),
                                position: 'topRight',
                            });
                        }
                    }
                });

                comments.addEventListener('input', () => comments.classList.remove('border-rose-500'));

                sync();
            });
        };

        document.addEventListener('DOMContentLoaded', init);
        document.addEventListener('livewire:navigated', init);
        init();
    })();
</script>
