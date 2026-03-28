<x-layouts::auth :title="__('Register')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" enctype="multipart/form-data" class="flex flex-col gap-6">
            @csrf
            <!-- Name -->
            <flux:input
                name="name"
                :label="__('Name')"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                :placeholder="__('Full name')"
            />

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Role -->
            <div>
                <label for="role" class="block text-sm font-medium text-zinc-900 dark:text-white">
                    {{ __('Role') }}
                </label>

                <select
                    id="role"
                    name="role"
                    required
                    class="mt-2 block w-full rounded-md border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-primary-500 focus:outline-none focus:ring-primary-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
                >
                    <option value="">{{ __('Select role') }}</option>
                    <option value="{{ App\Models\User::ROLE_SUPERADMIN }}" {{ old('role') === App\Models\User::ROLE_SUPERADMIN ? 'selected' : '' }}>{{ __('Superadmin') }}</option>
                    <option value="{{ App\Models\User::ROLE_CHAIRMAN }}" {{ old('role') === App\Models\User::ROLE_CHAIRMAN ? 'selected' : '' }}>{{ __('Chairman') }}</option>
                    <option value="{{ App\Models\User::ROLE_MANAGING_DIRECTOR }}" {{ old('role') === App\Models\User::ROLE_MANAGING_DIRECTOR ? 'selected' : '' }}>{{ __('Managing Director') }}</option>
                    <option value="{{ App\Models\User::ROLE_DIRECTOR }}" {{ old('role') === App\Models\User::ROLE_DIRECTOR ? 'selected' : '' }}>{{ __('Director') }}</option>
                </select>
            </div>

            <!-- Phone -->
            <flux:input
                name="phone"
                :label="__('Phone')"
                :value="old('phone')"
                type="tel"
                placeholder="+1 234 567 8900"
            />

            <!-- Profile picture -->
            <flux:input
                name="profile_photo"
                :label="__('Profile picture')"
                type="file"
                accept="image/*"
            />

            <!-- Digital signature -->
            <flux:input
                name="digital_signature"
                :label="__('Digital signature')"
                type="file"
                accept="image/*"
            />

            <!-- Two-factor authentication type -->
            <div>
                <label for="two_factor_type" class="block text-sm font-medium text-zinc-900 dark:text-white">
                    {{ __('Two-factor authentication') }}
                </label>

                <select
                    id="two_factor_type"
                    name="two_factor_type"
                    required
                    class="mt-2 block w-full rounded-md border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-primary-500 focus:outline-none focus:ring-primary-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
                >
                    <option value="{{ App\Models\User::TWO_FACTOR_TYPE_AUTHENTICATOR }}" {{ old('two_factor_type') === App\Models\User::TWO_FACTOR_TYPE_AUTHENTICATOR ? 'selected' : '' }}>{{ __('Authenticator app') }}</option>
                    <option value="{{ App\Models\User::TWO_FACTOR_TYPE_EMAIL }}" {{ old('two_factor_type') === App\Models\User::TWO_FACTOR_TYPE_EMAIL ? 'selected' : '' }}>{{ __('Email code') }}</option>
                </select>
            </div>

            <!-- Password -->
            <flux:input
                name="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Password')"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="__('Confirm password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Confirm password')"
                viewable
            />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                    {{ __('Create account') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Already have an account?') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
