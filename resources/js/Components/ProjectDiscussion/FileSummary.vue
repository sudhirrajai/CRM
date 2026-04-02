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
    // Sort by latest
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
    <div class="file-summary card shadow-sm border-0 h-100 mb-0">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="mb-0 fw-bold"><i class="ti ti-folder-open me-2 text-primary"></i> Shared Content</h5>
        </div>
        
        <div class="card-body p-4">
            <!-- Images Section -->
            <div class="mb-4">
                <h6 class="text-uppercase text-muted fw-bold small mb-3">Images ({{ images.length }})</h6>
                <div v-if="images.length > 0" class="row g-2">
                    <div v-for="img in images.slice(0, 9)" :key="img.id" class="col-4">
                        <a :href="img.file_path" target="_blank" class="ratio ratio-1x1 d-block border rounded overflow-hidden shadow-sm hover-lift transition-all">
                            <img :src="img.file_path" class="object-fit-cover w-100 h-100" alt="shared-image">
                        </a>
                    </div>
                </div>
                <div v-else class="text-center py-4 bg-light rounded text-muted small border border-dashed">
                    No images shared yet.
                </div>
            </div>

            <!-- Documents Section -->
            <div>
                <h6 class="text-uppercase text-muted fw-bold small mb-3">Documents ({{ files.length }})</h6>
                <div v-if="files.length > 0" class="d-flex flex-column gap-2">
                    <div v-for="file in files.slice(0, 10)" :key="file.id" class="d-flex align-items-center gap-2 p-2 rounded hover-bg-light transition-colors border-0">
                        <div class="bg-primary-subtle text-primary p-2 rounded">
                            <i class="ti fs-5" :class="getFileIcon(file.file_type)"></i>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="small fw-bold text-truncate">{{ file.name || file.file_name }}</div>
                        </div>
                        <a :href="route('projects.discussions.download', [project.id, file.id])" class="text-muted"><i class="ti ti-download"></i></a>
                    </div>
                </div>
                <div v-else class="text-center py-4 bg-light rounded text-muted small border border-dashed">
                    No documents shared yet.
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.grid-cols-3 {
    grid-template-columns: repeat(3, 1fr);
}

.hover-lift {
    transition: transform 0.2s, box-shadow 0.2s;
}

.hover-lift:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
}

.hover-bg-light:hover {
    background-color: #f8fafc;
}

.border-dashed {
    border-style: dashed !important;
}

.object-fit-cover {
    object-fit: cover;
}
</style>
