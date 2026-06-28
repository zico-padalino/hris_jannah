@php
    $autoOpenCreate = $errors->any() && ! request()->filled('edit') && ! old('_user_id');
@endphp

<div
    id="user-create-modal"
    class="app-form-modal hidden"
    role="dialog"
    aria-modal="true"
    aria-labelledby="user-create-title"
    aria-hidden="true"
    data-auto-open="{{ ($autoOpenCreate || request()->boolean('create')) ? '1' : '0' }}"
>
    <div class="app-form-modal__backdrop" data-user-create-close></div>
    <div class="app-form-modal__dialog">
        <div class="app-form-modal__head">
            <h3 id="user-create-title" class="app-form-modal__title">{{ __('pages.users.create_title') }}</h3>
            <button type="button" class="app-form-modal__close" data-user-create-close aria-label="{{ __('pages.users.cancel') }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form method="POST" action="{{ route('users.store') }}" class="app-form-modal__form">
            @csrf
            <div class="app-form-modal__body">
                @include('users._form', ['mode' => 'create'])
            </div>
            <div class="app-form-modal__foot">
                <button type="button" class="btn-secondary w-full sm:w-auto" data-user-create-close>{{ __('pages.users.cancel') }}</button>
                <button type="submit" class="btn-primary w-full sm:w-auto">{{ __('pages.users.save') }}</button>
            </div>
        </form>
    </div>
</div>
