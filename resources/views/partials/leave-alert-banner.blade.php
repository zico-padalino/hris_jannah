@if(($count ?? 0) > 0)
    <div class="mb-6 flex flex-col gap-3 rounded-xl border border-amber-300 bg-gradient-to-r from-amber-50 to-orange-50 p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-3">
            <span class="relative mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-500 text-white shadow-md leave-badge-pulse">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white ring-2 ring-white">
                    {{ ($count ?? 0) > 99 ? '99+' : $count }}
                </span>
            </span>
            <div>
                <p class="font-semibold text-amber-900">{{ $title }}</p>
                <p class="mt-0.5 text-sm text-amber-800">{{ $message }}</p>
            </div>
        </div>
        <a href="{{ $href }}" class="inline-flex shrink-0 items-center justify-center rounded-lg bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-amber-700">
            {{ $buttonLabel ?? 'Lihat Sekarang' }}
        </a>
    </div>
@endif
