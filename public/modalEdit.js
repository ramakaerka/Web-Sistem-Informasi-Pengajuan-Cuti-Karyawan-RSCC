document.addEventListener('DOMContentLoaded', function() {
const statusManager = document.getElementById('status_manager');
const validitasSuket = document.getElementById('validitas_suket');
const alasanPenolakan = document.getElementById('alasan_penolakan');

statusManager.addEventListener('change', () => {
    if (statusManager.value === 'rejected') {
        alasanPenolakan.style.display = 'block';
    } else {
        alasanPenolakan.style.display = 'none';
    }
});
validitasSuket.addEventListener('change', () => {
    if (validitasSuket.value === 'tdk_valid') {
        alasanPenolakan.style.display = 'block';
    } else {
        alasanPenolakan.style.display = 'none';
    }
});

});
