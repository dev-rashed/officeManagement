@props([
    'name',                 // input name, e.g. "featured_image"
    'label' => null,
    'value' => null,        // existing stored path
    'hint' => null,
    'accept' => 'image/*',
    'removable' => true,    // show a Remove button for the stored image
    'height' => '9rem',
])

@php
    $id = 'iu-' . $name . '-' . Str::random(5);
    $existing = filled($value) ? Storage::disk('public')->url($value) : null;
    $isPdf = filled($value) && Str::endsWith(Str::lower($value), '.pdf');
@endphp

{{--
    Image field with a preview of what is already stored, a live preview of a
    newly chosen file, and a Remove button.

    Removal posts `remove_<name>=1`, which the controller acts on. Nothing is
    deleted until the form is saved, so a mis-click is undone by not saving.
--}}
<div class="iu" data-image-upload data-has-existing="{{ $existing ? '1' : '0' }}">
    @if ($label)
        <label for="{{ $id }}" class="iu-label">{{ $label }}</label>
    @endif

    <div class="iu-frame" style="--iu-h: {{ $height }}">
        {{-- Preview --}}
        <div class="iu-preview" data-iu-preview>
            @if ($isPdf)
                <a href="{{ $existing }}" target="_blank" rel="noopener" class="iu-file">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-7"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>
                    <span>{{ __('View current file') }}</span>
                </a>
            @elseif ($existing)
                <img src="{{ $existing }}" alt="" data-iu-image>
            @else
                <div class="iu-empty" data-iu-empty>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-6"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/></svg>
                    <span>{{ __('No image yet') }}</span>
                </div>
                <img src="" alt="" data-iu-image hidden>
            @endif
        </div>

        {{-- Controls --}}
        <div class="iu-controls">
            <input
                id="{{ $id }}"
                type="file"
                name="{{ $name }}"
                accept="{{ $accept }}"
                class="iu-input"
                data-iu-file
                {{ $attributes->except(['class']) }}
            >

            <div class="iu-actions">
                <button type="button" class="iu-btn is-choose" data-iu-choose>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-3.5"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                    <span data-iu-choose-label>{{ $existing ? __('Replace') : __('Choose image') }}</span>
                </button>

                @if ($removable)
                    <button type="button" class="iu-btn is-remove" data-iu-remove @class(['hidden' => ! $existing])>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-3.5"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        {{ __('Remove') }}
                    </button>
                @endif

                <button type="button" class="iu-btn is-undo hidden" data-iu-undo>
                    {{ __('Undo') }}
                </button>
            </div>

            <p class="iu-meta" data-iu-meta>
                {{ $hint ?? __('JPG, PNG or WebP. Converted to WebP and resized automatically.') }}
            </p>
        </div>
    </div>

    {{-- Set to 1 when the user asks for the stored image to be removed. --}}
    <input type="hidden" name="remove_{{ $name }}" value="0" data-iu-remove-flag>
</div>

