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

const readTooltip = computed(() => {
    if (readers.value.length === 0) return 'Sent';
    const names = readers.value.slice(0, 3).map(r => r.name).join(', ');
    const extra = readers.value.length > 3 ? ` +${readers.value.length - 3} more` : '';
    return `Read by ${names}${extra}`;
});
</script>

<template>
    <span v-if="message.user_id === authUser.id" class="read-receipt-ticks" :title="readTooltip">
        <svg v-if="readers.length > 0" width="16" height="11" viewBox="0 0 16 11" fill="none" class="ticks-read">
            <path d="M11.071 0.929L4.5 7.5L1.929 4.929" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M14.071 0.929L7.5 7.5L6.5 6.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <svg v-else width="12" height="9" viewBox="0 0 12 9" fill="none" class="ticks-sent">
            <path d="M10.071 0.929L3.5 7.5L0.929 4.929" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </span>
</template>

<style scoped>
.read-receipt-ticks {
    display: inline-flex;
    align-items: center;
    margin-left: 3px;
    line-height: 1;
}

.ticks-read {
    color: #a5f3fc;
}

.ticks-sent {
    color: rgba(255, 255, 255, 0.55);
}
</style>
