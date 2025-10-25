import axios from 'axios';

// Configure axios defaults
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Add CSRF token from meta tag if it exists
const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
}

// Ensure credentials are included with requests
axios.defaults.withCredentials = true;

// Configure base URL to handle relative URLs properly
axios.defaults.baseURL = window.location.origin;

export default axios;