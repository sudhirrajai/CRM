import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Laravel Echo — Real-time event broadcasting via Reverb WebSockets
 */
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

// Debug Echo connection issues
if (window.Echo && window.Echo.connector && window.Echo.connector.pusher) {
    console.log('[Echo] Initialized. Reverb host:', import.meta.env.VITE_REVERB_HOST, 'port:', import.meta.env.VITE_REVERB_PORT, 'scheme:', import.meta.env.VITE_REVERB_SCHEME);
    window.Echo.connector.pusher.connection.bind('state_change', (states) => {
        console.log('[Echo] Connection state changed:', states);
    });
    window.Echo.connector.pusher.connection.bind('error', (err) => {
        console.error('[Echo] Connection error:', err);
    });
}
