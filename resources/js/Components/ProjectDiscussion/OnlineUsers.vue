<script setup>
const props = defineProps({
    project: {
        type: Object,
        required: true
    },
    onlineUsers: {
        type: Array,
        default: () => []
    },
    members: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['memberAdded', 'memberRemoved']);

const isOnline = (userId) => {
    return props.onlineUsers.some(u => u.id === userId);
};

import { ref, computed, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const page = usePage();
const isAdmin = computed(() => page.props.auth.roles.includes('admin'));

const showTeamModal = ref(false);
const availableStaff = ref([]);
const loading = ref(false);
const processing = ref(null);

const fetchAvailableStaff = async () => {
    loading.value = true;
    try {
        const response = await axios.get(route('projects.discussions.available-staff', props.project.id));
        availableStaff.value = response.data;
    } catch (error) {
        console.error('Error fetching staff:', error);
    } finally {
        loading.value = false;
    }
};

const assignMember = async (userId) => {
    processing.value = userId;
    try {
        await axios.post(route('projects.discussions.assign', props.project.id), { user_id: userId });
        await fetchAvailableStaff();
        emit('memberAdded');
    } catch (error) {
        alert('Error assigning member');
    } finally {
        processing.value = null;
    }
};

const unassignMember = async (userId) => {
    // Basic confirmation for removal
    if (!confirm('Remove this staff member from the project?')) return;
    
    processing.value = userId;
    try {
        await axios.delete(route('projects.discussions.unassign', [props.project.id, userId]));
        emit('memberRemoved');
    } catch (error) {
        alert('Error removing member');
    } finally {
        processing.value = null;
    }
};

const openModal = () => {
    showTeamModal.value = true;
    fetchAvailableStaff();
};

const sortedMembers = computed(() => {
    return [...props.members].sort((a, b) => {
        const aOnline = isOnline(a.id);
        const bOnline = isOnline(b.id);
        if (aOnline && !bOnline) return -1;
        if (!aOnline && bOnline) return 1;
        return 0;
    });
});
</script>

<template>
    <div class="online-users card border-0 shadow-sm mt-3">
        <div class="card-header bg-white py-2 border-bottom d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold small text-uppercase text-muted">Project Members</h6>
            <button v-if="isAdmin" @click="openModal" class="btn btn-icon-xs btn-light rounded-circle shadow-none border-0" title="Manage Team">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tabler-icon tabler-icon-user-plus"><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"></path><path d="M16 19h6"></path><path d="M19 16v6"></path><path d="M6 21v-2a4 4 0 0 1 4 -4h4"></path></svg>
            </button>
        </div>
        <div class="card-body p-2 online-members-scroll" style="max-height: 300px; overflow-y: auto;">
            <div v-for="member in sortedMembers" :key="member.id" class="d-flex align-items-center gap-2 p-2 rounded hover-bg-light transition-colors">
                <div class="position-relative">
                    <div class="avatar-xs rounded-circle bg-light d-flex align-items-center justify-content-center fw-bold text-muted small">
                        {{ member.avatar_initial }}
                    </div>
                    <span v-if="isOnline(member.id)" class="online-dot position-absolute bottom-0 end-0 rounded-circle border border-white"></span>
                </div>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="fw-semibold text-truncate small" :class="{ 'text-dark': isOnline(member.id), 'text-muted opacity-75': !isOnline(member.id) }">
                        {{ member.name }}
                    </div>
                    <div class="text-muted x-small d-flex justify-content-between align-items-center">
                        <span>{{ member.role }}</span>
                        <button v-if="isAdmin && member.role === 'staff'" @click="unassignMember(member.id)" class="btn btn-link p-0 text-danger opacity-0 group-hover-opacity-100 x-small text-decoration-none">Remove</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Team Management Modal -->
        <Modal :show="showTeamModal" @close="showTeamModal = false" max-width="md">
            <div class="p-6 bg-white dark:bg-gray-900 rounded-2xl overflow-hidden shadow-2xl border border-gray-100 dark:border-gray-800">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tabler-icon tabler-icon-users-group text-primary"><path d="M10 13a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path><path d="M8 21v-1a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v1"></path><path d="M15 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path><path d="M17 10h2a2 2 0 0 1 2 2v1"></path><path d="M5 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path><path d="M3 13v-1a2 2 0 0 1 2 -2h2"></path></svg>
                        Project Staff Assignment
                    </h2>
                    <button @click="showTeamModal = false" class="btn-close shadow-none x-small border-0"></button>
                </div>

                <div class="mb-4">
                    <div v-if="loading" class="text-center py-4">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        <span class="ms-2 text-muted small">Loading staff...</span>
                    </div>
                    <div v-else-if="availableStaff.length === 0" class="text-center py-4 bg-light rounded-3 border border-dashed">
                        <p class="text-muted small mb-0">No more staff members available to assign.</p>
                    </div>
                    <div v-else class="available-list" style="max-height: 250px; overflow-y: auto;">
                        <div v-for="user in availableStaff" :key="user.id" class="d-flex align-items-center justify-content-between p-3 rounded-3 hover-bg-light border-bottom border-light transition-all mb-1">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-sm rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-bold">
                                    {{ user.name.charAt(0).toUpperCase() }}
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-semibold small text-dark">{{ user.name }}</h6>
                                    <span class="text-muted x-small uppercase tracking-wider">{{ user.role || 'Staff' }}</span>
                                </div>
                            </div>
                            <PrimaryButton @click="assignMember(user.id)" :disabled="processing === user.id" class="!py-1.5 !px-4 !text-xs rounded-pill">
                                <span v-if="processing === user.id" class="spinner-border spinner-border-sm me-1 shadow-none" role="status"></span>
                                Assign
                            </PrimaryButton>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="showTeamModal = false" class="rounded-xl px-5 opacity-75 hover-opacity-100 border-none bg-gray-100 text-gray-700">
                        Close
                    </SecondaryButton>
                </div>
            </div>
        </Modal>
    </div>
</template>

<style scoped>
.avatar-xs {
    width: 28px;
    height: 28px;
    font-size: 10px;
}

.online-dot {
    width: 8px;
    height: 8px;
    background-color: #22c55e;
}

.hover-bg-light:hover {
    background-color: #f8fafc;
}

.transition-colors {
    transition: background-color 0.2s;
}

.x-small {
    font-size: 0.7rem;
}

.online-members-scroll::-webkit-scrollbar {
    width: 4px;
}

.online-members-scroll::-webkit-scrollbar-track {
    background: transparent;
}

.online-members-scroll::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}

.online-members-scroll::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}
</style>
