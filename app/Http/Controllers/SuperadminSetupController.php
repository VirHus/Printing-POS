<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SuperadminSetupController extends Controller
{
    public function create(): View
    {
        abort_if(User::where('role', 'Superadmin')->exists(), 404);

        return view('settings.setup', ['ownerEmail' => config('system.superadmin_email')]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if(User::where('role', 'Superadmin')->exists(), 404);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:12|max:255|confirmed',
        ]);
        $ownerEmail = config('system.superadmin_email');
        if (User::where('email', $ownerEmail)->exists()) {
            return back()->withErrors(['email' => 'This email is already registered. Use a different owner email before setup.']);
        }
        $owner = DB::transaction(function () use ($validated, $request, $ownerEmail): User {
            $settings = SystemSetting::firstOrCreate(['id' => 1], ['values' => SystemSetting::DEFAULTS]);
            SystemSetting::whereKey($settings->id)->lockForUpdate()->first();
            abort_if(User::where('role', 'Superadmin')->lockForUpdate()->first() !== null, 404);

            return User::create([
                'name' => $validated['name'],
                'email' => $ownerEmail,
                'password' => $validated['password'],
                'role' => 'Superadmin',
                'is_active' => true,
                'store_id' => $request->user()->store_id ?? Store::where('is_active', true)->value('id'),
            ]);
        });
        app()->forgetInstance(SystemSetting::class);
        Auth::login($owner);
        $request->session()->regenerate();

        return redirect()->route('settings.edit')->with('success', 'Your Superadmin account is ready. Customize your system below.');
    }
}
