<script setup>
import { computed } from 'vue';

const props = defineProps({
    project: {
        type: Object,
        required: true
    },
    discussions: {
        type: Array,
        default: () => []
    }
});

const allAttachments = computed(() => {
    let attachments = [];
    props.discussions.forEach(d => {
        if (d.attachments) attachments.push(...d.attachments);
        if (d.replies) {
            d.replies.forEach(r => {
                if (r.attachments) attachments.push(...r.attachments);
            });
        }
    });
    return attachments.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
});

const images = computed(() => allAttachments.value.filter(a => a.file_type.startsWith('image/')));
const files = computed(() => allAttachments.value.filter(a => !a.file_type.startsWith('image/')));

const getFileIcon = (type) => {
    if (type.includes('pdf')) return 'ti-file-type-pdf-outline';
    if (type.includes('word')) return 'ti-file-text-outline';
    return 'ti-file';
};
</script>

<template>
    <div class="file-summary card border shadow-none rounded mb-0 overflow-hidden">
        <div class="card-header bg-light py-2 border-bottom px-3">
            <h6 class="mb-0 fw-semibold text-uppercase text-muted" style="font-size: 0.65rem; letter-spacing: 0.1em;">Shared Content</h6>
        </div>
        
        <div class="card-body p-3">
            <!-- Images Section -->
            <div class="mb-4">
                <h6 class="text-uppercase text-muted fw-semibold mb-3 opacity-75" style="font-size: 0.6rem; letter-spacing: 0.08em;">Images ({{ images.length }})</h6>
                <div v-if="images.length > 0" class="row g-2">
                    <div v-for="img in images.slice(0, 9)" :key="img.id" class="col-4">
                        <a :href="img.file_path" target="_blank" class="ratio ratio-1x1 d-block border-0 rounded overflow-hidden hover-lift transition-all">
                            <img :src="img.file_path" class="object-fit-cover w-100 h-100" alt="shared-image">
                        </a>
                    </div>
                </div>
                <div v-else class="text-center py-4 bg-light rounded text-muted small border opacity-50">
                    No images shared yet.
                </div>
            </div>

            <!-- Documents Section -->
            <div>
                <h6 class="text-uppercase text-muted fw-semibold mb-3 opacity-75" style="font-size: 0.6rem; letter-spacing: 0.08em;">Documents ({{ files.length }})</h6>
                <div v-if="files.length > 0" class="d-flex flex-column gap-2">
                    <div v-for="file in files.slice(0, 10)" :key="file.id" class="d-flex align-items-center gap-3 p-2 rounded hover-bg transition-all border-0">
                        <div class="file-icon-box d-flex align-items-center justify-content-center rounded">
                            <i class="ti" :class="getFileIcon(file.file_type)" style="font-size: 1rem;"></i>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="fw-semibold text-truncate text-dark" style="font-size: 0.8rem;">{{ file.name || file.file_name }}</div>
                            <div class="text-muted opacity-75" style="font-size: 0.6rem;">{{ (file.file_size / 1024).toFixed(1) }} KB</div>
                        </div>
                        <a :href="route('projects.discussions.download', [project.id, file.id])" class="text-muted hover-text-primary p-2"><i class="ti ti-download" style="font-size: 1rem;"></i></a>
                    </div>
                </div>
                <div v-else class="text-center py-4 bg-light rounded text-muted small border opacity-50">
                    No documents shared yet.
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.hover-lift {
    transition: transform 0.2s, box-shadow 0.2s;
}

.hover-lift:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08) !important;
}

.hover-bg:hover {
    background-color: #f8f9fa;
}

.hover-text-primary:hover { 
    color: var(--bs-primary, #3e60d5) !important; 
}

.file-icon-box {
    width: 34px;
    height: 34px;
    background: rgba(62, 96, 213, 0.08);
    color: var(--bs-primary, #3e60d5);
    flex-shrink: 0;
}

.object-fit-cover {
    object-fit: cover;
}

.transition-all {
    transition: all 0.2s ease;
}
</style>
