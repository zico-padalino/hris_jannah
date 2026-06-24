@if(session('success'))
    <div class="alert alert--success" role="status">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert--error" role="alert">
        {{ session('error') }}
    </div>
@endif

@if(session('warning'))
    <div class="alert border-2 border-amber-500 bg-amber-50 px-5 py-4 text-base font-semibold text-amber-900" role="alert">
        {{ session('warning') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert--error" role="alert">
        <p class="mb-2 font-bold">{{ __('app.check_form') }}</p>
        <ul class="list-disc space-y-1 pl-5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
