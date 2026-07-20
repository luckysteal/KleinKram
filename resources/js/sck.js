import axios from 'axios';
import Alpine from 'alpinejs';
import QRious from 'qrious';
import './modal-drag-guard';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Set up CSRF token in Axios from meta tag if available
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

window.QRious = QRious;
window.Alpine = Alpine;
Alpine.start();

