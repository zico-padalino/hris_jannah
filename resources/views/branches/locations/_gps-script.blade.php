<script>
document.getElementById('btn-current-location')?.addEventListener('click', () => {
    if (!navigator.geolocation) {
        alert('Browser tidak mendukung geolocation.');
        return;
    }
    navigator.geolocation.getCurrentPosition((pos) => {
        document.getElementById('loc-latitude').value = pos.coords.latitude.toFixed(7);
        document.getElementById('loc-longitude').value = pos.coords.longitude.toFixed(7);
    }, () => alert('Gagal mengambil lokasi. Izinkan akses GPS.'));
});
</script>
