@if($leave->hasProof())
    <a href="{{ $leave->proof_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-teal-700 hover:underline">
        Lihat Bukti
    </a>
@else
    <span class="text-slate-400">—</span>
@endif
