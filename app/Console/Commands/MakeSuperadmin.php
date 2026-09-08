<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator;

/**
 * The only way in, until the Users screen exists.
 *
 * There is no user management in the admin and registration is switched off,
 * so a superadmin has to be made from the command line.
 */
class MakeSuperadmin extends Command
{
    protected $signature = 'user:superadmin
                            {email? : The account to promote, or create}
                            {--create : Create the account if it does not exist}
                            {--name= : Name to use when creating}
                            {--password= : Password to use when creating (you will be prompted otherwise)}';

    protected $description = 'Promote a user to superadmin, or create one';

    public function handle(): int
    {
        $email = $this->argument('email') ?: $this->ask('Email address');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('That is not a valid email address.');

            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            if (! $this->option('create') && ! $this->confirm("No account with {$email}. Create one?", true)) {
                $this->line('Nothing changed.');

                return self::FAILURE;
            }

            $user = $this->createUser($email);

            if (! $user) {
                return self::FAILURE;
            }
        } else {
            $previous = $user->role ?: 'none';
            $user->role = User::ROLE_SUPERADMIN;
            $user->email_verified_at ??= now();
            $user->save();

            $this->info("Promoted {$user->name} <{$user->email}> from '{$previous}' to superadmin.");
        }

        Role::flushCache();

        $this->newLine();
        $this->line('  Sign in at: '.url('/login'));
        $this->line('  Roles & Permissions: '.url('/admin/roles'));
        $this->newLine();
        $this->warn('  Two-factor authentication is enabled on this application.');
        $this->line('  The account uses the "'.$user->two_factor_type.'" method; set it up at '.url('/settings/security').'.');

        return self::SUCCESS;
    }

    private function createUser(string $email): ?User
    {
        $name = $this->option('name') ?: $this->ask('Full name');
        $password = $this->option('password') ?: $this->secret('Password (min 12 characters)');

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'unique:users,email'],
                'password' => ['required', 'string', Password::min(12)],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return null;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => User::ROLE_SUPERADMIN,
            'two_factor_type' => User::TWO_FACTOR_TYPE_EMAIL,
        ]);

        $user->email_verified_at = now();
        $user->save();

        $this->info("Created {$user->name} <{$user->email}> as superadmin.");

        return $user;
    }
}
