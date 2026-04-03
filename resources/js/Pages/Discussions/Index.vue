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
        <div class="row g-0 discussion-wrapper shadow-sm rounded-4 overflow-hidden bg-white">
            <!-- Project List Sidebar (Internal) -->
            <div class="col-lg-4 border-end project-sidebar" :class="{ 'd-none d-lg-block': !showProjectList }">
                <div class="p-3 border-bottom bg-light">
                    <h5 class="mb-0 fw-bold d-flex align-items-center">
                        <i class="ti ti-folders me-2 text-primary"></i> Projects
                    </h5>
                </div>
                
                <div class="project-list custom-scrollbar">
                    <div v-if="projects.length === 0" class="p-5 text-center text-muted">
                        <i class="ti ti-folder-off fs-1 d-block mb-2"></i>
                        <p>No projects found.</p>
                    </div>

                    <div 
                        v-for="project in projects" 
                        :key="project.id"
                        @click="selectProject(project)"
                        class="project-item p-3 border-bottom cursor-pointer transition-all"
                        :class="{ 'active': selectedProject?.id === project.id }"
                    >
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h6 class="mb-0 fw-bold text-truncate" style="max-width: 200px;">{{ project.name }}</h6>
                            <span class="badge x-small px-2" :class="getStatusClass(project.status)">
                                {{ formatStatus(project.status) }}
                            </span>
                        </div>
                        <p class="text-muted mb-0 x-small">
                            <i class="ti ti-user me-1"></i> {{ project.client_name }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Discussion Area -->
            <div class="col-lg-8 d-flex flex-column" :class="{ 'd-none d-lg-flex': showProjectList }">
                <div v-if="selectedProject" class="h-100 d-flex flex-column">
                    <!-- Mobile Back Button -->
                    <div class="d-lg-none p-2 border-bottom bg-light">
                        <button @click="backToList" class="btn btn-sm btn-outline-primary d-flex align-items-center">
                            <i class="ti ti-chevron-left me-1"></i> Back to Projects
                        </button>
                    </div>

                    <div class="flex-grow-1 p-0 overflow-hidden">
                        <!-- We use :key to force re-render when switching projects -->
                        <DiscussionContainer :project="selectedProject" :key="selectedProject.id" />
                    </div>
                </div>

                <div v-else class="h-100 d-flex flex-column justify-content-center align-items-center text-muted p-5 bg-light-subtle">
                    <div class="text-center animate-bounce-subtle">
                        <div class="bg-primary-subtle p-4 rounded-circle mb-4 mx-auto" style="width: 100px; height: 100px; display: flex; align-items: center; justify-content: center;">
                            <i class="ti ti-messages fs-1 text-primary"></i>
                        </div>
                        <h4 class="fw-bold text-dark">Select a project to start discussing</h4>
                        <p class="max-w-400 mx-auto">Choose a project from the list on the left to view and participate in real-time discussions with your team and clients.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.discussion-wrapper {
    height: calc(100vh - 160px);
    border: 1px solid #eef2f7;
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
    transition: all 0.2s ease;
}

.project-item:hover {
    background-color: #f8f9fa;
}

.project-item.active {
    background-color: #edf2ff;
    border-left: 4px solid #3e64ff;
    padding-left: calc(1rem - 4px) !important;
}

.cursor-pointer {
    cursor: pointer;
}

.x-small {
    font-size: 0.75rem;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #f1f5f9;
    border-radius: 10px;
}

.animate-bounce-subtle {
    animation: bounce 3s infinite ease-in-out;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

@media (max-width: 991px) {
    .discussion-wrapper {
        height: calc(100vh - 130px);
    }
}

.max-w-400 {
    max-width: 400px;
}
</style>
