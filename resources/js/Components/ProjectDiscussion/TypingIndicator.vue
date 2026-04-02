<script setup>
import { computed } from 'vue';

const props = defineProps({
    users: {
        type: Array,
        default: () => []
    }
});

const typingText = computed(() => {
    if (props.users.length === 0) return '';
    if (props.users.length === 1) return `${props.users[0].name} is typing...`;
    if (props.users.length === 2) return `${props.users[0].name} and ${props.users[1].name} are typing...`;
    return `${props.users[0].name} and ${props.users.length - 1} others are typing...`;
});
</script>

<template>
    <div v-if="users.length > 0" class="typing-indicator d-flex align-items-center gap-2 px-3 py-1 text-muted small animate-fade-in">
        <div class="dots d-flex gap-1">
            <span class="dot"></span>
            <span class="dot highlight"></span>
            <span class="dot"></span>
        </div>
        <span>{{ typingText }}</span>
    </div>
</template>

<style scoped>
.typing-indicator {
    height: 30px;
}

.dots {
    display: inline-flex;
    align-items: center;
}

.dot {
    width: 4px;
    height: 4px;
    background-color: #cbd5e1;
    border-radius: 50%;
    animation: bounce 1.4s infinite ease-in-out both;
}

.dot:nth-child(1) { animation-delay: -0.32s; }
.dot:nth-child(2) { animation-delay: -0.16s; }

@keyframes bounce {
    0%, 80%, 100% { transform: scale(0); }
    40% { transform: scale(1); }
}

.animate-fade-in {
    animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
