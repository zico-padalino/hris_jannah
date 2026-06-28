@php
    $user = $user ?? null;
    $mode = $mode ?? ($user ? 'edit' : 'create');
    $isCreate = $mode === 'create';
@endphp

<div class="user-form-fields space-y-4">
    <label class="block min-w-0">
        <span class="form-label">{{ __('pages.users.col_name') }}</span>
        <input
            type="text"
            name="name"
            value="{{ old('name', $isCreate ? '' : optional($user)->name) }}"
            required
            class="w-full"
        >
        @error('name')
            <p class="mt-1 text-sm font-semibold text-red-600">{{ $message }}</p>
        @enderror
    </label>

    <label class="block min-w-0">
        <span class="form-label">{{ __('pages.users.col_email') }}</span>
        <input
            type="email"
            name="email"
            value="{{ old('email', $isCreate ? '' : optional($user)->email) }}"
            required
            class="w-full"
        >
        @error('email')
            <p class="mt-1 text-sm font-semibold text-red-600">{{ $message }}</p>
        @enderror
    </label>

    <label class="block min-w-0">
        <span class="form-label">{{ __('pages.profile.password') }}</span>
        @include('partials.password-field', [
            'name' => 'password',
            'placeholder' => $isCreate ? null : __('pages.users.password_edit_placeholder'),
            'autocomplete' => 'new-password',
        ])
        @error('password')
            <p class="mt-1 text-sm font-semibold text-red-600">{{ $message }}</p>
        @enderror
    </label>

    <label class="block min-w-0">
        <span class="form-label">{{ __('pages.users.col_role') }}</span>
        <select name="role" required class="w-full">
            @if($isCreate)
                <option value="" @selected(old('role', '') === '')>-</option>
            @endif
            @foreach(['super_admin', 'hr', 'branch_admin', 'employee'] as $val)
                <option
                    value="{{ $val }}"
                    @selected(old('role', $isCreate ? '' : optional($user)->role?->value) === $val)
                >{{ __('enums.user_role.'.$val) }}</option>
            @endforeach
        </select>
        @error('role')
            <p class="mt-1 text-sm font-semibold text-red-600">{{ $message }}</p>
        @enderror
    </label>

    <label class="block min-w-0">
        <span class="form-label">{{ __('pages.users.col_branch') }}</span>
        <select name="branch_id" class="w-full">
            <option value="" @selected(old('branch_id', $isCreate ? '' : optional($user)->branch_id) === null || old('branch_id', $isCreate ? '' : optional($user)->branch_id) === '')>-</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}" @selected((string) old('branch_id', $isCreate ? '' : optional($user)->branch_id) === (string) $branch->id)>{{ $branch->name }}</option>
            @endforeach
        </select>
        @error('branch_id')
            <p class="mt-1 text-sm font-semibold text-red-600">{{ $message }}</p>
        @enderror
    </label>

    <label class="inline-flex items-center gap-2 text-sm font-semibold">
        <input
            type="checkbox"
            name="is_active"
            value="1"
            @checked($isCreate ? old('is_active', false) : old('is_active', optional($user)->is_active ?? false))
            class="h-4 w-4 rounded"
        >
        {{ __('pages.users.field_active') }}
    </label>

    @unless($isCreate)
        <p class="text-xs app-muted-text">{{ __('pages.users.password_hint') }}</p>
        <p class="text-xs app-muted-text">{{ __('pages.users.employee_sync_hint') }}</p>
    @endunless
</div>
