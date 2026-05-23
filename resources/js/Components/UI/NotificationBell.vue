<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';

const notifications = ref([]);
const unreadCount = ref(0);
const page = usePage();
const userId = page.props.auth.user.id;
let echoChannel = null;

const fetchNotifications = async () => {
    try {
        const { data } = await axios.get(route('notifications.index'));
        notifications.value = data.notifications;
        unreadCount.value = data.unread_count;
    } catch (e) {
        console.error('Failed to fetch notifications', e);
    }
};

const markAsRead = async (id, url) => {
    try {
        await axios.post(route('notifications.read', id));
        unreadCount.value = Math.max(0, unreadCount.value - 1);
        const notif = notifications.value.find(n => n.id === id);
        if (notif) notif.read_at = new Date().toISOString();
        if (url) window.location.href = url;
    } catch (e) {
        console.error('Failed to mark notification as read', e);
    }
};

const markAllAsRead = async () => {
    try {
        await axios.post(route('notifications.mark-all-read'));
        unreadCount.value = 0;
        notifications.value.forEach(n => {
            if (!n.read_at) n.read_at = new Date().toISOString();
        });
    } catch (e) {
        console.error('Failed to mark all as read', e);
    }
};

const subscribeToWebPush = async () => {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;

    try {
        const registration = await navigator.serviceWorker.register('/sw.js');
        const permission = await Notification.requestPermission();

        if (permission !== 'granted') return;

        const subscribeOptions = {
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(import.meta.env.VITE_VAPID_PUBLIC_KEY)
        };

        const subscription = await registration.pushManager.subscribe(subscribeOptions);

        await axios.post(route('push-subscriptions.store'), subscription.toJSON());
    } catch (e) {
        console.error('Failed to subscribe to web push', e);
    }
};

// Utility function for VAPID key
function urlBase64ToUint8Array(base64String) {
    if (!base64String) return new Uint8Array(0);
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

const setupEcho = () => {
    if (!window.Echo) return;
    
    echoChannel = window.Echo.private(`App.Models.User.${userId}`)
        .notification((notification) => {
            notifications.value.unshift({
                id: notification.id,
                data: notification,
                read_at: null,
                created_at: new Date().toISOString()
            });
            unreadCount.value++;
            
            // Limit to 50
            if (notifications.value.length > 50) {
                notifications.value.pop();
            }
        });
};

onMounted(() => {
    fetchNotifications();
    setupEcho();
    subscribeToWebPush();
});

onUnmounted(() => {
    if (window.Echo && echoChannel) {
        window.Echo.leave(`App.Models.User.${userId}`);
    }
});

const formatDate = (dateString) => {
    const d = new Date(dateString);
    return d.toLocaleDateString() + ' ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <div class="topbar-item">
        <div class="dropdown">
            <button class="topbar-link btn btn-outline-primary btn-icon dropdown-toggle drop-arrow-none" data-bs-toggle="dropdown" data-bs-offset="0,24" type="button" data-bs-auto-close="outside" aria-haspopup="false" aria-expanded="false">
                <i class="ti ti-bell fs-22" :class="{'animate-ring': unreadCount > 0}"></i>
                <span v-if="unreadCount > 0" class="noti-icon-badge badge bg-danger rounded-circle p-1">
                    <span class="visually-hidden">unread messages</span>
                </span>
            </button>

            <div class="dropdown-menu p-0 dropdown-menu-end dropdown-menu-lg" style="min-width: 320px;">
                <div class="p-3 border-bottom border-dashed d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fs-16 fw-semibold">Notifications</h6>
                    <button v-if="unreadCount > 0" @click="markAllAsRead" class="btn btn-sm btn-link p-0 text-decoration-none">
                        Mark all read
                    </button>
                </div>

                <div class="position-relative z-2 rounded-0" style="max-height: 350px; overflow-y: auto;">
                    <div v-if="notifications.length === 0" class="p-4 text-center text-muted">
                        No notifications found.
                    </div>
                    
                    <a v-for="notif in notifications" :key="notif.id" 
                       href="javascript:void(0);" 
                       @click.prevent="markAsRead(notif.id, notif.data.url)"
                       class="dropdown-item notify-item d-flex align-items-start p-3 border-bottom border-light"
                       :class="{'bg-light': !notif.read_at}">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar-sm">
                                <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-16">
                                    <i class="ti ti-message-circle"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 text-wrap">
                            <h6 class="m-0 fs-14 fw-semibold">{{ notif.data.title || 'New Notification' }}</h6>
                            <p class="text-muted mb-1 fs-13">{{ notif.data.body || 'You have a new message.' }}</p>
                            <small class="text-muted fs-12">{{ formatDate(notif.created_at) }}</small>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.noti-icon-badge {
    position: absolute;
    top: 6px;
    right: 6px;
    width: 8px;
    height: 8px;
}
.dropdown-menu {
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
.text-wrap {
    white-space: normal;
}
.bg-light {
    background-color: #f8f9fa !important;
}
</style>
