<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted, watch } from 'vue';
import DiscussionContainer from '@/Components/ProjectDiscussion/DiscussionContainer.vue';

const props = defineProps({
    projects: {
        type: Array,
        required: true
    },
    selectedProjectId: {
        type: [String, Number],
        default: null
    }
});

const selectedProject = ref(null);
const showProjectList = ref(true);

const selectProject = (project) => {
    selectedProject.value = project;
    showProjectList.value = false;
    // Update URL without full reload to keep state if possible, 
    // or just use internal state for a faster feel.
    // router.get(route('discussions.index'), { project_id: project.id }, { preserveState: true, preserveScroll: true });
};

const backToList = () => {
    showProjectList.value = true;
};

onMounted(() => {
    if (props.selectedProjectId) {
        const project = props.projects.find(p => p.id == props.selectedProjectId);
        if (project) {
            selectedProject.value = project;
            showProjectList.value = false;
        }
    } else if (props.projects.length > 0 && window.innerWidth > 991) {
        // Auto select first project on desktop
        selectedProject.value = props.projects[0];
    }
});

// Watch for prop changes (e.g. from sidebar navigation)
watch(() => props.selectedProjectId, (newId) => {
    if (newId) {
        const project = props.projects.find(p => p.id == newId);
        if (project) {
            selectedProject.value = project;
            showProjectList.value = false;
        }
    }
});

const getStatusClass = (status) => {
    switch (status) {
        case 'completed': return 'bg-success';
        case 'in_progress': return 'bg-primary';
        case 'on_hold': return 'bg-warning';
        default: return 'bg-secondary';
    }
};

const formatStatus = (status) => {
    if (!status) return '';
    return status.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
};
</script>

<template>
    <Head title="Discussions" />

    <AuthenticatedLayout>
        <div class="row g-0 discussion-wrapper shadow-lg rounded-4 overflow-hidden bg-white border-0">
            <!-- Project List Sidebar (Left) -->
            <div class="col-lg-3 border-end project-sidebar h-100" :class="{ 'd-none d-lg-flex': !showProjectList }">
                <div class="p-4 border-bottom bg-white d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-bold d-flex align-items-center text-dark">
                        <i class="ti ti-folders me-2 text-primary fs-4"></i> Projects
                    </h5>
                    <span class="badge bg-light text-muted rounded-pill">{{ projects.length }}</span>
                </div>
                
                <div class="project-list custom-scrollbar bg-light-subtle">
                    <div v-if="projects.length === 0" class="p-5 text-center text-muted">
                        <div class="mb-3 opacity-25">
                            <i class="ti ti-folder-off" style="font-size: 3rem;"></i>
                        </div>
                        <p class="small fw-medium">No projects found</p>
                    </div>

                    <div 
                        v-for="project in projects" 
                        :key="project.id"
                        @click="selectProject(project)"
                        class="project-item p-3 cursor-pointer transition-all border-bottom border-light-subtle"
                        :class="{ 'active': selectedProject?.id === project.id }"
                    >
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="mb-0 fw-bold text-truncate pe-2" style="max-width: 160px;">{{ project.name }}</h6>
                            <div class="status-dot flex-shrink-0" :class="getStatusClass(project.status)" :title="formatStatus(project.status)"></div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <p class="text-muted mb-0 x-small text-truncate" style="max-width: 140px;">
                                <i class="ti ti-user me-1 text-primary opacity-50"></i> {{ project.client_name }}
                            </p>
                            <span class="x-small text-muted opacity-50">{{ project.discussions_count || 0 }} msgs</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Discussion Area (Center/Right) -->
            <div class="col-lg-9 discussion-right-col" :class="{ 'd-none d-lg-flex': showProjectList }">
                <div v-if="selectedProject" class="discussion-active-container">
                    <!-- Mobile View Header -->
                    <div class="d-lg-none p-3 border-bottom bg-white shadow-sm d-flex align-items-center flex-shrink-0">
                        <button @click="backToList" class="btn btn-icon btn-light rounded-circle me-3">
                            <i class="ti ti-arrow-left"></i>
                        </button>
                        <div class="overflow-hidden">
                            <h6 class="mb-0 fw-bold text-truncate">{{ selectedProject.name }}</h6>
                            <span class="x-small text-success">Active Thread</span>
                        </div>
                    </div>

                    <div class="discussion-container-wrap">
                        <DiscussionContainer :project="selectedProject" :key="selectedProject.id" />
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="d-flex flex-column justify-content-center align-items-center bg-white p-5 animate-fade-in flex-grow-1">
                    <div class="text-center" style="max-width: 320px;">
                        <div class="empty-state-illustration mb-4">
                            <div class="bg-primary-subtle p-4 rounded-circle d-inline-flex mb-3">
                                <i class="ti ti-messages fs-1 text-primary"></i>
                            </div>
                        </div>
                        <h4 class="fw-bold text-dark mb-2">Select a Discussion</h4>
                        <p class="text-muted small">Choose a project from the left panel to join live discussions with your team and clients.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.discussion-wrapper {
    height: calc(100vh - 160px);
    background: #fff;
    display: flex;
    overflow: hidden;
}

.project-sidebar {
    background-color: #fff;
    display: flex;
    flex-direction: column;
}

.project-list {
    flex-grow: 1;
    overflow-y: auto;
}

.project-item {
    background: transparent;
    border-left: 4px solid transparent;
}

.project-item:hover {
    background-color: #fff;
    transform: translateX(4px);
}

.project-item.active {
    background-color: #fff;
    border-left: 4px solid #6366f1;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.project-item.active h6 {
    color: #4f46e5;
}

.status-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin-top: 4px;
}

.bg-primary { background-color: #4f46e5 !important; }
.bg-success { background-color: #10b981 !important; }
.bg-warning { background-color: #f59e0b !important; }
.bg-secondary { background-color: #6b7280 !important; }

.cursor-pointer {
    cursor: pointer;
}

.x-small {
    font-size: 0.7rem;
    font-weight: 500;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #e5e7eb;
    border-radius: 10px;
}

.animate-fade-in {
    animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Discussion right column */
.discussion-right-col {
    display: flex;
    flex-direction: column;
    min-height: 0;
    overflow: hidden;
}

.discussion-active-container {
    display: flex;
    flex-direction: column;
    flex: 1 1 0;
    min-height: 0;
    overflow: hidden;
}

.discussion-container-wrap {
    flex: 1 1 0;
    min-height: 0;
    overflow: hidden;
}

@media (max-width: 991px) {
    .discussion-wrapper {
        height: calc(100vh - 100px);
        margin: -1rem;
        border-radius: 0 !important;
    }
}

.btn-icon {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
