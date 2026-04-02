<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    members: {
        type: Array,
        required: true
    },
    search: {
        type: String,
        default: ''
    }
});

const emit = defineEmits(['select', 'close']);

const selectedIndex = ref(0);

const filteredMembers = computed(() => {
    if (!props.search) return props.members;
    const query = props.search.toLowerCase();
    return props.members.filter(m => 
        m.name.toLowerCase().includes(query) || 
        m.email.toLowerCase().includes(query)
    );
});

const selectMember = (member) => {
    emit('select', member);
};

const handleKeyDown = (e) => {
    if (e.key === 'ArrowDown') {
        selectedIndex.value = (selectedIndex.value + 1) % filteredMembers.value.length;
        e.preventDefault();
    } else if (e.key === 'ArrowUp') {
        selectedIndex.value = (selectedIndex.value - 1 + filteredMembers.value.length) % filteredMembers.value.length;
        e.preventDefault();
    } else if (e.key === 'Enter') {
        if (filteredMembers.value[selectedIndex.value]) {
            selectMember(filteredMembers.value[selectedIndex.value]);
            e.preventDefault();
        }
    } else if (e.key === 'Escape') {
        emit('close');
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
});

import { computed } from 'vue';
</script>

<template>
    <div v-if="filteredMembers.length > 0" class="mention-dropdown shadow-lg border rounded-3 bg-white overflow-hidden z-3 p-1">
        <div 
            v-for="(member, index) in filteredMembers" 
            :key="member.id"
            class="member-item d-flex align-items-center gap-2 p-2 rounded cursor-pointer"
            :class="{ 'bg-primary-subtle text-primary': index === selectedIndex }"
            @click="selectMember(member)"
            @mouseenter="selectedIndex = index"
        >
            <div class="avatar-sm rounded-circle bg-light d-flex align-items-center justify-content-center fw-bold text-muted small">
                {{ member.avatar_initial }}
            </div>
            <div class="flex-grow-1 overflow-hidden">
                <div class="fw-semibold text-truncate small">{{ member.name }}</div>
                <div class="text-muted x-small text-truncate">{{ member.role }}</div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.mention-dropdown {
    position: absolute;
    bottom: 100%;
    left: 0;
    min-width: 200px;
    max-height: 250px;
    overflow-y: auto;
    margin-bottom: 5px;
}

.member-item {
    transition: all 0.2s;
}

.avatar-sm {
    width: 28px;
    height: 28px;
    font-size: 10px;
}

.x-small {
    font-size: 0.75rem;
}

.cursor-pointer {
    cursor: pointer;
}
</style>
