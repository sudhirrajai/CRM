<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted, watch, computed } from 'vue';
import DiscussionContainer from '@/Components/ProjectDiscussion/DiscussionContainer.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    projects: {
        type: Array,
        required: true
    },
    groups: {
        type: Array,
        default: () => []
    },
    selectedProjectId: {
        type: [String, Number],
        default: null
    },
    selectedGroupId: {
        type: [String, Number],
        default: null
    }
});

const selectedProject = ref(null);
const showProjectList = ref(true);

const page = usePage();
const isAdmin = computed(() => page.props.auth.roles.includes('admin'));

const activeTab = ref('projects');
const showCreateGroupModal = ref(false);
const groupName = ref('');
const allUsers = ref([]);
const selectedUsers = ref([]);
const loadingUsers = ref(false);
const submittingGroup = ref(false);
const userSearchQuery = ref('');

const filteredUsers = computed(() => {
    if (!userSearchQuery.value) return allUsers.value;
    const q = userSearchQuery.value.toLowerCase();
    return allUsers.value.filter(u => u.name.toLowerCase().includes(q) || u.role.toLowerCase().includes(q));
});

const selectProject = (project) => {
    selectedProject.value = project;
    showProjectList.value = false;
    router.visit(route('discussions.index', { project_id: project.id }), {
        preserveState: true,
        preserveScroll: true,
    });
};

const selectGroup = (group) => {
    selectedProject.value = group;
    showProjectList.value = false;
    router.visit(route('discussions.index', { group_id: group.id }), {
        preserveState: true,
        preserveScroll: true,
    });
};

const backToList = () => {
    showProjectList.value = true;
};

const openCreateGroupModal = async () => {
    showCreateGroupModal.value = true;
    loadingUsers.value = true;
    groupName.value = '';
    selectedUsers.value = [];
    userSearchQuery.value = '';
    try {
        const response = await axios.get(route('discussion-groups.users'));
        allUsers.value = response.data.filter(u => u.id !== page.props.auth.user.id);
    } catch (error) {
        console.error('Error fetching users:', error);
    } finally {
        loadingUsers.value = false;
    }
};

const createGroup = async () => {
    if (!groupName.value.trim()) return;
    submittingGroup.value = true;
    try {
        const response = await axios.post(route('discussion-groups.store'), {
            name: groupName.value,
            user_ids: selectedUsers.value
        });
        showCreateGroupModal.value = false;
        
        router.visit(route('discussions.index', { group_id: response.data.group.id }), {
            preserveScroll: true,
        });
    } catch (error) {
        console.error('Error creating group:', error);
        alert(error.response?.data?.message || 'Error creating group');
    } finally {
        submittingGroup.value = false;
    }
};

onMounted(() => {
    if (props.selectedGroupId) {
        activeTab.value = 'groups';
        const group = props.groups.find(g => g.id == props.selectedGroupId);
        if (group) {
            selectedProject.value = group;
            showProjectList.value = false;
        }
    } else if (props.selectedProjectId) {
        activeTab.value = 'projects';
        const project = props.projects.find(p => p.id == props.selectedProjectId);
        if (project) {
            selectedProject.value = project;
            showProjectList.value = false;
        }
    } else {
        activeTab.value = 'projects';
        if (props.projects.length > 0 && window.innerWidth > 991) {
            selectedProject.value = props.projects[0];
        } else if (props.groups.length > 0 && window.innerWidth > 991) {
            activeTab.value = 'groups';
            selectedProject.value = props.groups[0];
        }
    }
});

watch(() => props.selectedProjectId, (newId) => {
    if (newId) {
        activeTab.value = 'projects';
        const project = props.projects.find(p => p.id == newId);
        if (project) {
            selectedProject.value = project;
            showProjectList.value = false;
        }
    }
});

