<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';

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


</script>
<template>
    <div v-if="filteredMembers.length > 0" class="mention-dropdown shadow-lg border rounded-3 bg-white overflow-hidden p-1">
        <div class="p-2 border-bottom bg-light bg-opacity-50 mb-1 d-flex justify-content-between align-items-center">
            <span class="fw-bold x-small text-muted text-uppercase letter-spacing-1">Mention Someone</span>
            <button @click="$emit('close')" class="btn btn-link btn-sm p-0 text-muted d-sm-none"><i class="ti ti-x"></i></button>
        </div>
        <div class="mention-list custom-scrollbar">
            <div 
                v-for="(member, index) in filteredMembers" 
                :key="member.id"
                class="member-item d-flex align-items-center gap-2 p-2 rounded cursor-pointer"
                :class="{ 'bg-primary-subtle text-primary fw-bold': index === selectedIndex }"
                @click="selectMember(member)"
                @mouseenter="selectedIndex = index"
            >
                <div class="avatar-sm rounded-circle bg-light d-flex align-items-center justify-content-center fw-bold text-muted small border">
                    {{ member.avatar_initial }}
                </div>
                <div class="flex-grow-1 overflow-hidden text-start">
                    <div class="text-truncate small">{{ member.name }}</div>
                    <div class="text-muted x-small text-truncate text-uppercase">{{ member.role }}</div>
                </div>
            </div>
        </div>
    </div>
    <div v-else-if="search" class="mention-dropdown shadow-lg border rounded-3 bg-white p-3 text-center">
        <div class="text-muted small">No members found matching "{{ search }}"</div>
    </div>
</template>

<style scoped>
.mention-dropdown {
    position: absolute;
    bottom: 100%;
    left: 0;
    width: 250px;
    max-height: 280px;
    z-index: 2000 !important;
    margin-bottom: 20px;
    animation: slideUp 0.2s ease-out;
}

.mention-list {
    max-height: 220px;
    overflow-y: auto;
}

.member-item {
    transition: all 0.2s;
}

.letter-spacing-1 {
    letter-spacing: 0.05rem;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(15px) scale(0.98); }
    to { opacity: 1; transform: translateY(0) scale(1); }
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
