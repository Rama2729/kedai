
document.addEventListener('DOMContentLoaded', function () {

    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('open');
        });
    }

    const datetimeEl = document.getElementById('navbarDatetime');
    if (datetimeEl) {
        function updateDatetime() {
            const now = new Date();
            const hari = ['Minggu','Senin','Selasa','Rabu','Kamis',"Jum'at",'Sabtu'][now.getDay()];
            const bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][now.getMonth()];
            const jam = String(now.getHours()).padStart(2, '0');
            const menit = String(now.getMinutes()).padStart(2, '0');
            const detik = String(now.getSeconds()).padStart(2, '0');
            datetimeEl.textContent = `${hari}, ${now.getDate()} ${bulan} ${now.getFullYear()} - ${jam}:${menit}:${detik}`;
        }
        updateDatetime();
        setInterval(updateDatetime, 1000);
    }

    const alertEl = document.querySelector('.alert');
    if (alertEl) {
        setTimeout(function () {
            alertEl.style.transition = 'opacity 0.4s ease';
            alertEl.style.opacity = '0';
            setTimeout(() => alertEl.remove(), 400);
        }, 4000);
    }

});

function formatRupiah(angka) {
    return 'Rp ' + Number(angka).toLocaleString('id-ID');
}
