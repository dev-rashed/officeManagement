<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:25'],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
            'digital_signature' => ['nullable', 'image', 'max:2048'],
            'remove_profile_photo' => ['nullable', 'boolean'],
            'remove_digital_signature' => ['nullable', 'boolean'],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ]);

        if ($request->hasFile('profile_photo')) {
            $profilePhoto = $request->file('profile_photo');


            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $profilePhotoPath = time() . '_' . $profilePhoto->getClientOriginalName();
            $profilePhoto->move(storage_path('app/public/images/profile-photos'), $profilePhotoPath);
            $user->profile_photo_path = "images/profile-photos/$profilePhotoPath";
        } elseif (($validated['remove_profile_photo'] ?? false) && $user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
            $user->profile_photo_path = null;
        }

        if ($request->hasFile('digital_signature')) {
            $digitalSignature = $request->file('digital_signature');

            if ($user->digital_signature_path) {
                Storage::disk('public')->delete($user->digital_signature_path);
            }

            $digitalSignaturePath = time() . '_' . $digitalSignature->getClientOriginalName();
            $digitalSignature->move(storage_path('app/public/images/digital-signatures'), $digitalSignaturePath);
            $user->digital_signature_path = "images/digital-signatures/$digitalSignaturePath";
        } elseif (($validated['remove_digital_signature'] ?? false) && $user->digital_signature_path) {
            Storage::disk('public')->delete($user->digital_signature_path);
            $user->digital_signature_path = null;
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        if (function_exists('notify')) {
            notify()->success('Profile updated successfully', 'Success');
        }

        return back();
    }

    public function destroyProfilePhoto(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
            $user->forceFill(['profile_photo_path' => null])->save();
        }

        if (function_exists('notify')) {
            notify()->success('Profile picture deleted successfully', 'Success');
        }

        return back();
    }

    public function destroyDigitalSignature(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->digital_signature_path) {
            Storage::disk('public')->delete($user->digital_signature_path);
            $user->forceFill(['digital_signature_path' => null])->save();
        }

        if (function_exists('notify')) {
            notify()->success('Digital signature deleted successfully', 'Success');
        }

        return back();
    }
}