watch(() => props.selectedGroupId, (newId) => {
    if (newId) {
        activeTab.value = 'groups';
        const group = props.groups.find(g => g.id == newId);
        if (group) {
            selectedProject.value = group;
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
                        <!-- Tab Picker -->
                        <div class="project-sidebar-header d-flex flex-column gap-2 border-bottom bg-light p-2 flex-shrink-0">
                            <div class="d-flex bg-white rounded border p-1 shadow-sm gap-1">
                                <button 
                                    @click="activeTab = 'projects'" 
                                    class="btn btn-sm flex-grow-1 d-flex align-items-center justify-content-center gap-2 border-0 shadow-none py-1.5 transition-all" 
                                    :class="activeTab === 'projects' ? 'bg-primary text-white fw-semibold rounded' : 'text-muted'"
                                    style="font-size: 0.75rem;"
                                >
                                    <i class="ti ti-folders"></i> Projects
                                </button>
                                <button 
                                    @click="activeTab = 'groups'" 
                                    class="btn btn-sm flex-grow-1 d-flex align-items-center justify-content-center gap-2 border-0 shadow-none py-1.5 transition-all" 
                                    :class="activeTab === 'groups' ? 'bg-primary text-white fw-semibold rounded' : 'text-muted'"
                                    style="font-size: 0.75rem;"
                                >
                                    <i class="ti ti-users"></i> Groups
                                </button>
                            </div>
                        </div>

                        <!-- Create Group Header for Admin -->
                        <div v-if="activeTab === 'groups' && isAdmin" class="px-3 py-2 border-bottom d-flex align-items-center justify-content-between bg-light-subtle flex-shrink-0">
                            <span class="x-small text-uppercase fw-semibold text-muted tracking-wider" style="font-size: 0.6rem; letter-spacing: 0.05em;">Discussion Groups</span>
                            <button @click="openCreateGroupModal" class="btn btn-xs btn-outline-primary d-flex align-items-center gap-1 shadow-none px-2 py-0.5" style="font-size: 0.65rem; border-radius: 4px;">
                                <i class="ti ti-plus" style="font-size: 0.7rem;"></i> Create Group
                            </button>
                        </div>
                        
                        <div class="project-list custom-scrollbar">
                            <!-- Projects Tab -->
                            <template v-if="activeTab === 'projects'">
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
                                    :class="{ 'active': selectedProject?.id === project.id && !selectedProject?.is_group }"
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
                            </template>

                            <!-- Groups Tab -->
                            <template v-else>
                                <div v-if="groups.length === 0" class="p-5 text-center text-muted">
                                    <div class="mb-3 opacity-25">
                                        <i class="ti ti-users" style="font-size: 3rem;"></i>
                                    </div>
                                    <p class="small fw-medium mb-0">No groups found</p>
                                </div>

                                <div 
                                    v-for="group in groups" 
                                    :key="group.id"
                                    @click="selectGroup(group)"
                                    class="project-item cursor-pointer transition-all"
                                    :class="{ 'active': selectedProject?.id === group.id && selectedProject?.is_group }"
                                >
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <h6 class="mb-0 fw-semibold text-truncate pe-2 project-name" style="max-width: 160px; font-size: 0.85rem;">{{ group.name }}</h6>
                                        <span class="badge bg-light border text-muted x-small rounded-pill">{{ group.members_count || 0 }} members</span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <p class="text-muted mb-0 x-small text-truncate">
                                            <i class="ti ti-hash me-1 opacity-50"></i> Super Admin Chat Group
                                        </p>
                                    </div>
                                </div>
                            </template>
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

        <!-- Create Group Modal -->
        <Modal :show="showCreateGroupModal" @close="showCreateGroupModal = false" max-width="md">
            <div class="p-4 bg-white rounded shadow-lg border">
                <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
                    <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="ti ti-users-plus text-primary fs-4"></i>
                        Create Discussion Group
                    </h5>
                    <button @click="showCreateGroupModal = false" class="btn-close shadow-none opacity-50"></button>
                </div>

                <form @submit.prevent="createGroup">
                    <!-- Group Name -->
                    <div class="mb-3">
                        <label for="group_name" class="form-label small fw-semibold text-muted">Group Name</label>
                        <input 
                            id="group_name"
                            v-model="groupName" 
                            type="text" 
                            class="form-control" 
                            placeholder="e.g. Executive Discussion" 
                            required
                        >
                    </div>

                    <!-- Member Selection -->
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="form-label small fw-semibold text-muted mb-0">Select Members</label>
                            <span v-if="selectedUsers.length > 0" class="badge bg-primary-subtle text-primary small rounded-pill">
                                {{ selectedUsers.length }} selected
                            </span>
                        </div>

                        <!-- User search input -->
                        <div class="position-relative mb-2">
                            <i class="ti ti-search position-absolute top-50 translate-middle-y text-muted" style="left: 12px; font-size: 0.85rem;"></i>
                            <input 
                                v-model="userSearchQuery" 
                                type="text" 
                                class="form-control form-control-sm ps-4" 
                                placeholder="Search users by name or role..."
                            >
                        </div>

                        <div v-if="loadingUsers" class="text-center py-4 bg-light rounded border">
                            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                            <p class="mt-2 text-muted x-small mb-0">Fetching users...</p>
                        </div>
                        
                        <div v-else class="border rounded bg-light p-2 custom-scrollbar" style="max-height: 200px; overflow-y: auto;">
                            <div v-if="filteredUsers.length === 0" class="text-center py-4 text-muted x-small">
                                No users found.
                            </div>
                            <div v-for="user in filteredUsers" :key="user.id" class="form-check d-flex align-items-center gap-2 p-2 rounded hover-bg-light border-bottom border-light-subtle last-border-0">
                                <input 
                                    :id="'user-' + user.id" 
                                    v-model="selectedUsers" 
                                    :value="user.id" 
                                    type="checkbox" 
                                    class="form-check-input mt-0 cursor-pointer"
                                >
                                <label :for="'user-' + user.id" class="form-check-label cursor-pointer flex-grow-1 overflow-hidden d-flex justify-content-between align-items-center pe-2">
                                    <span class="small text-dark fw-medium text-truncate">{{ user.name }}</span>
                                    <span class="badge bg-white border text-muted px-2 py-0.5" style="font-size: 0.65rem; text-transform: uppercase;">{{ user.role }}</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-2 border-top">
                        <SecondaryButton type="button" @click="showCreateGroupModal = false">Cancel</SecondaryButton>
                        <PrimaryButton type="submit" :disabled="submittingGroup || !groupName.trim()">
                            <span v-if="submittingGroup" class="spinner-border spinner-border-sm me-1" role="status"></span>
                            Create Group
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
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
