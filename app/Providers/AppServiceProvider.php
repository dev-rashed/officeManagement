<?php

namespace App\Providers;

use App\Auth\TwoFactorAuthenticationProvider as CustomTwoFactorAuthenticationProvider;
use App\Models\User;
use App\Notifications\EmailTwoFactorCode;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
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
        $this->registerRateLimiters();
    }

    /**
     * Register permission gates.
     */
    protected function registerPermissions(): void
    {
        Gate::before(fn (User $user, string $ability) => $user->hasPermission('*') ? true : null);

        Gate::define('finance.view', fn (User $user): bool => $user->hasPermission('finance.view'));
        // Seeing your OWN expenses is a baseline, not a permission. This is
        // what lets someone see everyone else's.
        Gate::define('finance.view_all', fn (User $user): bool => $user->hasPermission('finance.view_all'));
        Gate::define('finance.reimburse', fn (User $user): bool => $user->hasPermission('finance.manage'));
        Gate::define('finance.manage', fn (User $user): bool => $user->hasPermission('finance.manage'));
        Gate::define('finance.categories.manage', fn (User $user): bool => $user->hasPermission('finance.categories.manage'));
        Gate::define('approvals.manage', fn (User $user): bool => $user->hasPermission('approvals.manage'));
        Gate::define('users.manage', fn (User $user): bool => $user->hasPermission('users.manage'));
        Gate::define('settings.manage', fn (User $user): bool => $user->hasPermission('settings.manage'));
        Gate::define('cms.manage', fn (User $user): bool => $user->hasPermission('cms.manage'));
        Gate::define('assets.manage', fn (User $user): bool => $user->hasPermission('assets.manage'));
        Gate::define('projects.view', fn (User $user): bool => $user->hasPermission('projects.view'));
        Gate::define('projects.manage', fn (User $user): bool => $user->hasPermission('projects.manage'));
        Gate::define('students.manage', fn (User $user): bool => $user->hasPermission('students.manage'));
        Gate::define('activity.view', fn (User $user): bool => $user->hasPermission('activity.view'));

        /*
         * Roles and permissions are superadmin-only, and deliberately NOT a
         * grantable permission. If it were one, an admin holding
         * `settings.manage` could grant it to themselves and then grant
         * themselves everything else -- the screen would protect nothing.
         *
         * This checks the role directly, so it cannot be handed out from
         * inside the very screen it guards.
         */
        Gate::define('roles.manage', fn (User $user): bool => $user->isSuperAdmin());
    }

    /**
     * Rate limits for authenticated write traffic.
     *
     * The admin forms are behind CSRF and the permission gates, which stop an
     * outsider. This is the backstop for a compromised or misused session: a
     * stolen cookie cannot be used to hammer the write endpoints, and a runaway
     * script cannot fill the database.
     */
    protected function registerRateLimiters(): void
    {
        RateLimiter::for('admin-write', function (Request $request) {
            return Limit::perMinute(90)
                ->by($request->user()?->id ?: $request->ip())
                ->response(fn () => response()->json([
                    'message' => 'Too many requests. Please slow down and try again in a moment.',
                ], 429));
        });
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
