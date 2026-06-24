@if($attendance->hasPhoto())
    <button
        type="button"
        class="group relative overflow-hidden rounded-lg ring-1 ring-slate-200 transition hover:ring-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-500"
        data-photo-url="{{ $attendance->photo_url }}"
        data-photo-title="Foto Absensi — {{ $attendance->employee->name }}"
        data-photo-meta="{{ $attendance->attended_at->format('d/m/Y H:i') }} · {{ $attendance->type->label() }}"
        onclick="openAttendancePhotoModal(this)"
        title="Lihat foto absensi"
    >
        <img
            src="{{ $attendance->photo_url }}"
            alt="Foto absensi {{ $attendance->employee->name }}"
            class="h-11 w-11 object-cover"
        >
        <span class="absolute inset-0 flex items-center justify-center bg-black/0 text-[10px] font-medium text-white transition group-hover:bg-black/40">
            <span class="opacity-0 group-hover:opacity-100">Lihat</span>
        </span>
    </button>
@else
    <span class="inline-flex h-11 w-11 items-center justify-center rounded-lg bg-slate-100 text-slate-400" title="Tidak ada foto">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
        </svg>
    </span>
@endif
