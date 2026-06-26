@php($user = $user ?? null)
<div class="space-y-4">
<input name="name" value="{{ old('name', optional($user)->name) }}" placeholder="Nama" required class="w-full rounded-lg border px-3 py-2">
<input name="email" type="email" value="{{ old('email', optional($user)->email) }}" placeholder="Email" required class="w-full rounded-lg border px-3 py-2">
@include('partials.password-field', [
    'name' => 'password',
    'placeholder' => $user ? __('pages.users.password_edit_placeholder') : __('pages.users.password_create_placeholder'),
    'autocomplete' => 'new-password',
    'inputClass' => 'w-full rounded-lg border px-3 py-2',
])
<select name="role" required class="w-full rounded-lg border px-3 py-2">
@foreach(['super_admin'=>'Super Admin','hr'=>'HRD','branch_admin'=>'Admin Cabang','employee'=>'Pegawai'] as $val=>$label)
<option value="{{ $val }}" @selected(old('role', optional($user)->role?->value ?? '')===$val)>{{ $label }}</option>
@endforeach
</select>
<select name="branch_id" class="w-full rounded-lg border px-3 py-2"><option value="">- Cabang (kosong untuk pusat) -</option>
@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected(old('branch_id', optional($user)->branch_id)==$branch->id)>{{ $branch->name }}</option>@endforeach
</select>
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', optional($user)->is_active ?? true))> Aktif</label>
<p class="text-xs text-slate-500">{{ __('pages.users.password_hint') }}</p>
<p class="text-xs text-slate-500">{{ __('pages.users.employee_sync_hint') }}</p>
</div>
