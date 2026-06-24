@extends('layouts.app')

@section('title', __('pages.roles.edit_title', ['role' => $role->label()]))
@section('subtitle', __('pages.roles.edit_subtitle'))

@section('content')
    <div class="mx-auto max-w-2xl">
        <form method="POST" action="{{ route('roles.update', $role) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <section class="rounded-xl border bg-white p-6 shadow-sm">
                <div class="space-y-4">
                    <div>
                        <label class="form-label">{{ __('pages.roles.col_group') }}</label>
                        <input
                            type="text"
                            value="{{ $role->label() }}"
                            class="w-full bg-slate-50"
                            readonly
                        >
                    </div>

                    <div>
                        <label class="form-label" for="description">{{ __('pages.roles.col_description') }}</label>
                        <textarea
                            id="description"
                            name="description"
                            rows="4"
                            class="w-full"
                            required
                            maxlength="500"
                        >{{ old('description', $description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('roles.index') }}" class="btn-secondary text-center">{{ __('pages.roles.back') }}</a>
                <button type="submit" class="btn-primary">{{ __('pages.roles.save') }}</button>
            </div>
        </form>
    </div>
@endsection
