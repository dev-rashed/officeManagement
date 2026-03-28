<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Fortify\Fortify;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, mixed>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'role' => ['required', 'string', Rule::in(User::roles())],
            'phone' => ['nullable', 'string', 'max:25', Rule::requiredIf(fn () => in_array($input['role'] ?? null, [
                User::ROLE_CHAIRMAN,
                User::ROLE_MANAGING_DIRECTOR,
                User::ROLE_DIRECTOR,
            ]))],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
            'digital_signature' => ['nullable', 'image', 'max:2048'],
            'two_factor_type' => ['required', 'string', Rule::in(User::twoFactorTypes())],
            'password' => $this->passwordRules(),
        ])->validate();

        $profilePhotoPath = null;
        $digitalSignaturePath = null;

        if (isset($input['profile_photo'])) {
            $profilePhotoPath = Storage::disk('public')->putFile('profile-photos', $input['profile_photo']);
        }

        if (isset($input['digital_signature'])) {
            $digitalSignaturePath = Storage::disk('public')->putFile('digital-signatures', $input['digital_signature']);
        }

        $twoFactorSecret = null;
        $twoFactorConfirmedAt = null;

        if ($input['two_factor_type'] === User::TWO_FACTOR_TYPE_EMAIL) {
            $placeholderCode = (string) random_int(100000, 999999);
            $secretPayload = sprintf('email:%s:%s', Hash::make($placeholderCode), now()->addMinutes(10)->timestamp);
            $twoFactorSecret = Fortify::currentEncrypter()->encrypt($secretPayload);
            $twoFactorConfirmedAt = now();
        }

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'role' => $input['role'],
            'phone' => $input['phone'] ?? null,
            'profile_photo_path' => $profilePhotoPath,
            'digital_signature_path' => $digitalSignaturePath,
            'two_factor_type' => $input['two_factor_type'],
            'two_factor_secret' => $twoFactorSecret,
            'two_factor_confirmed_at' => $twoFactorConfirmedAt,
        ]);
    }
}
