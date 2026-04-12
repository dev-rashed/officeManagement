<?php

namespace App\Providers;

use App\Auth\TwoFactorAuthenticationProvider as CustomTwoFactorAuthenticationProvider;
use App\Models\User;
use App\Notifications\EmailTwoFactorCode;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider as TwoFactorAuthenticationProviderContract;
use Laravel\Fortify\Events\TwoFactorAuthenticationChallenged;
use Laravel\Fortify\Fortify;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TwoFactorAuthenticationProviderContract::class, CustomTwoFactorAuthenticationProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->registerTwoFactorListeners();
        $this->registerPermissions();
    }

    /**
     * Register permission gates.
     */
    protected function registerPermissions(): void
    {
        Gate::before(fn (User $user, string $ability) => $user->hasPermission('*') ? true : null);

        Gate::define('finance.view', fn (User $user): bool => $user->hasPermission('finance.view'));
        Gate::define('finance.manage', fn (User $user): bool => $user->hasPermission('finance.manage'));
        Gate::define('approvals.manage', fn (User $user): bool => $user->hasPermission('approvals.manage'));
        Gate::define('users.manage', fn (User $user): bool => $user->hasPermission('users.manage'));
        Gate::define('settings.manage', fn (User $user): bool => $user->hasPermission('settings.manage'));
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    protected function registerTwoFactorListeners(): void
    {
        Event::listen(TwoFactorAuthenticationChallenged::class, function (TwoFactorAuthenticationChallenged $event): void {
            $user = $event->user;

            if ($user->two_factor_type !== User::TWO_FACTOR_TYPE_EMAIL) {
                return;
            }

            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiresAt = now()->addMinutes(10)->timestamp;
            $payload = sprintf('email:%s:%s', Hash::make($code), $expiresAt);

            $user->two_factor_secret = Fortify::currentEncrypter()->encrypt($payload);
            $user->two_factor_confirmed_at = $user->two_factor_confirmed_at ?? now();
            $user->save();

            Notification::send($user, new EmailTwoFactorCode($code));
        });
    }
}
