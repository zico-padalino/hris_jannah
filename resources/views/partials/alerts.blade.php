@if(session('success'))
    <div class="alert alert--success" role="status">
        <span class="alert__icon-wrap" aria-hidden="true">
            <svg class="alert__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
        </span>
        <span class="alert__message">{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="alert alert--error" role="alert">
        <span class="alert__icon-wrap" aria-hidden="true">
            <svg class="alert__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
        </span>
        <span class="alert__message">{{ session('error') }}</span>
    </div>
@endif

@if(session('warning'))
    <div class="alert alert--warning" role="alert">
        <span class="alert__icon-wrap" aria-hidden="true">
            <svg class="alert__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
        </span>
        <span class="alert__message">{{ session('warning') }}</span>
    </div>
@endif

@if($errors->any())
    <div class="alert alert--error" role="alert">
        <span class="alert__icon-wrap" aria-hidden="true">
            <svg class="alert__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
        </span>
        <div class="alert__message">
            <p class="mb-2 font-bold">{{ __('app.check_form') }}</p>
            <ul class="list-disc space-y-1 pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