@once
    <style>
        .iu { display:flex; flex-direction:column; gap:.4rem; }
        .iu-label { color:#3f3f46; font-size:.78rem; font-weight:600; }
        .iu-frame { align-items:stretch; border:1px solid #e4e4e7; border-radius:12px; display:flex; gap:.85rem; padding:.7rem; }
        .iu-preview { align-items:center; background:#fafafa; border:1px solid #f4f4f5; border-radius:9px; display:flex; flex-shrink:0; height:var(--iu-h); justify-content:center; overflow:hidden; width:var(--iu-h); }
        .iu-preview img { display:block; height:100%; object-fit:cover; width:100%; }
        .iu-empty, .iu-file { align-items:center; color:#a1a1aa; display:flex; flex-direction:column; font-size:.62rem; gap:.3rem; justify-content:center; padding:.5rem; text-align:center; }
        .iu-file { color:#0284c7; }
        .iu-file:hover { text-decoration:underline; }
        .iu-controls { display:flex; flex-direction:column; gap:.5rem; justify-content:center; min-width:0; }
        .iu-input { display:none; }
        .iu-actions { display:flex; flex-wrap:wrap; gap:.4rem; }
        .iu-btn { align-items:center; border:1px solid #e4e4e7; border-radius:8px; display:inline-flex; font-size:.7rem; font-weight:600; gap:.3rem; padding:.35rem .6rem; transition:background .15s ease; }
        .iu-btn.is-choose { background:#fff; color:#3f3f46; }
        .iu-btn.is-choose:hover { background:#f4f4f5; }
        .iu-btn.is-remove { background:#fff; border-color:#fecaca; color:#dc2626; }
        .iu-btn.is-remove:hover { background:#fef2f2; }
        .iu-btn.is-undo { background:#fffbeb; border-color:#fde68a; color:#b45309; }
        .iu-meta { color:#a1a1aa; font-size:.66rem; line-height:1.4; overflow-wrap:anywhere; }
        .iu.is-removing .iu-preview { opacity:.35; }
        .iu.is-removing .iu-meta { color:#dc2626; }
        .dark .iu-label { color:#d4d4d8; }
        .dark .iu-frame { border-color:#3f3f46; }
        .dark .iu-preview { background:#09090b; border-color:#27272a; }
        .dark .iu-btn { border-color:#3f3f46; }
        .dark .iu-btn.is-choose { background:transparent; color:#d4d4d8; }
        .dark .iu-btn.is-choose:hover { background:#27272a; }
        .dark .iu-btn.is-remove { background:transparent; }
    </style>

    <script>
        (() => {
            const KB = 1024;
            const human = (bytes) => bytes > KB * KB
                ? (bytes / (KB * KB)).toFixed(1) + ' MB'
                : Math.max(1, Math.round(bytes / KB)) + ' KB';

            const init = () => {
                document.querySelectorAll('[data-image-upload]').forEach((root) => {
                    if (root.dataset.bound === '1') return;
                    root.dataset.bound = '1';

                    const file = root.querySelector('[data-iu-file]');
                    const img = root.querySelector('[data-iu-image]');
                    const empty = root.querySelector('[data-iu-empty]');
                    const meta = root.querySelector('[data-iu-meta]');
                    const removeBtn = root.querySelector('[data-iu-remove]');
                    const undoBtn = root.querySelector('[data-iu-undo]');
                    const flag = root.querySelector('[data-iu-remove-flag]');
                    const chooseLabel = root.querySelector('[data-iu-choose-label]');
                    const defaultMeta = meta.textContent;

                    root.querySelector('[data-iu-choose]').addEventListener('click', () => file.click());

                    file.addEventListener('change', () => {
                        const chosen = file.files[0];
                        if (!chosen) return;

                        // Choosing a new file cancels a pending removal.
                        flag.value = '0';
                        root.classList.remove('is-removing');
                        undoBtn?.classList.add('hidden');

                        if (chosen.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                img.src = e.target.result;
                                img.hidden = false;
                                empty?.remove();
                            };
                            reader.readAsDataURL(chosen);
                        }

                        meta.textContent = chosen.name + ' · ' + human(chosen.size);
                        removeBtn?.classList.remove('hidden');
                        if (chooseLabel) chooseLabel.textContent = 'Replace';
                    });

                    removeBtn?.addEventListener('click', () => {
                        // A newly picked file is just discarded; a stored one is
                        // flagged for the server and only deleted on save.
                        if (file.files.length) {
                            file.value = '';
                            if (root.dataset.hasExisting !== '1') {
                                img.hidden = true;
                                img.src = '';
                            }
                            meta.textContent = defaultMeta;
                            if (root.dataset.hasExisting !== '1') removeBtn.classList.add('hidden');
                            return;
                        }

                        flag.value = '1';
                        root.classList.add('is-removing');
                        meta.textContent = 'Will be removed when you save.';
                        removeBtn.classList.add('hidden');
                        undoBtn?.classList.remove('hidden');
                    });

                    undoBtn?.addEventListener('click', () => {
                        flag.value = '0';
                        root.classList.remove('is-removing');
                        meta.textContent = defaultMeta;
                        undoBtn.classList.add('hidden');
                        removeBtn?.classList.remove('hidden');
                    });
                });
            };

            document.addEventListener('DOMContentLoaded', init);
            document.addEventListener('livewire:navigated', init);
            init();
        })();
    </script>
@endonce
