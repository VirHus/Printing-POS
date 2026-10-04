@extends('layouts.app')
@section('page-title', 'Superadmin Setup')
@section('content')
<section class="mx-auto max-w-xl rounded-2xl border border-slate-700 glass-surface bg-[#0c1828] p-6 sm:p-8">
    <p class="mb-2 text-xs font-semibold uppercase tracking-[0.16em] text-teal-300">System owner</p>
    <h2 class="text-2xl font-bold tracking-tight text-white">Create your Superadmin account</h2>
    <p class="mt-3 text-sm leading-6 text-slate-400">This separate account controls branding, layout, images, receipts, and user access. Your current Administrator account will remain available.</p>
    <form method="POST" action="{{ route('superadmin.setup.store') }}" class="mt-6 space-y-5">
        @csrf
        <div><label for="ownerEmail">Superadmin email</label><input id="ownerEmail" type="email" value="{{ $ownerEmail }}" readonly class="opacity-70"></div>
        <div><label for="ownerName">Your name</label><input id="ownerName" name="name" value="{{ old('name', 'Lester John') }}" maxlength="255" autocomplete="name" required></div>
        <div><label for="ownerPassword">Password</label><input id="ownerPassword" name="password" type="password" minlength="12" maxlength="255" autocomplete="new-password" required><p class="mt-2 text-xs text-slate-500">Use at least 12 characters.</p></div>
        <div><label for="ownerPasswordConfirmation">Confirm password</label><input id="ownerPasswordConfirmation" name="password_confirmation" type="password" minlength="12" maxlength="255" autocomplete="new-password" required></div>
        <button type="submit" class="w-full rounded-xl bg-teal-600 px-5 py-3.5 text-sm font-semibold text-white hover:bg-teal-500">Create Superadmin and open settings</button>
        <p class="text-xs leading-5 text-slate-500">You will be signed in to the new account. This setup screen closes after the first Superadmin is created.</p>
    </form>
</section>
@endsection
