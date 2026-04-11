import axios from 'axios';
import { showGlobalLoader, hideGlobalLoader } from './Plugins/loader';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const token = document.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.getAttribute('content');
}

let activeRequests = 0;

window.axios.interceptors.request.use(config => {
    // Only show loader for POST, PUT, PATCH, DELETE (not GET or HEAD)
    if (config.method && !['get', 'head'].includes(config.method.toLowerCase())) {
        activeRequests++;
        let text = 'MEMPROSES DATA';
        // Allow custom text via headers or method heuristics
        if (config.headers['X-Loading-Text']) {
            text = config.headers['X-Loading-Text'];
        } else if (config.method.toLowerCase() === 'delete') {
            text = 'MENGHAPUS DATA';
        } else if (config.method.toLowerCase() === 'post') {
            text = 'MENYIMPAN DATA';
        }
        showGlobalLoader(text);
    }
    return config;
});

window.axios.interceptors.response.use(response => {
    if (response.config.method && !['get', 'head'].includes(response.config.method.toLowerCase())) {
        activeRequests--;
        if (activeRequests <= 0) {
            activeRequests = 0;
            hideGlobalLoader();
        }
    }
    return response;
}, error => {
    if (error.config && error.config.method && !['get', 'head'].includes(error.config.method.toLowerCase())) {
        activeRequests--;
        if (activeRequests <= 0) {
            activeRequests = 0;
            hideGlobalLoader();
        }
    }
    return Promise.reject(error);
});
