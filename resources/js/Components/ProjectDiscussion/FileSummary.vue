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
    <div class="file-summary card shadow-boron border-0 h-100 mb-0 rounded-4 overflow-hidden">
        <div class="card-header bg-white py-2 border-bottom px-3">
            <h6 class="mb-0 fw-bold text-uppercase text-indigo letter-spacing-1" style="font-size: 0.65rem;">Shared Content</h6>
        </div>
        
        <div class="card-body p-3">
            <!-- Images Section -->
            <div class="mb-4">
                <h6 class="text-uppercase text-muted fw-bold x-small mb-3 letter-spacing-1 opacity-75">Images ({{ images.length }})</h6>
                <div v-if="images.length > 0" class="row g-2">
                    <div v-for="img in images.slice(0, 9)" :key="img.id" class="col-4">
                        <a :href="img.file_path" target="_blank" class="ratio ratio-1x1 d-block border-0 rounded-3 overflow-hidden shadow-boron-sm hover-lift transition-all">
                            <img :src="img.file_path" class="object-fit-cover w-100 h-100" alt="shared-image">
                        </a>
                    </div>
                </div>
                <div v-else class="text-center py-4 bg-boron-light rounded-3 text-muted small border border-dashed opacity-50">
                    No images shared yet.
                </div>
            </div>

            <!-- Documents Section -->
            <div>
                <h6 class="text-uppercase text-muted fw-bold x-small mb-3 letter-spacing-1 opacity-75">Documents ({{ files.length }})</h6>
                <div v-if="files.length > 0" class="d-flex flex-column gap-2">
                    <div v-for="file in files.slice(0, 10)" :key="file.id" class="d-flex align-items-center gap-3 p-2 rounded-3 hover-bg-boron transition-all border-0">
                        <div class="bg-indigo-subtle text-indigo p-2 rounded-3">
                            <i class="ti fs-5" :class="getFileIcon(file.file_type)"></i>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="small fw-bold text-truncate text-dark">{{ file.name || file.file_name }}</div>
                            <div class="x-small text-muted opacity-75">{{ (file.file_size / 1024).toFixed(1) }} KB</div>
                        </div>
                        <a :href="route('projects.discussions.download', [project.id, file.id])" class="text-muted hover-text-indigo p-2"><i class="ti ti-download fs-5"></i></a>
                    </div>
                </div>
                <div v-else class="text-center py-4 bg-boron-light rounded-3 text-muted small border border-dashed opacity-50">
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
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(18, 38, 63, 0.1) !important;
}

.shadow-boron { box-shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, 0.03) !important; }
.shadow-boron-sm { box-shadow: 0 0.25rem 0.5rem rgba(18, 38, 63, 0.05) !important; }
.bg-boron-light { background-color: #f5f7fb !important; }
.text-indigo { color: #4c49e2 !important; }
.bg-indigo-subtle { background-color: rgba(76, 73, 226, 0.1) !important; }

.hover-bg-boron:hover {
    background-color: #f5f7fb;
}

.hover-text-indigo:hover { color: #4c49e2 !important; }
.letter-spacing-1 { letter-spacing: 0.1em; }

.x-small { font-size: 0.65rem; }

.border-dashed {
    border: 1.5px dashed #e2e8f0 !important;
}

.object-fit-cover {
    object-fit: cover;
}
</style>
