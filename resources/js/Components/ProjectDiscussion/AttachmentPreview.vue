<script setup>
import { computed } from 'vue';

const props = defineProps({
    project: {
        type: Object,
        required: true
    },
    attachment: {
        type: Object,
        required: true
    }
});

const isImage = computed(() => {
    return props.attachment.file_type.startsWith('image/');
});

const getFileIcon = (type) => {
    if (type.includes('pdf')) return 'ti-file-type-pdf-outline';
    if (type.includes('zip') || type.includes('rar')) return 'ti-file-zip-outline';
    if (type.includes('word') || type.includes('officedocument')) return 'ti-file-text-outline';
    if (type.includes('excel') || type.includes('sheet')) return 'ti-file-spreadsheet-outline';
    return 'ti-file';
};

const formatSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};
</script>

<template>
    <div class="attachment-preview">
        <!-- Image Preview -->
        <div v-if="isImage" class="image-attachment position-relative">
            <a :href="attachment.file_path" target="_blank" class="d-block overflow-hidden rounded border">
                <img :src="attachment.file_path" class="img-fluid" :alt="attachment.file_name" style="max-height: 200px; object-fit: contain;">
            </a>
            <div class="attachment-info p-2 mt-1 rounded bg-light border-0 d-flex justify-content-between align-items-center">
                <span class="small text-truncate" style="max-width: 150px;">{{ attachment.file_name }}</span>
                <a :href="route('projects.discussions.download', [project.id, attachment.id])" class="text-primary btn btn-sm btn-link p-0"><i class="ti ti-download"></i></a>
            </div>
        </div>

        <!-- File Preview -->
        <div v-else class="file-attachment rounded border p-2 d-flex align-items-center gap-3 bg-white hover-bg-light transition-colors" style="width: 250px;">
            <div class="file-icon flex-shrink-0 bg-light rounded d-flex align-items-center justify-content-center text-primary" style="width: 48px; height: 48px;">
                <i class="ti fs-2" :class="getFileIcon(attachment.file_type)"></i>
            </div>
            <div class="file-details flex-grow-1 overflow-hidden">
                <div class="fw-bold text-dark text-truncate small mb-0">{{ attachment.file_name }}</div>
                <div class="text-muted x-small">{{ formatSize(attachment.file_size) }}</div>
            </div>
            <a :href="route('projects.discussions.download', [project.id, attachment.id])" class="text-primary btn btn-sm btn-link p-0 flex-shrink-0">
                <i class="ti ti-download fs-5"></i>
            </a>
        </div>
    </div>
</template>

<style scoped>
.x-small {
    font-size: 0.7rem;
}

.hover-bg-light:hover {
    background-color: #f8fafc !important;
}

.attachment-preview {
    max-width: 100%;
}
</style>
