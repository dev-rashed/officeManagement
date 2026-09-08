<?php

namespace App\Http\Controllers\Settings;

use App\Concerns\HandlesImageUploads;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    use HandlesImageUploads;

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

        // Both go through ImageService: optimised, converted to WebP, and given
        // a random filename. The previous code kept the uploader's own filename,
        // which is user-controlled input landing on disk.
        $user->profile_photo_path = $this->resolveUpload(
            $request, 'profile_photo', $user->profile_photo_path, 'images/profile-photos', 'photo',
        );

        $user->digital_signature_path = $this->resolveUpload(
            $request, 'digital_signature', $user->digital_signature_path, 'images/digital-signatures', 'logo',
        );

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
