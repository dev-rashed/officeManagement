@if (session()->has('toast_message') || session()->has('status') || session()->has('success') || session()->has('error') || session()->has('warning') || session()->has('info'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const status = @json(session('status', null));
            const success = @json(session('success', null));
            const error = @json(session('error', null));
            const warning = @json(session('warning', null));
            const info = @json(session('info', null));
            const toastMessage = @json(session('toast_message', null));
            const toastType = @json(session('toast_type', null));

            const showToast = (type, title, message) => {
                if (!window.iziToast || !message) {
                    return;
                }

                iziToast[type]({
                    title,
                    message,
                    position: 'topRight',
                    timeout: 5000,
                    close: true,
                });
            };

            if (toastMessage) {
                const type = toastType || 'success';
                showToast(type, type.charAt(0).toUpperCase() + type.slice(1), toastMessage);
                return;
            }

            if (status === 'profile-updated') {
                showToast('success', 'Success', 'Profile updated successfully.');
                return;
            }

            if (status === 'verification-link-sent') {
                showToast('success', 'Success', 'Verification email sent.');
                return;
            }

            if (success) {
                showToast('success', 'Success', success);
                return;
            }

            if (error) {
                showToast('error', 'Error', error);
                return;
            }

            if (warning) {
                showToast('warning', 'Warning', warning);
                return;
            }

            if (info) {
                showToast('info', 'Info', info);
            }
        });
    </script>
@endif
