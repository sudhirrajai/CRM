<script setup>
import { computed } from 'vue';
import MessageItem from './MessageItem.vue';

const props = defineProps({
    discussions: {
        type: Array,
        required: true
    },
    project: {
        type: Object,
        required: true
    },
    members: {
        type: Array,
        default: () => []
    },
    lastReadId: {
        type: String,
        default: null
    },
    searchQuery: {
        type: String,
        default: ''
    },
    isGroup: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['message-updated', 'message-deleted', 'reply']);

// Grouping messages by date
const groupedDiscussions = computed(() => {
    const groups = [];
    let lastDate = null;

    props.discussions.forEach(message => {
        const date = new Date(message.created_at).toLocaleDateString(undefined, { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });

        if (date !== lastDate) {
            groups.push({ type: 'date', value: getFriendlyDate(message.created_at), date: date });
            lastDate = date;
        }
        
        groups.push({ type: 'message', value: message });
    });

    return groups;
});

const getFriendlyDate = (dateString) => {
    const date = new Date(dateString);
    const today = new Date();
    const yesterday = new Date();
    yesterday.setDate(today.getDate() - 1);

    if (date.toLocaleDateString() === today.toLocaleDateString()) return 'Today';
    if (date.toLocaleDateString() === yesterday.toLocaleDateString()) return 'Yesterday';
    
    return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
};

// Check if a message is unread
const isUnread = (message) => {
    if (!props.lastReadId) return true;
    const lastReadIdx = props.discussions.findIndex(d => d.id === props.lastReadId);
    const currentIdx = props.discussions.findIndex(d => d.id === message.id);
    return currentIdx > lastReadIdx;
};

// Find the first unread message to place the divider
const firstUnreadId = computed(() => {
    if (!props.lastReadId) return null;
    const lastReadIdx = props.discussions.findIndex(d => d.id === props.lastReadId);
    if (lastReadIdx === -1 || lastReadIdx === props.discussions.length - 1) return null;
    return props.discussions[lastReadIdx + 1]?.id;
});

// Smooth scroll to a specific message with pulse animation
const scrollToMessage = (messageId) => {
    const el = document.getElementById(`msg-${messageId}`);
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        el.classList.add('highlight-pulse');
        setTimeout(() => el.classList.remove('highlight-pulse'), 2000);
    }
};
</script>

<template>
    <div class="message-list-container">
        <div v-for="(item, index) in groupedDiscussions" :key="index">
            <!-- Date Separator -->
            <div v-if="item.type === 'date'" class="date-separator d-flex align-items-center justify-content-center my-4">
                <div class="line flex-grow-1 border-top opacity-10"></div>
                <span class="date-badge mx-3 py-1 px-3 rounded-pill bg-white text-indigo fw-bold small shadow-sm border border-light-subtle">{{ item.value }}</span>
                <div class="line flex-grow-1 border-top opacity-10"></div>
            </div>

            <!-- Message Item -->
            <div v-else class="message-group">
                <!-- Unread Divider -->
                <div v-if="firstUnreadId === item.value.id" class="unread-divider d-flex align-items-center my-4">
                    <div class="line flex-grow-1 border-top border-danger border-2 opacity-25"></div>
                    <span class="mx-3 text-danger fw-bold small text-uppercase letter-spacing-2">New Messages</span>
                    <div class="line flex-grow-1 border-top border-danger border-2 opacity-25"></div>
                </div>

                <MessageItem 
                    :message="item.value" 
                    :project="project" 
                    :members="members"
                    :search-query="searchQuery"
                    :is-group="isGroup"
                    @updated="emit('message-updated')" 
                    @deleted="emit('message-deleted')"
                    @reply="emit('reply', $event)"
                    @scroll-to="scrollToMessage($event)"
                />
            </div>
        </div>
    </div>
</template>

<style scoped>
.date-badge {
    font-size: 0.7rem;
    letter-spacing: 0.05em;
}

.text-indigo { color: #4c49e2 !important; }
.border-indigo-subtle { border-color: rgba(76, 73, 226, 0.2) !important; }

.unread-divider span {
    font-size: 0.65rem;
    letter-spacing: 0.15em;
}

.letter-spacing-2 {
    letter-spacing: 0.15em;
}

.message-list-container {
    padding-bottom: 20px;
}

/* Pulse highlight animation for scroll-to-message */
:deep(.highlight-pulse) {
    animation: highlightPulse 2s ease-out;
}

@keyframes highlightPulse {
    0% { background-color: rgba(62, 96, 213, 0.15); }
    30% { background-color: rgba(62, 96, 213, 0.2); }
    100% { background-color: transparent; }
}
</style>
