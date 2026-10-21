@extends('layouts.app')
@section('page-title', 'My Profile')
@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <header><h2 class="text-2xl font-bold tracking-tight text-white">My Profile</h2><p class="mt-2 text-sm text-slate-400">Manage your personal details and sign-in password.</p></header>
    <div class="glass-surface flex items-center gap-4 rounded-2xl border p-5 sm:p-6">
        <div class="avatar shrink-0">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
        <div class="min-w-0"><h3 class="break-words font-semibold text-white">{{ $user->name }}</h3><p class="mt-1 text-sm text-slate-400">{{ $user->role }}@if(! $user->isSuperadmin()) · {{ $user->store?->name ?? 'No store assigned' }}@endif</p></div>
    </div>
    <section class="glass-surface rounded-2xl border p-5 sm:p-7">
        <h3 class="text-lg font-semibold text-white">Personal details</h3>
        <form method="POST" action="{{ route('profile.update') }}" class="mt-6 space-y-5">
            @csrf @method('PUT')
            <div><label for="profileName">Name</label><input id="profileName" name="name" value="{{ old('name', $user->name) }}" autocomplete="name" maxlength="255" required></div>
            <div><label for="profileEmail">Email address</label><input id="profileEmail" type="email" name="email" value="{{ old('email', $user->email) }}" autocomplete="email" maxlength="255" required></div>
            <div><label for="emailCurrentPassword">Current password (only when changing email)</label><input id="emailCurrentPassword" type="password" name="current_password" autocomplete="current-password"><p class="mt-2 text-xs text-slate-400">Your role and assigned store can only be changed through account management.</p></div>
            <button class="rounded-xl bg-teal-600 px-5 py-3 text-sm font-semibold text-white hover:bg-teal-500">Save profile</button>
        </form>
    </section>
    <section class="glass-surface rounded-2xl border p-5 sm:p-7">
        <h3 class="text-lg font-semibold text-white">Change password</h3>
        <p class="mt-2 text-sm text-slate-400">Use at least 8 characters. Your current password is required.</p>
        <form method="POST" action="{{ route('profile.password.update') }}" class="mt-6 space-y-5">
            @csrf @method('PUT')
            <div><label for="passwordCurrent">Current password</label><input id="passwordCurrent" type="password" name="current_password" autocomplete="current-password" required></div>
            <div><label for="passwordNew">New password</label><input id="passwordNew" type="password" name="password" autocomplete="new-password" minlength="8" maxlength="255" required></div>
            <div><label for="passwordConfirm">Confirm new password</label><input id="passwordConfirm" type="password" name="password_confirmation" autocomplete="new-password" minlength="8" maxlength="255" required></div>
            <button class="rounded-xl bg-teal-600 px-5 py-3 text-sm font-semibold text-white hover:bg-teal-500">Update password</button>
        </form>
    </section>
</div>
@endsection
