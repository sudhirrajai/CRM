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
    }
});

const emit = defineEmits(['updated', 'deleted', 'reply']);
const page = usePage();
const authUser = computed(() => page.props.auth.user);

const isEditing = ref(false);
const editMessage = ref(props.message.message);
const updating = ref(false);
const showDeleteModal = ref(false);
const isDeleting = ref(false);

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

const handleDelete = () => {
    showDeleteModal.value = true;
};

const confirmDelete = async () => {
    isDeleting.value = true;
    try {
        await axios.delete(route('projects.discussions.destroy', [props.project.id, props.message.id]));
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
    <div class="message-wrapper animate-slide-in" :class="{ 'is-me': message.user_id === authUser.id, 'is-reply': isReply }">
        <div class="message-item d-flex gap-2 position-relative group" :class="{ 'flex-row-reverse': message.user_id === authUser.id }">
            
            <!-- Avatar -->
            <div v-if="message.user_id !== authUser.id" class="avatar flex-shrink-0 align-self-end mb-1 d-none d-sm-block">
                <div 
                    class="avatar-circle rounded-circle d-flex align-items-center justify-content-center fw-bold bg-light text-muted border"
                    style="width: 32px; height: 32px; font-size: 11px;"
                >
                    {{ message.user.name.charAt(0).toUpperCase() }}
                </div>
            </div>
            
            <div class="content-container d-flex flex-column" :class="message.user_id === authUser.id ? 'align-items-end self' : 'align-items-start other'" style="max-width: 85%;">
                <!-- User name (Only for others) -->
                <div v-if="message.user_id !== authUser.id && !isReply" class="user-name text-muted mb-1 px-2 fw-medium" style="font-size: 0.7rem;">
                    {{ message.user.name }}
                </div>
                
                <div class="message-bubble-row d-flex align-items-center gap-2 w-100" :class="{ 'flex-row-reverse': message.user_id === authUser.id }">
                    <!-- Message Bubble -->
                    <div v-if="!isEditing" class="message-bubble" :class="[
                        message.user_id === authUser.id 
                            ? 'bubble-self' 
                            : 'bubble-other'
                    ]">
                        <!-- Reply Context In Bubble -->
                        <div v-if="message.parent" class="reply-context-bubble mb-2 p-2 rounded border-start border-3 border-primary small cursor-pointer" @click="$emit('scroll-to', message.parent_id)"
                             :class="message.user_id === authUser.id ? 'bg-white bg-opacity-10' : 'bg-light'">
                           <div class="fw-bold opacity-75" :class="message.user_id === authUser.id ? 'text-white' : 'text-primary'" style="font-size: 0.7rem;">{{ message.parent.user?.name }}</div>
                           <div class="text-truncate opacity-75" :class="message.user_id === authUser.id ? 'text-white' : 'text-muted'" style="font-size: 0.7rem;">{{ message.parent.message }}</div>
                        </div>

                        <!-- Message Text -->
                        <div class="message-content text-break whitespace-pre-wrap" v-html="renderedMessage"></div>
                        
                        <!-- Attachments -->
                        <div v-if="message.attachments && message.attachments.length > 0" class="attachments-row d-flex flex-wrap gap-2 mt-2">
                            <AttachmentPreview v-for="attachment in message.attachments" :key="attachment.id" :attachment="attachment" :project="project" />
                        </div>

                        <!-- Bottom Metadata -->
                        <div class="d-flex align-items-center justify-content-end gap-1 mt-1 opacity-60" style="font-size: 10px;">
                            <span class="timestamp">{{ formatDate(message.created_at) }}</span>
                            <span v-if="message.is_edited">· edited</span>
                            <ReadReceipts v-if="message.user_id === authUser.id" :message="message" :read-by="message.read_by || []" />
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
    margin-bottom: 0.6rem;
    padding: 0 0.5rem;
}

.message-bubble {
    max-width: 80%;
    padding: 0.75rem 1rem;
    position: relative;
    line-height: 1.5;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

/* Self (my messages) - use admin primary color */
.bubble-self {
    background-color: var(--bs-primary, #3e60d5);
    color: #ffffff;
    border-radius: 0.75rem 0.75rem 0.2rem 0.75rem;
    box-shadow: 0 2px 8px rgba(62, 96, 213, 0.15);
}

/* Other (received messages) */
.bubble-other {
    background-color: #ffffff;
    color: #343a40;
    border-radius: 0.75rem 0.75rem 0.75rem 0.2rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    border: 1px solid #eef2f7;
}

.reply-context-bubble {
    max-width: 100%;
    overflow: hidden;
    border-radius: 4px;
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

.opacity-60 {
    opacity: 0.6;
}
</style>
