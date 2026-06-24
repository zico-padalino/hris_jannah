<div id="attendance-photo-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/70 p-4" onclick="closeAttendancePhotoModal(event)">
    <div class="relative max-h-[90vh] w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl" onclick="event.stopPropagation()">
        <div class="flex items-start justify-between border-b border-slate-100 px-5 py-4">
            <div>
                <h3 id="attendance-photo-title" class="text-lg font-semibold text-slate-900">Foto Absensi</h3>
                <p id="attendance-photo-meta" class="mt-1 text-sm text-slate-500"></p>
            </div>
            <button type="button" onclick="closeAttendancePhotoModal()" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600" aria-label="Tutup">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="bg-slate-900 p-4">
            <img id="attendance-photo-image" src="" alt="Foto absensi" class="mx-auto max-h-[60vh] w-full rounded-lg object-contain">
        </div>
        <div class="flex justify-end gap-2 border-t border-slate-100 px-5 py-4">
            <a id="attendance-photo-download" href="#" download class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Unduh</a>
            <button type="button" onclick="closeAttendancePhotoModal()" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white hover:bg-teal-800">Tutup</button>
        </div>
    </div>
</div>

<script>
    function openAttendancePhotoModal(button) {
        const modal = document.getElementById('attendance-photo-modal');
        const image = document.getElementById('attendance-photo-image');
        const title = document.getElementById('attendance-photo-title');
        const meta = document.getElementById('attendance-photo-meta');
        const download = document.getElementById('attendance-photo-download');

        image.src = button.dataset.photoUrl;
        title.textContent = button.dataset.photoTitle;
        meta.textContent = button.dataset.photoMeta;
        download.href = button.dataset.photoUrl;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    }

    function closeAttendancePhotoModal(event) {
        if (event && event.target !== event.currentTarget) {
            return;
        }

        const modal = document.getElementById('attendance-photo-modal');
        const image = document.getElementById('attendance-photo-image');

        modal.classList.add('hidden');
        modal.classList.remove('flex');
        image.src = '';
        document.body.classList.remove('overflow-hidden');
    }

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAttendancePhotoModal();
        }
    });
</script>
