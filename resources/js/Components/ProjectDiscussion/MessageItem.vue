<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import AttachmentPreview from './AttachmentPreview.vue';
import MessageInput from './MessageInput.vue';

const props = defineProps({
    message: {
        type: Object,
        required: true
    },
    isReply: {
        type: Boolean,
        default: false
    },
    project: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['updated', 'deleted']);
const page = usePage();
const authUser = computed(() => page.props.auth.user);

const showReplyInput = ref(false);
const isEditing = ref(false);
const editMessage = ref(props.message.message);
const updating = ref(false);

const canEdit = computed(() => {
    if (props.message.user_id !== authUser.value.id) return false;
    const createdAt = new Date(props.message.created_at);
    const now = new Date();
    const diffMinutes = Math.floor((now - createdAt) / 60000);
    return diffMinutes <= 10;
});

const canDelete = computed(() => {
    return props.message.user_id === authUser.value.id || page.props.auth.roles.includes('admin');
});

const handleUpdate = async () => {
    updating.value = true;
    try {
        await axios.put(route('projects.discussions.update', [props.project.id, props.message.id]), {
            message: editMessage.value
        });
        isEditing.value = false;
        emit('updated');
    } catch (error) {
        alert(error.response?.data?.message || 'Error updating message');
    } finally {
        updating.value = false;
    }
};

const handleDelete = async () => {
    if (!confirm('Are you sure you want to delete this message?')) return;
    try {
        await axios.delete(route('projects.discussions.destroy', [props.project.id, props.message.id]));
        emit('deleted');
    } catch (error) {
        console.error('Error deleting message:', error);
    }
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleString(undefined, { 
        month: 'short', 
        day: 'numeric', 
        hour: '2-digit', 
        minute: '2-digit' 
    });
};

const handleReplySent = () => {
    showReplyInput.value = false;
    emit('updated');
};
</script>

<template>
    <div class="message-item d-flex gap-3 position-relative group" :class="{ 'reply-item': isReply }">
        <div class="avatar flex-shrink-0">
            <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px; font-size: 14px;">
                {{ message.user.name.charAt(0).toUpperCase() }}
            </div>
        </div>
        
        <div class="content-wrapper flex-grow-1">
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="fw-bold text-dark">{{ message.user.name }}</span>
                <span class="text-muted small">{{ formatDate(message.created_at) }}</span>
                <span v-if="message.is_edited" class="badge bg-light text-muted small border fw-normal">Edited</span>
            </div>
            
            <div v-if="!isEditing" class="message-text text-secondary mb-2 whitespace-pre-wrap">
                {{ message.message }}
            </div>
            
            <div v-else class="edit-area mb-2">
                <textarea v-model="editMessage" class="form-control form-control-sm mb-2" rows="3"></textarea>
                <div class="d-flex gap-2">
                    <button @click="handleUpdate" class="btn btn-sm btn-primary" :disabled="updating">Save Changes</button>
                    <button @click="isEditing = false" class="btn btn-sm btn-light">Cancel</button>
                </div>
            </div>

            <!-- Attachments -->
            <div v-if="message.attachments && message.attachments.length > 0" class="attachments-grid d-flex flex-wrap gap-2 mt-2">
                <AttachmentPreview v-for="attachment in message.attachments" :key="attachment.id" :attachment="attachment" :project="project" />
            </div>

            <!-- Actions -->
            <div class="actions d-flex gap-3 mt-1 small opacity-0 group-hover-opacity-100 transition-all">
                <a v-if="!isReply" href="javascript:void(0)" @click="showReplyInput = !showReplyInput" class="text-primary text-decoration-none">Reply</a>
                <a v-if="canEdit" href="javascript:void(0)" @click="isEditing = true" class="text-muted text-decoration-none">Edit</a>
                <a v-if="canDelete" href="javascript:void(0)" @click="handleDelete" class="text-danger text-decoration-none">Delete</a>
            </div>

            <!-- Reply Input -->
            <div v-if="showReplyInput" class="mt-3">
                <MessageInput :project="project" :parent-id="message.id" @sent="handleReplySent" />
            </div>
        </div>
    </div>
</template>

<style scoped>
.message-item {
    padding: 8px 12px;
    border-radius: 8px;
    transition: background-color 0.2s;
}

.message-item:hover {
    background-color: #f8fafc;
}

.whitespace-pre-wrap {
    white-space: pre-wrap;
}

.group-hover-opacity-100 {
    opacity: 0;
}

.message-item:hover .group-hover-opacity-100 {
    opacity: 1 !important;
}

.reply-item {
    background-color: transparent !important;
}

.avatar {
    margin-top: 4px;
}
</style>
