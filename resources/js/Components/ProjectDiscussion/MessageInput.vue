<script setup>
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    project: {
        type: Object,
        required: true
    },
    parentId: {
        type: String,
        default: null
    }
});

const emit = defineEmits(['sent']);

const message = ref('');
const files = ref([]);
const uploading = ref(false);
const fileInput = ref(null);

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
    
    files.value.forEach(file => {
        formData.append('attachments[]', file);
    });

    try {
        const response = await axios.post(route('projects.discussions.store', props.project.id), formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        message.value = '';
        files.value = [];
        if (fileInput.value) fileInput.value.value = '';
        emit('sent', response.data);
    } catch (error) {
        console.error('Error sending message:', error);
        alert('Failed to send message.');
    } finally {
        uploading.value = false;
    }
};

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
        <!-- Selected Files Preview -->
        <div v-if="files.length > 0" class="d-flex flex-wrap gap-2 mb-2 p-2 bg-light rounded border">
            <div v-for="(file, index) in files" :key="index" class="badge bg-white text-dark border d-flex align-items-center gap-2 p-2">
                <i class="ti fs-5" :class="getFileIcon(file)"></i>
                <span class="text-truncate" style="max-width: 150px;">{{ file.name }}</span>
                <i @click="removeFile(index)" class="ti ti-x text-danger cursor-pointer"></i>
            </div>
        </div>

        <div class="d-flex flex-column gap-2 border rounded-3 p-2 bg-white focus-within-shadow">
            <textarea 
                v-model="message" 
                class="form-control border-0 shadow-none resize-none p-2" 
                placeholder="Type your message here..." 
                rows="2"
                @keydown.enter.ctrl="sendMessage"
            ></textarea>
            
            <div class="d-flex justify-content-between align-items-center pt-2 px-1">
                <div class="d-flex gap-2">
                    <button @click="triggerFileInput" class="btn btn-sm btn-light border-0 text-muted" title="Upload files">
                        <i class="ti ti-paperclip fs-5"></i>
                    </button>
                    <input type="file" ref="fileInput" class="d-none" multiple @change="handleFileChange">
                </div>
                
                <button 
                    @click="sendMessage" 
                    class="btn btn-primary btn-sm px-3 d-flex align-items-center gap-1"
                    :disabled="uploading || (!message.trim() && files.length === 0)"
                >
                    <span v-if="uploading" class="spinner-border spinner-border-sm mr-1" role="status"></span>
                    <i v-else class="ti ti-send"></i>
                    {{ parentId ? 'Reply' : 'Send' }}
                </button>
            </div>
        </div>
        <div class="small text-muted mt-1 px-1">Tip: Press Ctrl + Enter to send</div>
    </div>
</template>

<style scoped>
.resize-none {
    resize: none;
}

.focus-within-shadow:focus-within {
    border-color: var(--bs-primary) !important;
    box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.1);
}

.cursor-pointer {
    cursor: pointer;
}
</style>
