import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';

// Listen to public notifications
window.Echo.channel('public-notifications')
    .listen('SendPublicNotification', (e) => {
        console.log('Public notification received:', e);
        // Handle the notification here
        // You can show a toast, update UI, etc.
    });
