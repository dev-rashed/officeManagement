<?php

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Profile settings')] class extends Component {
    use ProfileValidationRules;

    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public ?string $existingProfilePhotoUrl = null;
    public ?string $existingDigitalSignatureUrl = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
        $this->phone = Auth::user()->phone ?? '';
        $this->existingProfilePhotoUrl = $this->getFileUrl(Auth::user()->profile_photo_path);
        $this->existingDigitalSignatureUrl = $this->getFileUrl(Auth::user()->digital_signature_path);
    }

    protected function getFileUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        return $disk->url($path);
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            ...$this->profileRules($user->id),
            'profile_photo' => ['nullable', 'image', 'max:2048'],
            'digital_signature' => ['nullable', 'image', 'max:2048'],
        ]);

        $user->fill(Arr::except($validated, ['profile_photo', 'digital_signature']));

        if ($this->profile_photo) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            $user->profile_photo_path = $this->profile_photo->store('images/profile-photos', 'public');
        }

        if ($this->digital_signature) {
            if ($user->digital_signature_path) {
                Storage::disk('public')->delete($user->digital_signature_path);
            }

            $user->digital_signature_path = $this->digital_signature->store('images/digital-signatures', 'public');
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->existingProfilePhotoUrl = $this->getFileUrl($user->profile_photo_path);
        $this->existingDigitalSignatureUrl = $this->getFileUrl($user->digital_signature_path);
        $this->reset(['profile_photo', 'digital_signature']);

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Profile settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Profile')" :subheading="__('Update your profile details and contact information')">
        <form wire:ignore method="POST" action="{{ route('profile.update') }}" class="my-6 w-full space-y-6" enctype="multipart/form-data">
            @csrf

            <flux:input name="name" value="{{ old('name', $name) }}" :label="__('Name')" type="text" required autofocus autocomplete="name" />

            <div>
                <flux:input name="email" value="{{ old('email', $email) }}" :label="__('Email')" type="email" required autocomplete="email" />
            </div>

            <div>
                <flux:input name="phone" value="{{ old('phone', $phone) }}" :label="__('Phone')" type="tel" placeholder="+1 234 567 8900" />
            </div>

            <div wire:ignore>
                <label for="profile_photo" class="block text-sm font-medium text-zinc-900 dark:text-white">
                    {{ __('Profile picture') }}
                </label>
                <input type="hidden" name="remove_profile_photo" id="remove_profile_photo" value="0" />
                <input
                    id="profile_photo"
                    name="profile_photo"
                    type="file"
                    accept="image/*"
                    class="mt-2 block w-full rounded-md border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-primary-500 focus:outline-none focus:ring-primary-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
                    data-preview-input
                    data-preview-target="profile_photo_preview"
                    data-remove-target="remove_profile_photo"
                />

                <div
                    id="profile_photo_preview"
                    class="@if (! $existingProfilePhotoUrl) hidden @endif mt-3 flex items-center gap-3 rounded-lg border border-zinc-200 bg-zinc-50 p-2.5 dark:border-zinc-800 dark:bg-zinc-900/60"
                    data-preview-wrapper
                    data-existing-src="{{ $existingProfilePhotoUrl }}"
                    data-delete-form="delete_profile_photo_form"
                >
                    <img
                        src="{{ $existingProfilePhotoUrl }}"
                        alt="{{ __('Current profile photo') }}"
                        class="h-14 w-14 rounded-md object-cover ring-1 ring-zinc-200 dark:ring-zinc-700"
                        data-preview-image
                    />

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-xs font-medium text-zinc-900 dark:text-white" data-preview-name>
                            {{ __('Current image') }}
                        </p>
                        <p class="text-[11px] text-zinc-500 dark:text-zinc-400" data-preview-state>
                            {{ __('Saved image') }}
                        </p>
                    </div>

                    <button
                        type="button"
                        class="inline-flex items-center rounded-md border border-red-200 px-2.5 py-1 text-xs font-medium text-red-600 transition hover:bg-red-50 dark:border-red-900/70 dark:text-red-400 dark:hover:bg-red-950/40"
                        data-preview-delete
                    >
                        {{ __('Delete') }}
                    </button>
                </div>

                @error('profile_photo')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror

            </div>

            <div wire:ignore>
                <label for="digital_signature" class="block text-sm font-medium text-zinc-900 dark:text-white">
                    {{ __('Digital signature') }}
                </label>
                <input type="hidden" name="remove_digital_signature" id="remove_digital_signature" value="0" />
                <input
                    id="digital_signature"
                    name="digital_signature"
                    type="file"
                    accept="image/*"
                    class="mt-2 block w-full rounded-md border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-primary-500 focus:outline-none focus:ring-primary-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
                    data-preview-input
                    data-preview-target="digital_signature_preview"
                    data-remove-target="remove_digital_signature"
                />

                <div
                    id="digital_signature_preview"
                    class="@if (! $existingDigitalSignatureUrl) hidden @endif mt-3 flex items-center gap-3 rounded-lg border border-zinc-200 bg-zinc-50 p-2.5 dark:border-zinc-800 dark:bg-zinc-900/60"
                    data-preview-wrapper
                    data-existing-src="{{ $existingDigitalSignatureUrl }}"
                    data-delete-form="delete_digital_signature_form"
                >
                    <img
                        src="{{ $existingDigitalSignatureUrl }}"
                        alt="{{ __('Current digital signature') }}"
                        class="h-14 w-14 rounded-md object-cover ring-1 ring-zinc-200 dark:ring-zinc-700"
                        data-preview-image
                    />

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-xs font-medium text-zinc-900 dark:text-white" data-preview-name>
                            {{ __('Current image') }}
                        </p>
                        <p class="text-[11px] text-zinc-500 dark:text-zinc-400" data-preview-state>
                            {{ __('Saved image') }}
                        </p>
                    </div>

                    <button
                        type="button"
                        class="inline-flex items-center rounded-md border border-red-200 px-2.5 py-1 text-xs font-medium text-red-600 transition hover:bg-red-50 dark:border-red-900/70 dark:text-red-400 dark:hover:bg-red-950/40"
                        data-preview-delete
                    >
                        {{ __('Delete') }}
                    </button>
                </div>

                @error('digital_signature')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror

            </div>

            @if ($this->hasUnverifiedEmail)
                <div>
                    <flux:text class="mt-4">
                        {{ __('Your email address is unverified.') }}

                        <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                            {{ __('Click here to re-send the verification email.') }}
                        </flux:link>
                    </flux:text>

                    @if (session('status') === 'verification-link-sent')
                        <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </flux:text>
                    @endif
                </div>
            @endif

            <div class="mt-8 flex items-center justify-between gap-4 border-t border-zinc-200/80 pt-5 dark:border-zinc-800">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                    {{ __('Save your changes when you are ready.') }}
                </div>

                <div class="flex items-center justify-end">
                    <button
                        type="submit"
                        class="inline-flex min-w-28 items-center justify-center rounded-full bg-white px-5 py-2.5 text-sm font-medium text-zinc-900 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:bg-zinc-100 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-white/70 focus:ring-offset-2 focus:ring-offset-zinc-900 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
                        data-test="update-profile-button"
                    >
                        {{ __('Save') }}
                    </button>
                </div>

                @if (session('status') === 'profile-updated')
                    <flux:text class="font-medium !text-green-600 !dark:text-green-400">
                        {{ __('Saved.') }}
                    </flux:text>
                @endif
            </div>
        </form>

        @if ($existingProfilePhotoUrl)
            <form id="delete_profile_photo_form" method="POST" action="{{ route('profile.photo.destroy') }}" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endif

        @if ($existingDigitalSignatureUrl)
            <form id="delete_digital_signature_form" method="POST" action="{{ route('profile.signature.destroy') }}" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endif

        @if ($this->showDeleteUser)
            <livewire:pages::settings.delete-user-form />
        @endif
    </x-pages::settings.layout>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-preview-input]').forEach((input) => {
                const wrapper = document.getElementById(input.dataset.previewTarget);

                if (!wrapper) {
                    return;
                }

                const image = wrapper.querySelector('[data-preview-image]');
                const name = wrapper.querySelector('[data-preview-name]');
                const state = wrapper.querySelector('[data-preview-state]');
                const removeField = document.getElementById(input.dataset.removeTarget);
                const deleteButton = wrapper.querySelector('[data-preview-delete]');
                const existingSrc = wrapper.dataset.existingSrc || '';
                const deleteFormId = wrapper.dataset.deleteForm;

                const hidePreview = () => {
                    wrapper.classList.add('hidden');
                    if (image) {
                        image.src = '';
                    }
                };

                const showExistingPreview = () => {
                    if (!existingSrc) {
                        hidePreview();
                        return;
                    }

                    wrapper.classList.remove('hidden');
                    image.src = existingSrc;
                    name.textContent = 'Current image';
                    state.textContent = 'Saved image';
                };

                input.addEventListener('change', () => {
                    const [file] = input.files || [];

                    if (!file) {
                        if (removeField.value === '1') {
                            hidePreview();
                            return;
                        }

                        showExistingPreview();
                        return;
                    }

                    removeField.value = '0';
                    wrapper.classList.remove('hidden');
                    image.src = URL.createObjectURL(file);
                    name.textContent = file.name;
                    state.textContent = 'New image selected';
                });

                deleteButton?.addEventListener('click', () => {
                    const [file] = input.files || [];

                    if (file) {
                        input.value = '';
                        removeField.value = '0';
                        showExistingPreview();
                        return;
                    }

                    if (!existingSrc || !deleteFormId) {
                        hidePreview();
                        return;
                    }

                    const deleteForm = document.getElementById(deleteFormId);

                    if (!deleteForm) {
                        return;
                    }

                    window.appPopup?.open({
                        title: 'Delete image?',
                        message: 'This will permanently remove the saved file from your profile.',
                        buttonText: 'Delete',
                        secondaryButtonText: 'Cancel',
                        onPrimary: () => deleteForm.submit(),
                    });
                });
            });
        });
    </script>
</section>
