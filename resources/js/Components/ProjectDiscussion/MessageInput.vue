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
    <div class="message-input-container position-relative">
        <!-- Reply Context -->
        <div v-if="replyTo" class="reply-context d-flex align-items-center gap-2 mb-2 p-2 bg-light rounded-top border-start border-3 border-primary small">
            <i class="ti ti-arrow-back-up text-primary"></i>
            <span class="text-muted">Replying to <strong>{{ replyTo.user.name }}</strong></span>
            <button @click="$emit('cancel-reply')" class="btn btn-xs ms-auto p-0"><i class="ti ti-x"></i></button>
        </div>

        <!-- Selected Files Preview -->
        <div v-if="files.length > 0" class="d-flex flex-wrap gap-2 mb-2 p-2 bg-light rounded border">
            <div v-for="(file, index) in files" :key="index" class="badge bg-white text-dark border d-flex align-items-center gap-2 p-2 shadow-sm rounded-pill">
                <i class="ti fs-6" :class="getFileIcon(file)"></i>
                <span class="text-truncate" style="max-width: 150px;">{{ file.name }}</span>
                <i @click="removeFile(index)" class="ti ti-x text-danger cursor-pointer"></i>
            </div>
        </div>

        <div class="input-actions-wrapper border rounded-4 bg-white focus-within-shadow transition-all overflow-hidden" :class="{ 'rounded-top-0 border-top-0': replyTo }">
            <textarea 
                ref="messageTextarea"
                v-model="message" 
                class="form-control border-0 shadow-none resize-none p-3" 
                placeholder="Type your message here... use @ to mention" 
                rows="2"
                @keydown="handleKeydown"
            ></textarea>
            
            <div class="d-flex justify-content-between align-items-center pb-2 px-2 border-top-0 pt-0">
                <div class="d-flex gap-1 ml-1">
                    <button @click="triggerFileInput" class="btn btn-icon btn-sm btn-light border-0 rounded-circle text-muted" title="Upload files">
                        <i class="ti ti-paperclip fs-5"></i>
                    </button>
                    <input type="file" ref="fileInput" class="d-none" multiple @change="handleFileChange">
                    
                    <button class="btn btn-icon btn-sm btn-light border-0 rounded-circle text-muted" title="Add emoji">
                        <i class="ti ti-mood-smile fs-5"></i>
                    </button>
                </div>
                
                <div class="d-flex align-items-center gap-2 pr-1">
                    <span class="small text-muted hidden-sm">Ctrl + Enter to send</span>
                    <button 
                        @click="sendMessage" 
                        class="btn btn-primary d-flex align-items-center justify-content-center rounded-circle shadow-sm p-0"
                        style="width: 36px; height: 36px;"
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
.resize-none {
    resize: none;
    min-height: 80px;
}

.focus-within-shadow:focus-within {
    border-color: #6366f1 !important;
    box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.1), 0 4px 6px -2px rgba(99, 102, 241, 0.05);
}

.cursor-pointer {
    cursor: pointer;
}

.btn-icon {
    width: 34px;
    height: 34px;
}

.x-small {
    font-size: 11px;
}

.reply-context {
    border-top-left-radius: 1rem !important;
    border-top-right-radius: 1rem !important;
}

.transition-all {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

@media (max-width: 576px) {
    .hidden-sm {
        display: none;
    }
}
</style>
