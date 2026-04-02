<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    message: {
        type: Object,
        required: true
    },
    readBy: {
        type: Array,
        default: () => []
    }
});

const page = usePage();
const authUser = computed(() => page.props.auth.user);

// Filter out the sender themselves from the read receipts list
const readers = computed(() => {
    return props.readBy.filter(u => u.id !== authUser.value.id);
});
</script>

<template>
    <div v-if="message.user_id === authUser.id" class="read-receipts-container d-flex align-items-center gap-1 mt-1 justify-content-end">
        <!-- Visual indicators for status -->
        <div class="status-ticks me-1" :class="{ 'text-primary': readers.length > 0, 'text-muted': readers.length === 0 }">
            <i v-if="readers.length > 0" class="ti ti-checks fs-6" title="Read"></i>
            <i v-else class="ti ti-check fs-6" title="Sent"></i>
        </div>

        <div v-if="readers.length > 0" class="avatar-stack d-flex align-items-center">
            <div 
                v-for="(reader, index) in readers.slice(0, 3)" 
                :key="reader.id"
                class="avatar-reader rounded-circle border border-white bg-light d-flex align-items-center justify-content-center fw-bold"
                :style="{ zIndex: 10 - index, marginLeft: index > 0 ? '-8px' : '0' }"
                :title="`Read by ${reader.name}`"
            >
                {{ reader.name.charAt(0).toUpperCase() }}
            </div>
            <div v-if="readers.length > 3" class="extra-count ms-1 text-muted x-small">+{{ readers.length - 3 }}</div>
        </div>
    </div>
</template>

<style scoped>
.read-receipts-container {
    height: 16px;
    animation: fadeIn 0.5s ease-out;
}

.avatar-reader {
    width: 14px;
    height: 14px;
    font-size: 8px;
    color: #475569;
}

.x-small {
    font-size: 0.65rem;
}

.status-ticks {
    display: flex;
    align-items: center;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
</style>
