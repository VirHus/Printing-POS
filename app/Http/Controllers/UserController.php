<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('store')->when(! auth()->user()->isSuperadmin(), fn ($query) => $query->where('role', '!=', 'Superadmin'))->latest()->get();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create', ['stores' => Store::where('is_active', true)->orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in($request->user()->isSuperadmin() ? ['Superadmin', 'Administrator', 'Cashier'] : ['Administrator', 'Cashier'])],
            'store_id' => 'required|integer|exists:stores,id',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'store_id' => $validated['store_id'],
            'is_active' => true,
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        abort_if($user->isSuperadmin() && ! auth()->user()->isSuperadmin(), 403);
        $stores = Store::orderBy('name')->get();

        return view('users.edit', compact('user', 'stores'));
    }

    public function update(Request $request, User $user)
    {
        abort_if($user->isSuperadmin() && ! $request->user()->isSuperadmin(), 403);
        $validated = $request->validate([
            'name' => 'required|string|max:255',

            'email' => 'required|email|max:255|unique:users,email,'.$user->id,

            'role' => ['required', Rule::in($request->user()->isSuperadmin() ? ['Superadmin', 'Administrator', 'Cashier'] : ['Administrator', 'Cashier'])],
            'store_id' => 'required|integer|exists:stores,id',

            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($user->id === $request->user()->id && $user->isSuperadmin() && $validated['role'] !== 'Superadmin') {
            return back()->withErrors(['role' => 'You cannot remove your own Superadmin access.'])->withInput();
        }
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->store_id = $validated['store_id'];

        if (! empty($validated['password'])) {
            $user->password = Hash::make(
                $validated['password']
            );
        }

        $user->save();

        return redirect()
            ->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        abort_if($user->isSuperadmin() && ! auth()->user()->isSuperadmin(), 403);
        if ($user->id === auth()->id()) {
            return back()->withErrors([
                'user' => 'You cannot delete your own account.',
            ]);
        }

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'User deleted successfully.');
    }

    public function toggle(User $user)
    {
        abort_if($user->isSuperadmin() && ! auth()->user()->isSuperadmin(), 403);
        if ($user->id === auth()->id()) {
            return back()->withErrors([
                'user' => 'You cannot deactivate your own account.',
            ]);
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        return back()->with(
            'success',
            'User status updated successfully.'
        );
    }
}
