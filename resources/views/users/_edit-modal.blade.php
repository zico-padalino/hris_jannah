@php
    $autoOpenEdit = $errors->any() && (request()->filled('edit') || old('_user_id'));
@endphp

<div
    id="user-edit-modal"
    class="app-form-modal hidden"
    role="dialog"
    aria-modal="true"
    aria-labelledby="user-edit-title"
    aria-hidden="true"
    data-auto-open="{{ $autoOpenEdit ? '1' : '0' }}"
>
    <div class="app-form-modal__backdrop" data-user-edit-close></div>
    <div class="app-form-modal__dialog">
        <div class="app-form-modal__head">
            <h3 id="user-edit-title" class="app-form-modal__title">{{ __('pages.users.edit_title') }}</h3>
            <button type="button" class="app-form-modal__close" data-user-edit-close aria-label="{{ __('pages.users.cancel') }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form
            method="POST"
            action="{{ $editUser ? route('users.update', $editUser) : '#' }}"
            id="user-edit-form"
            class="app-form-modal__form"
        >
            @csrf
            @method('PUT')
            <input type="hidden" name="_user_id" value="{{ old('_user_id', $editUser?->id) }}">
            <div class="app-form-modal__body">
                @include('users._form', ['mode' => 'edit', 'user' => $editUser])
            </div>
            <div class="app-form-modal__foot">
                <button type="button" class="btn-secondary w-full sm:w-auto" data-user-edit-close>{{ __('pages.users.cancel') }}</button>
                <button type="submit" class="btn-primary w-full sm:w-auto">{{ __('pages.users.save') }}</button>
            </div>
        </form>
    </div>
</div>
