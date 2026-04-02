<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import AttachmentPreview from './AttachmentPreview.vue';
import MessageInput from './MessageInput.vue';
import ReadReceipts from './ReadReceipts.vue';

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
    },
    members: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['updated', 'deleted', 'reply']);
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
        emit('deleted', { id: props.message.id, parent_id: props.message.parent_id });
    } catch (error) {
        console.error('Error deleting message:', error);
    }
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    const now = new Date();
    
    // Relative time for messages less than 1 day old
    const diff = Math.floor((now - date) / 1000);
    if (diff < 60) return 'just now';
    if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
    if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
    
    return date.toLocaleString(undefined, { 
        month: 'short', 
        day: 'numeric', 
        hour: '2-digit', 
        minute: '2-digit' 
    });
};

const handleReplySent = (newMessage) => {
    showReplyInput.value = false;
    emit('updated', newMessage);
};

// Mentions parsing logic
const renderedMessage = computed(() => {
    let msg = props.message.message || '';
    if (!props.message.mentions || props.message.mentions.length === 0) return msg;

    // Replace @username with span with styling
    // Simple regex matching @AnyName and checking against members
    // In a prod app, we'd store indices or use a library
    const mentionRegex = /@([a-zA-Z0-9\s]+)/g;
    return msg.replace(mentionRegex, (match, name) => {
        const trimmedName = name.trim();
        const member = props.members.find(m => m.name.startsWith(trimmedName));
        if (member) {
            return `<span class="mention-badge badge bg-primary-subtle text-primary border-primary border-0 rounded-pill px-2">@${trimmedName}</span>`;
        }
        return match;
    });
});
</script>

<template>
    <div class="message-wrapper animate-slide-in" :class="{ 'is-reply': isReply, 'is-me': message.user_id === authUser.id }">
        <div class="message-item d-flex gap-3 position-relative group" :class="{ 'flex-row-reverse': message.user_id === authUser.id }">
            
            <!-- Avatar -->
            <div class="avatar flex-shrink-0 z-1">
                <div 
                    class="avatar-circle rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm transition-all"
                    :class="message.user_id === authUser.id ? 'bg-primary text-white' : 'bg-white border text-muted'"
                >
                    {{ message.user.name.charAt(0).toUpperCase() }}
                </div>
            </div>
            
            <div class="content-container flex-grow-1" :style="{ textAlign: message.user_id === authUser.id ? 'right' : 'left' }">
                <!-- User name and timestamp -->
                <div class="d-flex align-items-center gap-2 mb-1 px-1" :class="{ 'flex-row-reverse': message.user_id === authUser.id }">
                    <span class="user-name fw-bold text-dark-emphasis">{{ message.user_id === authUser.id ? 'You' : message.user.name }}</span>
                    <span class="timestamp text-muted small opacity-75" :title="new Date(message.created_at).toLocaleString()">{{ formatDate(message.created_at) }}</span>
                    <span v-if="message.is_edited" class="edited-label badge bg-light text-muted border-0 fw-normal">edited</span>
                </div>
                
                <div class="message-bubble-row d-flex align-items-end gap-2" :class="{ 'flex-row-reverse': message.user_id === authUser.id }">
                    <!-- Message Bubble -->
                    <div v-if="!isEditing" class="message-bubble p-3 shadow-none border" :class="[
                        message.user_id === authUser.id 
                            ? 'bg-primary-subtle border-primary-subtle text-dark rounded-start-4 rounded-bottom-4' 
                            : 'bg-white border-light-subtle rounded-end-4 rounded-bottom-4 shadow-sm',
                        { 'rounded-4': true }
                    ]">
                        <!-- Mentioned-parsed text -->
                        <div class="message-content text-start text-break whitespace-pre-wrap" v-html="renderedMessage"></div>
                        
                        <!-- Attachments -->
                        <div v-if="message.attachments && message.attachments.length > 0" class="attachments-row d-flex flex-wrap gap-2 mt-2" :class="{ 'justify-content-end': message.user_id === authUser.id }">
                            <AttachmentPreview v-for="attachment in message.attachments" :key="attachment.id" :attachment="attachment" :project="project" />
                        </div>

                        <!-- Read Receipts (Inside bubble bottom right) -->
                        <ReadReceipts :message="message" :read-by="message.read_by || []" />
                    </div>
                    
                    <!-- Edit Window -->
                    <div v-else class="edit-bubble flex-grow-1 p-3 bg-white border border-primary rounded-4 shadow-sm text-start">
                        <textarea v-model="editMessage" class="form-control border-0 shadow-none p-0 bg-transparent" rows="3" placeholder="Edit your message..."></textarea>
                        <div class="d-flex gap-2 mt-2 justify-content-end">
                            <button @click="isEditing = false" class="btn btn-xs btn-link text-muted">Cancel</button>
                            <button @click="handleUpdate" class="btn btn-xs btn-primary rounded-pill px-3" :disabled="updating">Save Changes</button>
                        </div>
                    </div>

                    <!-- Actions hover menu -->
                    <div class="message-actions opacity-0 group-hover-opacity-100 transition-all d-flex gap-1">
                        <button v-if="!isReply && !isEditing" @click="showReplyInput = !showReplyInput" class="btn btn-icon btn-sm btn-light rounded-circle shadow-sm" title="Reply">
                            <i class="ti ti-arrow-back-up fs-5"></i>
                        </button>
                        <div class="btn-group shadow-sm rounded-pill overflow-hidden">
                            <button v-if="canEdit && !isEditing" @click="isEditing = true" class="btn btn-sm btn-light border-0" title="Edit"><i class="ti ti-pencil"></i></button>
                            <button v-if="canDelete && !isEditing" @click="handleDelete" class="btn btn-sm btn-light border-0 text-danger" title="Delete"><i class="ti ti-trash"></i></button>
                        </div>
                    </div>
                </div>

                <!-- Reply Input Inline -->
                <div v-if="showReplyInput" class="reply-input-wrapper mt-3 ms-2">
                    <MessageInput 
                        :project="project" 
                        :parent-id="message.id" 
                        :members="members"
                        :reply-to="message"
                        @sent="handleReplySent" 
                        @cancel-reply="showReplyInput = false"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.message-wrapper {
    margin-bottom: 1.5rem;
}

.is-reply {
    margin-top: -0.75rem;
    margin-left: 2.5rem;
}

.is-me.is-reply {
    margin-left: 0;
    margin-right: 2.5rem;
}

.avatar-circle {
    width: 40px;
    height: 40px;
    font-size: 14px;
}

.message-bubble {
    max-width: 80%;
    position: relative;
    font-size: 15px;
    line-height: 1.6;
}

.bg-primary-subtle {
    background-color: #f0f4ff !important;
    border-color: #dbeafe !important;
}

.message-content {
    word-break: break-word;
}

.whitespace-pre-wrap {
    white-space: pre-wrap;
}

.group-hover-opacity-100 {
    opacity: 0;
    pointer-events: none;
    transform: translateX(10px);
}

.message-item:hover .group-hover-opacity-100 {
    opacity: 1 !important;
    pointer-events: auto;
    transform: translateX(0);
}

.btn-icon {
    width: 32px;
    height: 32px;
}

.btn-xs {
    padding: 3px 12px;
    font-size: 12px;
}

.animate-slide-in {
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

:deep(.mention-badge) {
    font-weight: 600;
}

.timestamp {
    letter-spacing: -0.01em;
}

.user-name {
    font-size: 0.9rem;
}
</style>
