<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SystemSettingController extends Controller
{
    public function edit(): View
    {
        return view('settings.edit');
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'system_name' => 'required|string|max:80',
            'tagline' => 'nullable|string|max:100',
            'header_text' => 'nullable|string|max:120',
            'login_description' => 'nullable|string|max:500',
            'business_name' => 'required|string|max:120',
            'business_address' => 'nullable|string|max:255',
            'receipt_heading' => 'required|string|max:80',
            'receipt_footer' => 'nullable|string|max:500',
            'accent_color' => ['required', Rule::in(['#0d9488', '#2563eb', '#7c3aed', '#be185d', '#b45309'])],
            'sidebar_style' => 'required|in:standard,compact',
            'content_width' => 'required|in:contained,full',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048|dimensions:max_width=2400,max_height=2400',
            'favicon' => 'nullable|image|mimes:png|max:512|dimensions:max_width=512,max_height=512',
            'login_image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:4096|dimensions:max_width=4000,max_height=4000',
            'remove_logo' => 'nullable|boolean',
            'remove_favicon' => 'nullable|boolean',
            'remove_login_image' => 'nullable|boolean',
        ]);
        $settings = SystemSetting::current();
        $values = $settings->settings();
        foreach (array_diff(array_keys(SystemSetting::DEFAULTS), ['logo_path', 'favicon_path', 'login_image_path']) as $key) {
            $values[$key] = $validated[$key] ?? '';
        }
        $oldPaths = [];
        foreach (['logo', 'favicon', 'login_image'] as $field) {
            if ($request->hasFile($field) || $request->boolean('remove_'.$field)) {
                $oldPaths[] = $values[$field.'_path'];
                $values[$field.'_path'] = $request->hasFile($field)
                    ? $request->file($field)->store('branding', 'public') : null;
                abort_if($values[$field.'_path'] === false, 500, 'The image could not be stored.');
            }
        }
        $settings->fill(['values' => $values])->save();
        foreach (array_filter($oldPaths) as $path) {
            Storage::disk('public')->delete($path);
        }

        return redirect()->route('settings.edit')->with('success', 'System settings saved. Your changes are now live.');
    }
}
