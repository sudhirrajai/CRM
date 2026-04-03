<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import MentionDropdown from './MentionDropdown.vue';

const props = defineProps({
    project: {
        type: Object,
        required: true
    },
    parentId: {
        type: String,
        default: null
    },
    members: {
        type: Array,
        default: () => []
    },
    replyTo: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['sent', 'typing']);

const message = ref('');
const files = ref([]);
const uploading = ref(false);
const fileInput = ref(null);
const messageTextarea = ref(null);

// Mentions logic
const showMentionDropdown = ref(false);
const mentionSearch = ref('');
const mentions = ref([]); // Array of mentioned user IDs

const handleKeydown = (e) => {
    if (e.key === '@') {
        showMentionDropdown.value = true;
        mentionSearch.value = '';
    } else if (e.key === ' ' || e.key === 'Enter') {
        if (!showMentionDropdown.value && e.ctrlKey && e.key === 'Enter') {
            sendMessage();
        }
        if (showMentionDropdown.value && e.key === ' ') {
            showMentionDropdown.value = false;
        }
    } else {
        // Update mention search if open
        if (showMentionDropdown.value) {
            // Very simple logic: just capturing characters after @ until space
            // In a real production app, we'd use a more robust library or specialized cursor tracking
            setTimeout(() => {
                const textBeforeCursor = message.value.substring(0, messageTextarea.value.selectionStart);
                const lastAtIdx = textBeforeCursor.lastIndexOf('@');
                if (lastAtIdx !== -1) {
                    mentionSearch.value = textBeforeCursor.substring(lastAtIdx + 1);
                } else {
                    showMentionDropdown.value = false;
                }
            }, 0);
        }
    }
};

const insertMention = (user) => {
    const cursorSelector = messageTextarea.value.selectionStart;
    const textBeforeAt = message.value.substring(0, message.value.lastIndexOf('@', cursorSelector - 1));
    const textAfterCursor = message.value.substring(cursorSelector);
    
    message.value = textBeforeAt + '@' + user.name + ' ' + textAfterCursor;
    if (!mentions.value.includes(user.id)) {
        mentions.value.push(user.id);
    }
    
    showMentionDropdown.value = false;
    messageTextarea.value.focus();
};

const handleFileChange = (e) => {
    const selectedFiles = Array.from(e.target.files);
    files.value = [...files.value, ...selectedFiles];
};

const removeFile = (index) => {
    files.value.splice(index, 1);
};

const sendMessage = async () => {
    if (!message.value.trim() && files.value.length === 0) return;

    uploading.value = true;
    const formData = new FormData();
    formData.append('message', message.value);
    if (props.parentId) formData.append('parent_id', props.parentId);
    
    mentions.value.forEach(id => {
        formData.append('mentions[]', id);
    });

    files.value.forEach(file => {
        formData.append('attachments[]', file);
    });

    try {
        const response = await axios.post(route('projects.discussions.store', props.project.id), formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        message.value = '';
        files.value = [];
        mentions.value = [];
        if (fileInput.value) fileInput.value.value = '';
        emit('sent', response.data);
    } catch (error) {
        console.error('Error sending message:', error);
        alert('Failed to send message.');
    } finally {
        uploading.value = false;
    }
};

const isFocused = ref(false);
const textareaHeight = computed(() => {
    // Simple auto-expand logic could go here, for now fixed or minimal
    return 'auto';
});

// Typing indicator whisper
let typingTimeout = null;
watch(message, (newVal) => {
    if (newVal) {
        emit('typing', true);
        if (typingTimeout) clearTimeout(typingTimeout);
        typingTimeout = setTimeout(() => {
            emit('typing', false);
        }, 2000);
    }
});

const triggerFileInput = () => {
    fileInput.value.click();
};

const getFileIcon = (file) => {
    if (file.type.startsWith('image/')) return 'ti-photo';
    if (file.type.includes('pdf')) return 'ti-file-description';
    return 'ti-file';
};
</script>

<template>
    <div class="message-input-container">
        <!-- Reply Context -->
        <div v-if="replyTo" class="reply-context d-flex align-items-center gap-3 mb-0 p-3 bg-light border-start border-4 border-primary rounded-top-4 animate-slide-up">
            <div class="flex-grow-1 overflow-hidden">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="ti ti-arrow-back-up text-primary fs-5"></i>
                    <span class="fw-bold text-dark small">Replying to {{ replyTo.user.name }}</span>
                </div>
                <div class="text-muted text-truncate x-small">{{ replyTo.message }}</div>
            </div>
            <button @click="$emit('cancel-reply')" class="btn btn-link btn-sm p-1 text-muted hover-text-danger transition-all border-0 shadow-none">
                <i class="ti ti-x fs-5"></i>
            </button>
        </div>

        <!-- Selected Files Preview -->
        <div v-if="files.length > 0" class="d-flex flex-wrap gap-2 mb-0 p-3 bg-light border-top border-light-subtle" :class="{ 'rounded-top-4': !replyTo }">
            <div v-for="(file, index) in files" :key="index" class="file-preview-badge badge bg-white text-dark border d-flex align-items-center gap-2 p-2 shadow-sm rounded-3">
                <i class="ti fs-5 text-primary" :class="getFileIcon(file)"></i>
                <div class="text-start overflow-hidden">
                    <div class="text-truncate fw-medium" style="max-width: 120px;">{{ file.name }}</div>
                    <div class="x-small text-muted">{{ (file.size / 1024).toFixed(1) }} KB</div>
                </div>
                <button @click="removeFile(index)" class="btn btn-link btn-sm p-0 text-danger border-0 shadow-none">
                    <i class="ti ti-circle-x-filled"></i>
                </button>
            </div>
        </div>

        <div class="input-actions-wrapper border bg-white transition-all overflow-hidden shadow-sm" 
             :class="[
                replyTo || files.length > 0 ? 'rounded-bottom-4 border-top-0' : 'rounded-4',
                { 'focused': isFocused }
             ]">
            <textarea 
                ref="messageTextarea"
                v-model="message" 
                class="form-control border-0 shadow-none resize-none p-3 pb-0" 
                placeholder="Type a message..." 
                rows="1"
                @keydown="handleKeydown"
                @focus="isFocused = true"
                @blur="isFocused = false"
                :style="{ height: textareaHeight }"
            ></textarea>
            
            <div class="d-flex justify-content-between align-items-center p-2 pt-0">
                <div class="d-flex gap-1">
                    <button @click="triggerFileInput" class="btn btn-icon btn-sm btn-light-subtle rounded-circle text-muted hover-text-primary transition-all" title="Attach Files">
                        <i class="ti ti-paperclip fs-5"></i>
                    </button>
                    <input type="file" ref="fileInput" class="d-none" multiple @change="handleFileChange">
                    
                    <button class="btn btn-icon btn-sm btn-light-subtle rounded-circle text-muted hover-text-warning transition-all" title="Emoji">
                        <i class="ti ti-mood-smile fs-5"></i>
                    </button>
                </div>
                
                <div class="d-flex align-items-center gap-2">
                    <span class="x-small text-muted d-none d-md-inline-block opacity-50">Press Ctrl + Enter to send</span>
                    <button 
                        @click="sendMessage" 
                        class="btn btn-primary d-flex align-items-center justify-content-center rounded-circle shadow-primary p-0 transition-all hover-scale"
                        style="width: 40px; height: 40px;"
                        :disabled="uploading || (!message.trim() && files.length === 0)"
                    >
                        <div v-if="uploading" class="spinner-border spinner-border-sm" role="status"></div>
                        <i v-else class="ti ti-send-2 fs-4"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mentions Dropdown -->
        <MentionDropdown 
            v-if="showMentionDropdown" 
            :members="members" 
            :search="mentionSearch"
            @select="insertMention"
            @close="showMentionDropdown = false"
        />
    </div>
</template>

<style scoped>
.message-input-container {
    animation: fadeIn 0.3s ease-out;
}

.resize-none {
    resize: none;
    overflow-y: hidden;
    min-height: 48px;
    max-height: 200px;
}

.input-actions-wrapper {
    border-color: #e5e7eb !important;
}

.input-actions-wrapper.focused {
    border-color: #6366f1 !important;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.15) !important;
}

.btn-icon {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
}

.btn-icon:hover {
    background: #f3f4f6;
}

.btn-soft-primary {
    background-color: #eef2ff;
    color: #4f46e5;
}

.shadow-primary {
    box-shadow: 0 4px 10px rgba(99, 102, 241, 0.4);
}

.shadow-primary:hover {
    box-shadow: 0 6px 14px rgba(99, 102, 241, 0.5);
}

.hover-scale:active {
    transform: scale(0.95);
}

.hover-text-primary:hover { color: #6366f1 !important; }
.hover-text-warning:hover { color: #f59e0b !important; }
.hover-text-danger:hover { color: #ef4444 !important; }

.x-small {
    font-size: 0.7rem;
}

.animate-slide-up {
    animation: slideUp 0.2s ease-out;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.btn-light-subtle {
    background-color: #f9fafb;
    border: none;
}

.file-preview-badge {
    max-width: 200px;
}
</style>
