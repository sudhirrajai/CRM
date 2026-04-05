<script setup>
import { ref, onMounted, onUnmounted, computed, watch, nextTick } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import MentionDropdown from './MentionDropdown.vue';
import EmojiPicker from './EmojiPicker.vue';

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
const mentions = ref([]);

const page = usePage();
const authUser = computed(() => page.props.auth.user);
const mentionableMembers = computed(() => props.members.filter(m => m.id !== authUser.value.id));

// Emoji logic
const showEmojiPicker = ref(false);
const inputWrapperRef = ref(null);

const handleKeydown = (e) => {
    if (e.key === '@') {
        showMentionDropdown.value = true;
        mentionSearch.value = '';
    } else if (e.key === 'Escape') {
        showMentionDropdown.value = false;
        showEmojiPicker.value = false;
    } else if (e.key === 'Enter') {
        if (!showMentionDropdown.value && e.ctrlKey) {
            e.preventDefault();
            sendMessage();
        }
    } else {
        if (showMentionDropdown.value) {
            setTimeout(() => {
                const cursorPosition = messageTextarea.value.selectionStart;
                const textBeforeCursor = message.value.substring(0, cursorPosition);
                const lastAtIdx = textBeforeCursor.lastIndexOf('@');
                
                if (lastAtIdx !== -1) {
                    const currentQuery = textBeforeCursor.substring(lastAtIdx + 1);
                    if (currentQuery.includes(' ')) {
                        showMentionDropdown.value = false;
                    } else {
                        mentionSearch.value = currentQuery;
                    }
                } else {
                    showMentionDropdown.value = false;
                }
            }, 0);
        }
    }
};

const insertMention = (user) => {
    const cursorPosition = messageTextarea.value.selectionStart;
    const textBeforeCursor = message.value.substring(0, cursorPosition);
    const lastAtIdx = textBeforeCursor.lastIndexOf('@');
    
    if (lastAtIdx === -1) return;

    const textBeforeAt = message.value.substring(0, lastAtIdx);
    const textAfterCursor = message.value.substring(cursorPosition);
    
    const mentionText = '@' + user.name + ' ';
    message.value = textBeforeAt + mentionText + textAfterCursor;
    
    if (!mentions.value.includes(user.id)) {
        mentions.value.push(user.id);
    }
    
    showMentionDropdown.value = false;
    
    nextTick(() => {
        const newPos = lastAtIdx + mentionText.length;
        messageTextarea.value.setSelectionRange(newPos, newPos);
        messageTextarea.value.focus();
    });
};

