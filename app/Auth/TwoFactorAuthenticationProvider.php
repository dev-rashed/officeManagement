<?php

namespace App\Auth;

use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\TwoFactorAuthenticationProvider as FortifyTwoFactorAuthenticationProvider;

class TwoFactorAuthenticationProvider extends FortifyTwoFactorAuthenticationProvider
{
    /**
     * Verify the given code.
     *
     * @param  string  $secret
     * @param  string  $code
     * @return bool
     */
    public function verify($secret, $code)
    {
        if (str_starts_with($secret, 'email:')) {
            [$prefix, $hash, $expiresAt] = explode(':', $secret, 3) + [null, null, null];

            if ($prefix !== 'email' || is_null($hash) || is_null($expiresAt)) {
                return false;
            }

            if (now()->timestamp > (int) $expiresAt) {
                return false;
            }

            return Hash::check($code, $hash);
        }

        return parent::verify($secret, $code);
    }
}
