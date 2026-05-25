<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ContactSettingController extends Controller
{
    /**
     * Show the contact settings form.
     */
    public function edit()
    {
        Gate::authorize('cms.manage');
        $settings = ContactSetting::instance();
        return view('pages.admin.cms.contact-settings', compact('settings'));
    }

    /**
     * Update the contact settings.
     */
    public function update(Request $request)
    {
        Gate::authorize('cms.manage');

        $validated = $request->validate([
            'email' => 'required|email|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'social_links' => 'nullable|array',
            'social_links.*' => 'nullable|url',
        ]);

        $settings = ContactSetting::instance();
        $settings->update($validated);

        return redirect()->route('contact-settings.edit')
            ->with('success', 'Contact settings updated successfully.');
    }
}
