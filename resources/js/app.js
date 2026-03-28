document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('global-popup');

    if (!root) {
        return;
    }

    const panel = root.querySelector('[data-popup-panel]');
    const overlay = root.querySelector('[data-popup-overlay]');
    const closeButton = root.querySelector('[data-popup-close]');
    const iconWrap = root.querySelector('[data-popup-icon-wrap]');
    const icon = root.querySelector('[data-popup-icon]');
    const title = root.querySelector('[data-popup-title]');
    const message = root.querySelector('[data-popup-message]');
    const primaryButton = root.querySelector('[data-popup-primary]');
    const secondaryButton = root.querySelector('[data-popup-secondary]');
    const actions = root.querySelector('[data-popup-actions]');

    const defaults = {
        title: 'Welcome to our website!',
        message: 'Have fun navigating through the demos.',
        buttonText: 'Close',
        secondaryButtonText: null,
        iconUrl: icon?.getAttribute('src') ?? '',
        iconAlt: icon?.getAttribute('alt') ?? 'Popup icon',
        closable: true,
        closeOnPrimary: true,
        closeOnSecondary: true,
        onPrimary: null,
        onSecondary: null,
        onClose: null,
    };

    let state = { ...defaults };
    let previousActiveElement = null;

    const setContent = (options) => {
        state = { ...defaults, ...options };

        if (title) {
            title.textContent = state.title;
        }

        if (message) {
            message.textContent = state.message;
        }

        if (primaryButton) {
            primaryButton.textContent = state.buttonText;
        }

        if (secondaryButton) {
            if (state.secondaryButtonText) {
                secondaryButton.textContent = state.secondaryButtonText;
                secondaryButton.classList.remove('hidden');
            } else {
                secondaryButton.classList.add('hidden');
            }
        }

        if (icon && state.iconUrl) {
            icon.src = state.iconUrl;
            icon.alt = state.iconAlt;
            icon.classList.remove('hidden');
            iconWrap?.classList.remove('hidden');
        } else {
            icon.classList.add('hidden');
            iconWrap?.classList.add('hidden');
        }

        if (closeButton) {
            closeButton.classList.toggle('hidden', !state.closable);
        }

        if (overlay) {
            overlay.classList.toggle('cursor-pointer', state.closable);
        }

        if (actions) {
            actions.classList.remove('hidden');
        }
    };

    const close = () => {
        root.classList.add('hidden');
        root.classList.remove('flex');
        root.classList.add('pointer-events-none');
        document.body.classList.remove('overflow-hidden');
        root.setAttribute('aria-hidden', 'true');

        if (typeof state.onClose === 'function') {
            state.onClose();
        }

        if (previousActiveElement instanceof HTMLElement) {
            previousActiveElement.focus();
        }
    };

    const open = (options = {}) => {
        previousActiveElement = document.activeElement;
        setContent(options);
        root.classList.remove('hidden', 'pointer-events-none');
        root.classList.add('flex');
        document.body.classList.add('overflow-hidden');
        root.setAttribute('aria-hidden', 'false');
        panel?.focus();
    };

    overlay?.addEventListener('click', () => {
        if (state.closable) {
            close();
        }
    });

    closeButton?.addEventListener('click', close);
    primaryButton?.addEventListener('click', () => {
        if (typeof state.onPrimary === 'function') {
            state.onPrimary();
        }

        if (state.closeOnPrimary) {
            close();
        }
    });

    secondaryButton?.addEventListener('click', () => {
        if (typeof state.onSecondary === 'function') {
            state.onSecondary();
        }

        if (state.closeOnSecondary) {
            close();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !root.classList.contains('hidden') && state.closable) {
            close();
        }
    });

    window.addEventListener('app-popup:open', (event) => {
        open(event.detail ?? {});
    });

    window.addEventListener('app-popup:close', close);

    window.appPopup = {
        open,
        close,
    };

    const initActivityLogTable = () => {
        if (typeof $ === 'undefined' || typeof $.fn.DataTable === 'undefined') {
            return;
        }

        const table = $('#activity-log-table');

        if (!table.length || $.fn.DataTable.isDataTable(table)) {
            return;
        }

        const ajaxUrl = table.data('ajax-url');

        if (!ajaxUrl) {
            return;
        }

        table.DataTable({
            processing: true,
            serverSide: true,
            ajax: ajaxUrl,
            order: [[0, 'desc']],
            columns: [
                { data: 'created_at', defaultContent: '' },
                { data: 'user', defaultContent: '' },
                { data: 'action', defaultContent: '' },
                { data: 'route_name', defaultContent: '' },
                { data: 'ip_address', defaultContent: '' },
                { data: 'description', defaultContent: '' },
            ],
            pageLength: 25,
            lengthMenu: [[25, 50, 100], [25, 50, 100]],
            language: {
                emptyTable: 'No data available in table',
                zeroRecords: 'No matching records found',
            },
        });
    };

    const runActivityLogInit = () => {
        initActivityLogTable();
    };

    document.addEventListener('livewire:load', runActivityLogInit);
    document.addEventListener('livewire:update', runActivityLogInit);
    window.addEventListener('load', runActivityLogInit);

    runActivityLogInit();
});
