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
    <div class="online-users card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-light-subtle py-2 px-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <div class="header-dot bg-indigo animate-pulse"></div>
                <h6 class="mb-0 fw-bold x-small text-uppercase text-dark-slate letter-spacing-2">Project Members</h6>
            </div>
            <button v-if="isAdmin" @click="openModal" class="btn btn-add-member d-flex align-items-center justify-content-center shadow-none transition-all" title="Manage Team">
                <i class="ti ti-plus fw-bold"></i>
            </button>
        </div>
        <div class="card-body p-1 online-members-scroll" style="max-height: 320px; overflow-y: auto;">
            <div v-for="member in sortedMembers" :key="member.id" class="member-item d-flex align-items-center gap-2 p-2 px-3 rounded-3 transition-all group">
                <div class="position-relative">
                    <div class="avatar-box rounded-circle d-flex align-items-center justify-content-center fw-bold transition-all shadow-sm"
                         :class="isOnline(member.id) ? 'bg-white text-indigo border-indigo' : 'bg-light text-muted border'">
                        {{ member.avatar_initial }}
                    </div>
                    <span v-if="isOnline(member.id)" class="online-status-pulse position-absolute bottom-0 end-0 rounded-circle active-shadow"></span>
                </div>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="fw-bold text-truncate small mb-0 name-text" :class="isOnline(member.id) ? 'text-dark' : 'text-secondary opacity-75'">
                        {{ member.name }}
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="role-badge x-small fw-semibold text-uppercase opacity-50">{{ member.role }}</span>
                        <button v-if="isAdmin && member.role === 'staff'" @click="unassignMember(member.id)" class="btn-remove opacity-0 group-hover-opacity-100 transition-all">
                            <i class="ti ti-trash x-small"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Team Management Modal -->
        <Modal :show="showTeamModal" @close="showTeamModal = false" max-width="md">
            <div class="p-4 p-md-5 bg-white dark:bg-gray-900 rounded-4 shadow-2xl border-0 overflow-hidden animate-zoom-in">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="ti ti-users-plus text-indigo fs-4"></i>
                        Manage Project Team
                    </h5>
                    <button @click="showTeamModal = false" class="btn-close shadow-none opacity-50 hover-opacity-100 x-small"></button>
                </div>

                <div class="mb-4">
                    <div v-if="loading" class="text-center py-5">
                        <div class="spinner-border spinner-border-sm text-indigo" role="status"></div>
                        <p class="mt-2 text-muted x-small">Discovering available staff...</p>
                    </div>
                    <div v-else-if="availableStaff.length === 0" class="text-center py-5 bg-light rounded-4 border-dash">
                        <i class="ti ti-mood-empty fs-2 text-muted mb-2"></i>
                        <p class="text-muted small mb-0">Everyone is already assigned!</p>
                    </div>
                    <div v-else class="available-list pe-1" style="max-height: 300px; overflow-y: auto;">
                        <div v-for="user in availableStaff" :key="user.id" class="d-flex align-items-center justify-content-between p-3 rounded-4 bg-white border border-light transition-all mb-2 hover-shadow-sm">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-sm rounded-circle bg-indigo-subtle text-indigo d-flex align-items-center justify-content-center fw-bold">
                                    {{ user.name.charAt(0).toUpperCase() }}
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold small text-dark">{{ user.name }}</h6>
                                    <span class="text-muted x-small text-uppercase fw-semibold">{{ user.role || 'Staff' }}</span>
                                </div>
                            </div>
                            <button @click="assignMember(user.id)" :disabled="processing === user.id" class="btn btn-sm btn-indigo rounded-pill px-3 py-1">
                                <span v-if="processing === user.id" class="spinner-border spinner-border-sm me-1" role="status"></span>
                                Assign
                            </button>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button @click="showTeamModal = false" class="btn btn-light rounded-pill px-4 text-muted small fw-bold">Done</button>
                </div>
            </div>
        </Modal>
    </div>
</template>

<style scoped>
.avatar-box {
    width: 38px;
    height: 38px;
    font-size: 13px;
    border: 2px solid transparent;
}

.avatar-box.bg-white { border-color: rgba(76, 73, 226, 0.2) !important; }

.header-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
}

.bg-indigo { background-color: #4c49e2 !important; }
.bg-indigo-subtle { background-color: rgba(76, 73, 226, 0.08) !important; }
.text-indigo { color: #4c49e2 !important; }
.border-indigo { border-color: rgba(76, 73, 226, 0.3) !important; }
.btn-indigo { background-color: #4c49e2 !important; color: #fff !important; }

.online-status-pulse {
    width: 10px;
    height: 10px;
    background-color: #22c55e;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.1);
}

.active-shadow {
    animation: pulse-green 2s infinite;
}

@keyframes pulse-green {
    0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4); }
    70% { box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); }
    100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
}

.bg-light-subtle { background-color: #f8fafc !important; }
.text-dark-slate { color: #334155 !important; }

.letter-spacing-2 { letter-spacing: 0.15em; }

.member-item:hover {
    background-color: #ffffff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.name-text { transition: color 0.2s ease; }

.role-badge {
    font-size: 0.6rem;
    letter-spacing: 0.05em;
}

.btn-add-member {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: transparent;
    color: #4c49e2;
    border: 1.5px dashed #4c49e2;
}

.btn-add-member:hover {
    background: #4c49e2;
    color: #fff;
    border-style: solid;
}

.btn-remove {
    background: transparent;
    border: none;
    color: #ef4444;
    padding: 0;
}

.btn-remove:hover { color: #dc2626; transform: scale(1.1); }

.border-dash { border: 2px dashed #e2e8f0; }

.hover-shadow-sm:hover { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }

.animate-zoom-in {
    animation: zoomIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes zoomIn {
    from { opacity: 0; transform: scale(0.9); }
    to { opacity: 1; transform: scale(1); }
}

.online-members-scroll::-webkit-scrollbar { width: 4px; }
.online-members-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>
