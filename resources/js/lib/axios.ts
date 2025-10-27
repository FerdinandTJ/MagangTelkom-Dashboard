import axios from 'axios';

// Create axios instance with proper configuration
const axiosInstance = axios.create({
    baseURL: window.location.origin,
    withCredentials: true,
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    },
});

// Function to get CSRF token from meta tag or cookie
const getCsrfToken = (): string | null => {
    // Try to get from meta tag first
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    if (metaTag) {
        return metaTag.getAttribute('content');
    }
    
    // Try to get from cookie (for API routes)
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    if (match) {
        return decodeURIComponent(match[1]);
    }
    
    return null;
};

// Add request interceptor to include CSRF token
axiosInstance.interceptors.request.use(
    (config) => {
        const token = getCsrfToken();
        if (token) {
            config.headers['X-CSRF-TOKEN'] = token;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Add response interceptor to handle 419 errors (CSRF token mismatch)
axiosInstance.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 419) {
            console.error('CSRF token mismatch. Reloading page to get fresh token...');
            // Reload the page to get fresh CSRF token
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }
        return Promise.reject(error);
    }
);

export default axiosInstance;