const insertEmoji = (emoji) => {
    const cursorPosition = messageTextarea.value.selectionStart || message.value.length;
    const textBefore = message.value.substring(0, cursorPosition);
    const textAfter = message.value.substring(cursorPosition);
    
    message.value = textBefore + emoji + textAfter;
    
    nextTick(() => {
        const newPos = cursorPosition + emoji.length;
        messageTextarea.value.setSelectionRange(newPos, newPos);
        messageTextarea.value.focus();
    });
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

// Handle clicks outside to close dropdowns
const handleClickOutside = (e) => {
    if (inputWrapperRef.value && !inputWrapperRef.value.contains(e.target)) {
        showEmojiPicker.value = false;
        showMentionDropdown.value = false;
    }
};

onMounted(() => {
    document.addEventListener('mousedown', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('mousedown', handleClickOutside);
});
</script>

<template>
    <div class="message-input-container">
        <!-- Reply Context -->
        <div v-if="replyTo" class="reply-context d-flex align-items-center gap-3 mb-0 p-3 bg-light border-start border-3 border-primary rounded-top animate-slide-up">
            <div class="flex-grow-1 overflow-hidden">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="ti ti-arrow-back-up text-primary" style="font-size: 0.9rem;"></i>
                    <span class="fw-semibold text-dark" style="font-size: 0.8rem;">Replying to {{ replyTo.user.name }}</span>
                </div>
                <div class="text-muted text-truncate" style="font-size: 0.7rem;">{{ replyTo.message }}</div>
            </div>
            <button @click="$emit('cancel-reply')" class="btn btn-link btn-sm p-1 text-muted transition-all border-0 shadow-none">
                <i class="ti ti-x" style="font-size: 0.9rem;"></i>
            </button>
        </div>

        <!-- Selected Files Preview -->
        <div v-if="files.length > 0" class="d-flex flex-wrap gap-2 mb-0 p-3 bg-light border-top border-light-subtle" :class="{ 'rounded-top': !replyTo }">
            <div v-for="(file, index) in files" :key="index" class="file-preview-badge badge bg-white text-dark border d-flex align-items-center gap-2 p-2 shadow-sm rounded">
                <i class="ti text-primary" :class="getFileIcon(file)" style="font-size: 1rem;"></i>
                <div class="text-start overflow-hidden">
                    <div class="text-truncate fw-medium" style="max-width: 120px; font-size: 0.75rem;">{{ file.name }}</div>
                    <div class="text-muted" style="font-size: 0.6rem;">{{ (file.size / 1024).toFixed(1) }} KB</div>
                </div>
                <button @click="removeFile(index)" class="btn btn-link btn-sm p-0 text-danger border-0 shadow-none">
                    <i class="ti ti-circle-x-filled" style="font-size: 0.85rem;"></i>
                </button>
            </div>
        </div>

        <div ref="inputWrapperRef" class="input-actions-wrapper border bg-white transition-all" 
             :class="[
                replyTo || files.length > 0 ? 'rounded-bottom border-top-0' : 'rounded',
                { 'focused': isFocused }
             ]">
            <textarea 
                ref="messageTextarea"
                v-model="message" 
                class="form-control border-0 shadow-none resize-none p-3 bg-transparent" 
                placeholder="Type a message..." 
                rows="1"
                @keydown="handleKeydown"
                @focus="isFocused = true"
                @blur="isFocused = false"
                :style="{ height: textareaHeight }"
            ></textarea>
            
            <div class="d-flex justify-content-between align-items-center p-3 pt-1">
                <div class="d-flex gap-2">
                    <button @click="triggerFileInput" class="btn btn-sm btn-light border rounded d-flex align-items-center justify-content-center transition-all" style="width: 32px; height: 32px;" title="Attach Files">
                        <i class="ti ti-paperclip" style="font-size: 0.9rem;"></i>
                    </button>
                    <input type="file" ref="fileInput" class="d-none" multiple @change="handleFileChange">
                    
                    <div class="position-relative" style="z-index: 1050;">
                        <button 
                            @click="showEmojiPicker = !showEmojiPicker" 
                            class="btn btn-sm btn-light border rounded d-flex align-items-center justify-content-center transition-all"
                            style="width: 32px; height: 32px;"
                            :class="{ 'text-warning bg-warning-subtle border-warning': showEmojiPicker }"
                            title="Emoji"
                        >
                            <i class="ti ti-mood-smile" style="font-size: 0.9rem;"></i>
                        </button>
                        <EmojiPicker 
                            v-if="showEmojiPicker" 
                            @select="insertEmoji" 
                            @close="showEmojiPicker = false" 
                        />
                        
                        <!-- Mentions Dropdown -->
                        <MentionDropdown 
                            v-if="showMentionDropdown" 
                            :members="mentionableMembers" 
                            :search="mentionSearch"
                            @select="insertMention"
                            @close="showMentionDropdown = false"
                        />
                    </div>
                </div>
                
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted d-none d-md-inline-block" style="font-size: 0.65rem; opacity: 0.5;">Press Ctrl + Enter to send</span>
                    <button 
                        @click="sendMessage" 
                        class="btn btn-primary d-flex align-items-center justify-content-center rounded-circle p-0 transition-all send-btn"
                        style="width: 36px; height: 36px;"
                        :disabled="uploading || (!message.trim() && files.length === 0)"
                    >
                        <div v-if="uploading" class="spinner-border spinner-border-sm" role="status"></div>
                        <i v-else class="ti ti-send" style="font-size: 1rem;"></i>
                    </button>
                </div>
            </div>
        </div>

    </div>
</template>

<style scoped>
.message-input-container {
    animation: fadeIn 0.3s ease-out;
}

.resize-none {
    resize: none;
    overflow-y: hidden;
    min-height: 44px;
    max-height: 200px;
    font-size: 0.875rem;
}

.input-actions-wrapper {
    border-color: #dee2e6 !important;
    position: relative;
    z-index: 1;
}

.input-actions-wrapper.focused {
    border-color: var(--bs-primary, #3e60d5) !important;
    box-shadow: 0 0 0 0.15rem rgba(62, 96, 213, 0.12) !important;
}

.send-btn:not(:disabled):hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(62, 96, 213, 0.3);
}

.send-btn:active {
    transform: scale(0.95);
}

.transition-all {
    transition: all 0.2s ease;
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

.file-preview-badge {
    max-width: 200px;
}
</style>
