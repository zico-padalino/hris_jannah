@extends('layouts.app')

@section('title', __('pages.profile.edit_title'))
@section('subtitle', __('pages.profile.edit_subtitle'))

@section('content')
    <div class="mx-auto max-w-2xl space-y-6">
        @include('partials.profile-face-section')

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="app-card space-y-6 p-5 sm:p-6">
            @csrf
            @method('PUT')

            <section class="profile-photo-panel">
                @if($user->hasProfilePhoto())
                    <img
                        src="{{ $user->profilePhotoUrl() }}"
                        alt="{{ $user->name }}"
                        class="profile-photo-panel__avatar"
                    >
                @else
                    <span class="profile-photo-panel__avatar profile-photo-panel__avatar--placeholder">
                        {{ $user->profileInitials() }}
                    </span>
                @endif

                <div class="profile-photo-panel__actions space-y-3 text-center">
                    <label class="block">
                        <span class="form-label">{{ __('pages.profile.photo') }}</span>
                        <input
                            type="file"
                            name="profile_photo"
                            accept="image/jpeg,image/png,image/webp"
                            class="profile-photo-panel__file-input mt-1 block w-full text-sm"
                        >
                    </label>
                    <p class="text-xs app-muted-text">{{ __('pages.profile.photo_hint') }}</p>

                    @if($user->hasProfilePhoto())
                        <label class="inline-flex items-center gap-2 text-sm font-semibold">
                            <input type="checkbox" name="remove_profile_photo" value="1" @checked(old('remove_profile_photo')) class="h-4 w-4 rounded">
                            {{ __('pages.profile.photo_remove') }}
                        </label>
                    @endif
                </div>
            </section>

            <section class="space-y-4">
                <h2 class="profile-section-title">{{ __('pages.profile.account_section') }}</h2>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block min-w-0 sm:col-span-2">
                        <span class="form-label">{{ __('pages.profile.name') }}</span>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full">
                    </label>

                    <label class="block min-w-0 sm:col-span-2">
                        <span class="form-label">{{ __('pages.profile.email') }}</span>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full">
                    </label>

                    @if($employee)
                        <label class="block min-w-0">
                            <span class="form-label">{{ __('pages.profile.phone') }}</span>
                            <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}" class="w-full">
                        </label>

                        <label class="block min-w-0 sm:col-span-2">
                            <span class="form-label">{{ __('pages.profile.address') }}</span>
                            <textarea name="address" rows="3" class="w-full">{{ old('address', $employee->address) }}</textarea>
                        </label>
                    @endif
                </div>
            </section>

            <section class="space-y-4 border-t pt-4" style="border-color: var(--app-border);">
                <h2 class="profile-section-title">{{ __('pages.profile.security_section') }}</h2>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block min-w-0">
                        <span class="form-label">{{ __('pages.profile.password') }}</span>
                        @include('partials.password-field', [
                            'name' => 'password',
                            'autocomplete' => 'new-password',
                            'placeholder' => __('pages.profile.password_placeholder'),
                        ])
                    </label>

                    <label class="block min-w-0">
                        <span class="form-label">{{ __('pages.profile.password_confirm') }}</span>
                        @include('partials.password-field', [
                            'name' => 'password_confirmation',
                            'autocomplete' => 'new-password',
                        ])
                    </label>
                </div>
            </section>

            <div class="flex flex-col gap-3 border-t pt-4 sm:flex-row sm:justify-end" style="border-color: var(--app-border);">
                <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('dashboard') }}" class="btn-secondary w-full text-center sm:w-auto">
                    {{ __('app.cancel') }}
                </a>
                <button type="submit" class="btn-primary w-full sm:w-auto">{{ __('pages.profile.save') }}</button>
            </div>
        </form>
    </div>
@endsection
