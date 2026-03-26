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
    <div class="message-wrapper" :class="{ 'is-reply': isReply, 'is-me': message.user_id === authUser.id }">
        <div class="message-item d-flex gap-2 position-relative group" :class="{ 'flex-row-reverse': message.user_id === authUser.id }">
            <!-- Thread Line for Replies -->
            <div v-if="isReply" class="thread-line" :class="{ 'on-right': message.user_id === authUser.id }"></div>

            <div class="avatar flex-shrink-0 z-1">
                <div class="avatar-circle bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm">
                    {{ message.user.name.charAt(0).toUpperCase() }}
                </div>
            </div>
            
            <div class="content-container flex-grow-1 overflow-hidden" :style="{ textAlign: message.user_id === authUser.id ? 'right' : 'left' }">
                <div class="d-flex align-items-center gap-2 mb-1 px-1" :class="{ 'flex-row-reverse': message.user_id === authUser.id }">
                    <span class="user-name fw-semibold text-dark">{{ message.user.name }}</span>
                    <span class="timestamp text-muted small">{{ formatDate(message.created_at) }}</span>
                    <span v-if="message.is_edited" class="edited-label badge bg-light text-muted border-0 fw-normal">edited</span>
                </div>
                
                <div class="message-bubble-container d-flex align-items-end gap-2" :class="{ 'flex-row-reverse': message.user_id === authUser.id }">
                    <div v-if="!isEditing" class="message-bubble p-3 shadow-sm" :class="[
                        message.user_id === authUser.id ? 'bg-primary text-white border-0 rounded-start-4 rounded-bottom-4 shadow-primary-sm' : 'bg-white border rounded-end-4 rounded-bottom-4',
                        { 'rounded-4': true }
                    ]">
                        <div class="message-content whitespace-pre-wrap text-start text-break">{{ message.message }}</div>
                        
                        <!-- Attachments inside bubble or right below -->
                        <div v-if="message.attachments && message.attachments.length > 0" class="attachments-row d-flex flex-wrap gap-2 mt-2" :class="{ 'justify-content-end': message.user_id === authUser.id }">
                            <AttachmentPreview v-for="attachment in message.attachments" :key="attachment.id" :attachment="attachment" :project="project" />
                        </div>
                    </div>
                    
                    <div v-else class="edit-bubble flex-grow-1 p-3 bg-white border rounded-4 shadow-sm text-start">
                        <textarea v-model="editMessage" class="form-control border-0 shadow-none p-0 bg-transparent" rows="3" placeholder="Edit your message..."></textarea>
                        <div class="d-flex gap-2 mt-2 justify-content-end">
                            <button @click="handleUpdate" class="btn btn-xs btn-primary rounded-pill" :disabled="updating">Save</button>
                            <button @click="isEditing = false" class="btn btn-xs btn-light rounded-pill">Cancel</button>
                        </div>
                    </div>

                    <!-- Actions hover menu -->
                    <div class="message-actions opacity-0 group-hover-opacity-100 transition-all d-flex gap-1">
                        <button v-if="!isReply" @click="showReplyInput = !showReplyInput" class="btn btn-icon btn-sm btn-light rounded-circle shadow-sm" title="Reply"><i class="ti ti-arrow-back-up"></i></button>
                        <button v-if="canEdit" @click="isEditing = true" class="btn btn-icon btn-sm btn-light rounded-circle shadow-sm" title="Edit"><i class="ti ti-pencil"></i></button>
                        <button v-if="canDelete" @click="handleDelete" class="btn btn-icon btn-sm btn-light rounded-circle shadow-sm text-danger" title="Delete"><i class="ti ti-trash"></i></button>
                    </div>
                </div>

                <!-- Reply Input -->
                <div v-if="showReplyInput" class="reply-input-wrapper mt-3 ms-2 ps-3 border-start border-2 border-primary-subtle text-start">
                    <MessageInput :project="project" :parent-id="message.id" @sent="handleReplySent" />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.message-wrapper {
    margin-bottom: 24px;
}

.is-reply {
    margin-top: -12px;
    margin-left: 50px;
}

.is-me.is-reply {
    margin-left: 0;
    margin-right: 50px;
}

.message-item {
    transition: all 0.2s ease;
}

.thread-line {
    position: absolute;
    left: -25px;
    top: -20px;
    bottom: 20px;
    width: 2px;
    background-color: #e2e8f0;
    z-index: 0;
}

.thread-line.on-right {
    left: auto;
    right: -25px;
}

.thread-line::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: 0px;
    width: 20px;
    height: 2px;
    background-color: #e2e8f0;
}

.thread-line.on-right::after {
    left: auto;
    right: 0;
}

.avatar-circle {
    width: 38px;
    height: 38px;
    font-size: 14px;
    border: 3px solid #fff;
}

.content-container {
    max-width: calc(100% - 48px);
}

.user-name {
    font-size: 14px;
}

.timestamp {
    font-size: 11px;
}

.edited-label {
    font-size: 10px;
    background: transparent !important;
}

.message-bubble {
    max-width: 85%;
    position: relative;
    font-size: 14px;
    line-height: 1.6;
}

.shadow-primary-sm {
    box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.1), 0 2px 4px -1px rgba(99, 102, 241, 0.06);
}

.bg-primary {
    background-color: #6366f1 !important; /* Premium Modern Indigo */
}

.message-content {
    word-break: break-word;
}

.whitespace-pre-wrap {
    white-space: pre-wrap;
}

.message-actions {
    margin-bottom: 5px;
}

.btn-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
}

.btn-xs {
    padding: 3px 12px;
    font-size: 12px;
}

.z-1 {
    z-index: 1;
}

.group-hover-opacity-100 {
    opacity: 0;
    pointer-events: none;
}

.message-item:hover .group-hover-opacity-100 {
    opacity: 1 !important;
    pointer-events: auto;
}

.reply-input-wrapper {
    animation: slideDown 0.2s ease-out;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-5px); }
    to { opacity: 1; transform: translateY(0); }
}

.text-start {
    text-align: left !important;
}
</style>
