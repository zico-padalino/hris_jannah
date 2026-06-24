@extends('layouts.app')

@section('title', __('pages.announcements.edit_title'))

@section('content')
    <div class="panel mx-auto max-w-2xl p-6">
        <form method="POST" action="{{ route('announcements.update', $announcement) }}" class="space-y-4">
            @csrf
            @method('PUT')
            @include('announcements._form', ['announcement' => $announcement])
            <div class="flex flex-wrap gap-3 pt-2">
                <button type="submit" class="btn-primary">{{ __('pages.announcements.save_changes') }}</button>
                <a href="{{ route('announcements.index') }}" class="btn-secondary">{{ __('pages.announcements.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
