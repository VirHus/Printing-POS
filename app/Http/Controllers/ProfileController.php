<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('users.profile', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'current_password' => [Rule::requiredIf($request->input('email') !== $user->email), 'nullable', 'current_password'],
        ]);
        $user->fill(['name' => $validated['name'], 'email' => $validated['email']]);
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }
        $user->save();

        return redirect()->route('profile.edit')->with('success', 'Your profile has been updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|max:255|confirmed',
        ]);
        $request->user()->update(['password' => $validated['password']]);
        $request->session()->regenerate();

        return redirect()->route('profile.edit')->with('success', 'Your password has been updated.');
    }
}
