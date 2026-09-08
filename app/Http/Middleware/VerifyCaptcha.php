<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

/**
 * Checks the captcha on the forms a bot can actually reach.
 *
 * Only the unauthenticated auth forms are covered. Every other form in this
 * application sits behind `auth`, where a captcha would stop no attack -- the
 * request is already coming from a signed-in person -- while costing staff a
 * puzzle on every save. Those endpoints are protected by CSRF, the permission
 * gates and rate limiting instead.
 *
 * Registered on Fortify's route group, so it sees each auth request and
 * decides for itself whether that route needs a captcha.
 */
class VerifyCaptcha
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->shouldVerify($request)) {
            return $next($request);
        }

        $validator = Validator::make($request->all(), [
            'captcha' => ['required', 'captcha'],
        ], [
            'captcha.required' => __('Please enter the characters shown in the image.'),
            'captcha.captcha' => __('The characters did not match. Please try the new image.'),
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->except(['password', 'password_confirmation', 'captcha']));
        }

        return $next($request);
    }

    private function shouldVerify(Request $request): bool
    {
        if (! config('captcha.enabled', true)) {
            return false;
        }

        if (! $request->isMethod('POST')) {
            return false;
        }

        return in_array($request->route()?->getName(), config('captcha.protected_routes', []), true);
    }
}
