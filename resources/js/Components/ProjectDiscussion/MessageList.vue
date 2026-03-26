<script setup>
import MessageItem from './MessageItem.vue';

defineProps({
    discussions: {
        type: Array,
        required: true
    },
    project: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['message-updated', 'message-deleted']);
</script>

<template>
    <div class="message-list">
        <div v-for="message in discussions" :key="message.id" class="mb-4">
            <MessageItem 
                :message="message" 
                :project="project" 
                @updated="emit('message-updated')" 
                @deleted="emit('message-deleted')"
            />
            
            <!-- Replies -->
            <div v-if="message.replies && message.replies.length > 0" class="ms-5 mt-2 ps-3 border-start">
                <MessageItem 
                    v-for="reply in message.replies" 
                    :key="reply.id" 
                    :message="reply" 
                    :is-reply="true"
                    :project="project"
                    @updated="emit('message-updated')" 
                    @deleted="emit('message-deleted')"
                />
            </div>
        </div>
    </div>
</template>
