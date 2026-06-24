@push('scripts')
<script>
(function () {
    const pollUrl = @json($pollUrl);
    const intervalMs = {{ (int) config('attendance.fingerprint_auto_pull_seconds', 30) * 1000 }};
    let snapshot = @json($logSyncSnapshot);

    async function checkForUpdates() {
        try {
            const response = await fetch(pollUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (! response.ok) {
                return;
            }

            const data = await response.json();

            if (
                data.logs_count !== snapshot.logs_count
                || data.latest_log_id !== snapshot.latest_log_id
            ) {
                window.location.reload();
            }
        } catch (error) {
            // Abaikan error jaringan sementara.
        }
    }

    window.setInterval(checkForUpdates, intervalMs);
})();
</script>
@endpush
