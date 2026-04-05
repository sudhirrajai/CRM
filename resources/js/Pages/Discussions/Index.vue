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
        selectedProject.value = props.projects[0];
    }
});

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
        <!-- Page Title - matches admin pattern -->
        <h4 class="header-title mb-3">Discussions</h4>

        <div class="card discussion-card mb-0">
            <div class="card-body p-0">
                <div class="row g-0 discussion-wrapper">
                    <!-- Project List Sidebar (Left) -->
                    <div class="col-lg-3 border-end project-sidebar" :class="{ 'd-none d-lg-flex': !showProjectList }">
                        <div class="project-sidebar-header d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 fw-semibold d-flex align-items-center gap-2">
                                <i class="ti ti-folders text-muted"></i> Projects
                            </h6>
                            <span class="badge bg-light text-muted rounded-pill border">{{ projects.length }}</span>
                        </div>
                        
                        <div class="project-list custom-scrollbar">
                            <div v-if="projects.length === 0" class="p-5 text-center text-muted">
                                <div class="mb-3 opacity-25">
                                    <i class="ti ti-folder-off" style="font-size: 3rem;"></i>
                                </div>
                                <p class="small fw-medium mb-0">No projects found</p>
                            </div>

                            <div 
                                v-for="project in projects" 
                                :key="project.id"
                                @click="selectProject(project)"
                                class="project-item cursor-pointer transition-all"
                                :class="{ 'active': selectedProject?.id === project.id }"
                            >
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h6 class="mb-0 fw-semibold text-truncate pe-2 project-name" style="max-width: 160px; font-size: 0.85rem;">{{ project.name }}</h6>
                                    <div class="status-dot flex-shrink-0" :class="getStatusClass(project.status)" :title="formatStatus(project.status)"></div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between">
                                    <p class="text-muted mb-0 x-small text-truncate" style="max-width: 140px;">
                                        <i class="ti ti-user me-1 opacity-50"></i> {{ project.client_name }}
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
                            <div class="d-lg-none p-3 border-bottom bg-white d-flex align-items-center flex-shrink-0">
                                <button @click="backToList" class="btn btn-sm btn-light rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                                    <i class="ti ti-arrow-left"></i>
                                </button>
                                <div class="overflow-hidden">
                                    <h6 class="mb-0 fw-semibold text-truncate" style="font-size: 0.9rem;">{{ selectedProject.name }}</h6>
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
                                    <div class="bg-light p-4 rounded-circle d-inline-flex mb-3">
                                        <i class="ti ti-messages fs-1 text-primary"></i>
                                    </div>
                                </div>
                                <h5 class="fw-bold text-dark mb-2">Select a Discussion</h5>
                                <p class="text-muted small mb-0">Choose a project from the left panel to join live discussions with your team and clients.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Discussion card - match admin card style */
.discussion-card {
    border-radius: 0.25rem;
    overflow: hidden;
}

.discussion-wrapper {
    height: calc(100vh - 200px);
    display: flex;
    overflow: hidden;
}

/* --- Project Sidebar --- */
.project-sidebar {
    background-color: #fff;
    display: flex;
    flex-direction: column;
}

.project-sidebar-header {
    padding: 14px 16px;
    border-bottom: 1px solid #eef2f7;
    background: #f8f9fa;
    flex-shrink: 0;
}

.project-list {
    flex-grow: 1;
    overflow-y: auto;
}

.project-item {
    padding: 12px 16px;
    background: transparent;
    border-bottom: 1px solid #f3f6f9;
    border-left: 3px solid transparent;
}

.project-item:hover {
    background-color: #f8f9fa;
}

.project-item.active {
    background-color: #f0f4ff;
    border-left-color: var(--bs-primary, #3e60d5);
}

.project-item.active .project-name {
    color: var(--bs-primary, #3e60d5);
}

/* Status dots */
.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-top: 5px;
    flex-shrink: 0;
}

.cursor-pointer {
    cursor: pointer;
}

.x-small {
    font-size: 0.7rem;
    font-weight: 500;
}

/* Scrollbar */
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #e5e7eb;
    border-radius: 10px;
}

/* Fade animation */
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

.transition-all {
    transition: all 0.2s ease;
}

/* Responsive */
@media (max-width: 991px) {
    .discussion-wrapper {
        height: calc(100vh - 140px);
    }
    .discussion-card {
        margin: -1rem;
        border-radius: 0 !important;
    }
}
</style>
