<script setup>
const props = defineProps({
    onlineUsers: {
        type: Array,
        default: () => []
    },
    members: {
        type: Array,
        default: () => []
    }
});

const isOnline = (userId) => {
    return props.onlineUsers.some(u => u.id === userId);
};

const sortedMembers = computed(() => {
    return [...props.members].sort((a, b) => {
        const aOnline = isOnline(a.id);
        const bOnline = isOnline(b.id);
        if (aOnline && !bOnline) return -1;
        if (!aOnline && bOnline) return 1;
        return 0;
    });
});

import { computed } from 'vue';
</script>

<template>
    <div class="online-users card border-0 shadow-sm mt-3">
        <div class="card-header bg-white py-2 border-bottom">
            <h6 class="mb-0 fw-bold small text-uppercase text-muted">Project Members</h6>
        </div>
        <div class="card-body p-2 online-members-scroll" style="max-height: 300px; overflow-y: auto;">
            <div v-for="member in sortedMembers" :key="member.id" class="d-flex align-items-center gap-2 p-2 rounded hover-bg-light transition-colors">
                <div class="position-relative">
                    <div class="avatar-xs rounded-circle bg-light d-flex align-items-center justify-content-center fw-bold text-muted small">
                        {{ member.avatar_initial }}
                    </div>
                    <span v-if="isOnline(member.id)" class="online-dot position-absolute bottom-0 end-0 rounded-circle border border-white"></span>
                </div>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="fw-semibold text-truncate small" :class="{ 'text-dark': isOnline(member.id), 'text-muted opacity-75': !isOnline(member.id) }">
                        {{ member.name }}
                    </div>
                    <div class="text-muted x-small">{{ member.role }}</div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.avatar-xs {
    width: 28px;
    height: 28px;
    font-size: 10px;
}

.online-dot {
    width: 8px;
    height: 8px;
    background-color: #22c55e;
}

.hover-bg-light:hover {
    background-color: #f8fafc;
}

.transition-colors {
    transition: background-color 0.2s;
}

.x-small {
    font-size: 0.7rem;
}

.online-members-scroll::-webkit-scrollbar {
    width: 4px;
}

.online-members-scroll::-webkit-scrollbar-track {
    background: transparent;
}

.online-members-scroll::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}

.online-members-scroll::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}
</style>
