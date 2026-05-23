self.addEventListener('push', function (event) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    const data = event.data?.json() ?? {};

    event.waitUntil(
        self.registration.showNotification(data.title || 'New Notification', {
            body: data.body || '',
            icon: data.icon || '/assets/images/favicon.png',
            badge: data.badge || '/assets/images/logo-sm.png',
            vibrate: data.vibrate || [100, 50, 100],
            tag: data.tag || undefined,
            renotify: data.renotify || false,
            data: data.data || {},
            actions: data.actions || [],
            requireInteraction: true,
        })
    );
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    const urlToOpen = event.notification.data.url;

    if (urlToOpen) {
        event.waitUntil(
            clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
                let matchingClient = null;

                for (let i = 0; i < windowClients.length; i++) {
                    const windowClient = windowClients[i];
                    if (windowClient.url === urlToOpen) {
                        matchingClient = windowClient;
                        break;
                    }
                }

                if (matchingClient) {
                    return matchingClient.focus();
                } else {
                    return clients.openWindow(urlToOpen);
                }
            })
        );
    }
});
