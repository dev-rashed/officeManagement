<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * The account that always exists.
 *
 * Safe to run repeatedly: an existing account with this email is promoted and
 * protected rather than replaced, and its password is left alone.
 */
class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('SUPERADMIN_EMAIL', 'rashed.eee.brur@gmail.com');
        $name = env('SUPERADMIN_NAME', 'Rashed');

        $existing = User::where('email', $email)->first();

        if ($existing) {
            $this->promote($existing);

            return;
        }

        $this->create($email, $name);
    }

    private function promote(User $user): void
    {
        $previous = $user->role ?: 'none';

        // Assigned directly rather than through fill(): is_protected is left
        // out of the model's fillable list so it can never be set by a form.
        $user->role = User::ROLE_SUPERADMIN;
        $user->is_protected = true;
        $user->email_verified_at ??= now();

        // Only reset the password when the environment explicitly asks for
        // one. Without SUPERADMIN_PASSWORD set, re-seeding must never quietly
        // change the password of a live account.
        $supplied = env('SUPERADMIN_PASSWORD');
        $passwordReset = false;

        if (filled($supplied)) {
            $user->password = Hash::make($supplied);
            $passwordReset = true;
        }

        $user->save();

        Role::flushCache();

        $this->command?->info("Superadmin: {$user->email} already existed — promoted from '{$previous}' and protected.");
        $this->command?->line($passwordReset
            ? '  Password reset to the value of SUPERADMIN_PASSWORD.'
            : '  Password left unchanged (set SUPERADMIN_PASSWORD to reset it).');

        $this->warnIfWeak($supplied);
    }

    /**
     * The protected superadmin cannot be deleted or demoted, so a weak password
     * on it is worth saying out loud.
     */
    private function warnIfWeak(?string $password): void
    {
        if (blank($password)) {
            return;
        }

        $problems = [];

        if (strlen($password) < 12) {
            $problems[] = 'shorter than 12 characters';
        }

        if (in_array(strtolower($password), ['password', 'secret', '12345678', 'admin', 'letmein', 'password123'], true)) {
            $problems[] = 'one of the most commonly guessed passwords';
        }

        if ($problems === []) {
            return;
        }

        $this->command?->newLine();
        $this->command?->warn('  Warning: this password is '.implode(' and ', $problems).'.');
        $this->command?->line('  It belongs to an account that cannot be deleted or demoted, and the');
        $this->command?->line('  application sends real email. Change it before this reaches production.');
    }

    private function create(string $email, string $name): void
    {
        // A password in a repository is a password in everybody's git history,
        // so one is generated unless the environment supplies it.
        $supplied = env('SUPERADMIN_PASSWORD');
        $password = $supplied ?: Str::password(16, symbols: false);

        $user = new User([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => User::ROLE_SUPERADMIN,
            // Email codes need no authenticator app to be enrolled first,
            // which matters for an account that has to stay reachable.
            'two_factor_type' => User::TWO_FACTOR_TYPE_EMAIL,
        ]);

        $user->is_protected = true;
        $user->email_verified_at = now();
        $user->save();

        Role::flushCache();

        $this->command?->info("Superadmin created: {$email}");

        if (! $supplied) {
            $this->command?->newLine();
            $this->command?->warn('  Generated password (shown once, copy it now):');
            $this->command?->line('    '.$password);
            $this->command?->newLine();
            $this->command?->line('  Set SUPERADMIN_PASSWORD in .env to choose your own instead.');
        }

        $this->command?->line('  This account is protected: it cannot be deleted or moved off superadmin.');

        $this->warnIfWeak($supplied);
    }
}
