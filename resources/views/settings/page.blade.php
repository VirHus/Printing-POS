@extends('layouts.app')
@section('page-title', $customPage['title'])
@section('content')
<article class="glass-surface mx-auto max-w-4xl rounded-2xl border border-slate-700 p-5 sm:p-8"><p class="text-xs uppercase tracking-wider text-teal-300">{{ $customPage['section'] }}</p><h2 class="mt-3 text-2xl font-bold text-white">{{ $customPage['title'] }}</h2><div class="mt-6 whitespace-pre-wrap break-words text-sm leading-7 text-slate-300">{{ $customPage['body'] }}</div></article>
@endsection
