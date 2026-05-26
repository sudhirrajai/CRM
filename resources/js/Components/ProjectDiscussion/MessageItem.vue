<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import AttachmentPreview from './AttachmentPreview.vue';
import MessageInput from './MessageInput.vue';
import ReadReceipts from './ReadReceipts.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';

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
    },
    isGroup: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['updated', 'deleted', 'reply', 'scroll-to']);
const page = usePage();
const authUser = computed(() => page.props.auth.user);

const isEditing = ref(false);
const editMessage = ref(props.message.message);
const updating = ref(false);
const showDeleteModal = ref(false);
const isDeleting = ref(false);

const isMe = computed(() => props.message.user_id === authUser.value.id);

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
        const url = props.isGroup
            ? route('groups.discussions.update', [props.project.id, props.message.id])
            : route('projects.discussions.update', [props.project.id, props.message.id]);
        await axios.put(url, {
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

const handleDelete = () => {
    showDeleteModal.value = true;
};

const confirmDelete = async () => {
    isDeleting.value = true;
    try {
        const url = props.isGroup
            ? route('groups.discussions.destroy', [props.project.id, props.message.id])
            : route('projects.discussions.destroy', [props.project.id, props.message.id]);
        await axios.delete(url);
        showDeleteModal.value = false;
        emit('deleted', { id: props.message.id, parent_id: props.message.parent_id });
    } catch (error) {
        console.error('Error deleting message:', error);
    } finally {
        isDeleting.value = false;
    }
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    const now = new Date();
    
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

// Mentions parsing logic
const renderedMessage = computed(() => {
    let msg = props.message.message || '';
    
    const mentionRegex = /@([a-zA-Z0-9\s]+)/g;
    msg = msg.replace(mentionRegex, (match, name) => {
        const trimmedName = name.trim();
        const member = props.members.find(m => m.name.startsWith(trimmedName));
        if (member) {
            return `<span class="mention-badge badge bg-primary-subtle text-primary border-0 rounded-pill px-2">@${trimmedName}</span>`;
        }
        return match;
    });

    if (props.searchQuery && props.searchQuery.length >= 2) {
        const searchRegex = new RegExp(`(${props.searchQuery})`, 'gi');
        msg = msg.replace(searchRegex, '<mark class="search-highlight fw-bold bg-warning text-dark p-0 rounded-1">$1</mark>');
    }

    return msg;
});
</script>

<template>
    <div 
        :id="`msg-${message.id}`"
        class="message-wrapper animate-slide-in" 
        :class="{ 'is-me': isMe }"
    >
        <div class="message-item d-flex gap-2 position-relative group" :class="{ 'flex-row-reverse': isMe }">
            
            <!-- Avatar -->
            <div v-if="!isMe" class="avatar flex-shrink-0 align-self-end mb-1 d-none d-sm-block">
                <div 
                    class="avatar-circle rounded-circle d-flex align-items-center justify-content-center fw-bold bg-light text-muted border"
                    style="width: 32px; height: 32px; font-size: 11px;"
                >
                    {{ message.user.name.charAt(0).toUpperCase() }}
                </div>
            </div>
            
            <div class="content-container d-flex flex-column" :class="isMe ? 'align-items-end self' : 'align-items-start other'" style="max-width: 85%;">
                <!-- User name (Only for others) -->
                <div v-if="!isMe" class="user-name text-muted mb-1 px-2 fw-medium" style="font-size: 0.7rem;">
                    {{ message.user.name }}
                </div>
                
                <div class="message-bubble-row d-flex align-items-center gap-2 w-100" :class="{ 'flex-row-reverse': isMe }">
                    <!-- Message Bubble -->
                    <div v-if="!isEditing" class="message-bubble" :class="[
                        isMe 
                            ? 'bubble-self' 
                            : 'bubble-other'
                    ]">
                        <!-- Quoted Reply Context (WhatsApp-style) -->
                        <div 
                            v-if="message.parent" 
                            class="reply-quote cursor-pointer"
                            :class="isMe ? 'reply-quote-self' : 'reply-quote-other'"
                            @click="$emit('scroll-to', message.parent_id)"
                        >
                            <div class="reply-quote-name">{{ message.parent.user?.name }}</div>
                            <div class="reply-quote-text">{{ message.parent.message || '📎 Attachment' }}</div>
                        </div>

                        <!-- Message Text -->
                        <div class="message-content text-break whitespace-pre-wrap" v-html="renderedMessage"></div>
                        
                        <!-- Attachments -->
                        <div v-if="message.attachments && message.attachments.length > 0" class="attachments-row d-flex flex-wrap gap-2 mt-2">
                            <AttachmentPreview v-for="attachment in message.attachments" :key="attachment.id" :attachment="attachment" :project="project" :is-group="isGroup" />
                        </div>

                        <!-- Bottom Metadata -->
                        <div class="d-flex align-items-center justify-content-end gap-1 mt-1 message-meta">
                            <span class="timestamp">{{ formatDate(message.created_at) }}</span>
                            <span v-if="message.is_edited">· edited</span>
                            <ReadReceipts v-if="isMe" :message="message" :read-by="message.read_by || []" />
                        </div>
                    </div>
                    
                    <!-- Edit Window -->
                    <div v-else class="edit-bubble p-3 bg-white border rounded shadow-sm w-100">
                        <textarea v-model="editMessage" class="form-control form-control-sm border-0 shadow-none p-0 bg-transparent" rows="3"></textarea>
                        <div class="d-flex gap-2 mt-2 justify-content-end">
                            <button @click="isEditing = false" class="btn btn-xs btn-link text-muted px-2">Cancel</button>
                            <button @click="handleUpdate" class="btn btn-xs btn-primary px-3" :disabled="updating">Save</button>
                        </div>
                    </div>

                    <!-- Actions hover menu -->
                    <div class="message-actions opacity-0 group-hover-opacity-100 transition-all d-flex gap-1">
                        <button v-if="!isEditing" @click="$emit('reply', message)" class="btn btn-action btn-sm btn-light rounded-circle shadow-sm" title="Reply">
                            <i class="ti ti-arrow-back-up" style="font-size: 0.8rem;"></i>
                        </button>
                        <div class="btn-group shadow-sm rounded-pill overflow-hidden bg-white border">
                            <button v-if="canEdit && !isEditing" @click="isEditing = true" class="btn btn-sm btn-white border-0 py-1 px-2" title="Edit"><i class="ti ti-pencil" style="font-size: 0.75rem;"></i></button>
                            <button v-if="canDelete && !isEditing" @click="handleDelete" class="btn btn-sm btn-white border-0 py-1 px-2 text-danger" title="Delete"><i class="ti ti-trash" style="font-size: 0.75rem;"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <ConfirmationModal
            :show="showDeleteModal"
            title="Delete Message"
            message="Are you sure you want to delete this message?"
            confirm-text="Delete"
            :processing="isDeleting"
            @close="showDeleteModal = false"
            @confirm="confirmDelete"
        />
    </div>
</template>

<style scoped>
.message-wrapper {
    margin-bottom: 0.35rem;
    padding: 2px 0.5rem;
    border-radius: 8px;
    transition: background-color 0.5s ease;
}

.message-bubble {
    max-width: 80%;
    padding: 0.5rem 0.75rem;
    position: relative;
    line-height: 1.5;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

/* Self (my messages) - WhatsApp-style teal/green tint */
.bubble-self {
    background-color: var(--bs-primary, #3e60d5);
    color: #ffffff;
    border-radius: 0.75rem 0.75rem 0.2rem 0.75rem;
    box-shadow: 0 1px 4px rgba(62, 96, 213, 0.12);
}

/* Other (received messages) - clean white */
.bubble-other {
    background-color: #ffffff;
    color: #343a40;
    border-radius: 0.75rem 0.75rem 0.75rem 0.2rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    border: 1px solid #eef2f7;
}

/* ===========================
   REPLY QUOTE (WhatsApp-style)
   =========================== */
.reply-quote {
    padding: 6px 10px;
    margin-bottom: 6px;
    border-radius: 6px;
    border-left: 3px solid;
    cursor: pointer;
    transition: filter 0.15s ease;
    overflow: hidden;
}

.reply-quote:hover {
    filter: brightness(0.95);
}

/* Quote inside my bubble (light overlay on primary bg) */
.reply-quote-self {
    background-color: rgba(255, 255, 255, 0.12);
    border-left-color: rgba(255, 255, 255, 0.5);
}

.reply-quote-self .reply-quote-name {
    color: rgba(255, 255, 255, 0.9);
}

.reply-quote-self .reply-quote-text {
    color: rgba(255, 255, 255, 0.7);
}

/* Quote inside other's bubble (subtle tint) */
.reply-quote-other {
    background-color: #f0f4ff;
    border-left-color: var(--bs-primary, #3e60d5);
}

.reply-quote-other .reply-quote-name {
    color: var(--bs-primary, #3e60d5);
}

.reply-quote-other .reply-quote-text {
    color: #6c757d;
}

.reply-quote-name {
    font-size: 0.7rem;
    font-weight: 600;
    margin-bottom: 1px;
}

.reply-quote-text {
    font-size: 0.72rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 280px;
}

/* ===========================
   MESSAGE METADATA
   =========================== */
.message-meta {
    font-size: 10px;
    opacity: 0.6;
}

/* Actions */
.message-item:hover .message-actions {
    opacity: 1 !important;
    transform: translateX(0);
}

.message-actions {
    transform: translateX(5px);
    transition: all 0.2s ease;
}

.btn-action {
    width: 26px;
    height: 26px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
}

.btn-xs {
    padding: 2px 8px;
    font-size: 11px;
}

.whitespace-pre-wrap {
    white-space: pre-wrap;
}

.transition-all {
    transition: all 0.2s ease;
}

.animate-slide-in {
    animation: slideIn 0.2s ease-out;
}

@keyframes slideIn {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Mentions */
:deep(.mention-badge) {
    background-color: rgba(62, 96, 213, 0.1) !important;
    color: var(--bs-primary, #3e60d5) !important;
    font-weight: 600;
    font-size: 0.8em;
}

.self :deep(.mention-badge) {
    background-color: rgba(255, 255, 255, 0.2) !important;
    color: white !important;
}

/* Search highlights */
:deep(.search-highlight) {
    background-color: #fef08a !important;
    color: #1e293b !important;
    padding: 0 1px;
    border-radius: 2px;
    box-shadow: 0 0 0 1px rgba(234, 179, 8, 0.2);
}

.is-me :deep(.search-highlight) {
    background-color: #fde047 !important;
    box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.4);
}

.cursor-pointer {
    cursor: pointer;
}
</style>
