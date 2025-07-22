import './bootstrap';
import toastr from 'toastr';

// Inisialisasi Toastr (opsional)
toastr.options = {
  positionClass: 'toast-top-right',
  preventDuplicates: true,
  progressBar: true,
  timeOut: 20000,
  extendedTimeOut: 5000,
  closeButton: true,
  newestOnTop: true,
  escapeHtml: false
};


console.log('Toastr telah di-load!');