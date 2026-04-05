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
    <div class="online-users card border shadow-none rounded mb-0">
        <div class="card-header bg-light py-2 px-3 d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-semibold text-uppercase card-header-title">Project Members</h6>
            <button v-if="isAdmin" @click="openModal" class="btn btn-sm btn-outline-primary d-flex align-items-center justify-content-center shadow-none btn-add-member" title="Manage Team">
                <i class="ti ti-plus fw-bold" style="font-size: 0.7rem;"></i>
            </button>
        </div>
        <div class="card-body p-1 online-members-scroll" style="max-height: 320px; overflow-y: auto;">
            <div v-for="member in sortedMembers" :key="member.id" class="member-item d-flex align-items-center gap-2 p-2 px-3 rounded transition-all group">
                <div class="position-relative">
                    <div class="avatar-circle rounded-circle d-flex align-items-center justify-content-center fw-bold"
                         :class="isOnline(member.id) ? 'avatar-online' : 'avatar-offline'">
                        {{ member.avatar_initial }}
                    </div>
                    <span v-if="isOnline(member.id)" class="status-dot-online position-absolute bottom-0 end-0 rounded-circle"></span>
                </div>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="fw-semibold text-truncate member-name" :class="isOnline(member.id) ? 'text-dark' : 'text-muted'">
                        {{ member.name }}
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="role-badge text-uppercase text-muted">{{ member.role }}</span>
                        <button v-if="isAdmin && member.role === 'staff'" @click="unassignMember(member.id)" class="btn-remove opacity-0 group-hover-opacity-100 transition-all">
                            <i class="ti ti-trash" style="font-size: 0.65rem;"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Team Management Modal -->
        <Modal :show="showTeamModal" @close="showTeamModal = false" max-width="md">
            <div class="p-4 bg-white rounded">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="ti ti-users-plus text-primary fs-4"></i>
                        Manage Project Team
                    </h5>
                    <button @click="showTeamModal = false" class="btn-close shadow-none opacity-50"></button>
                </div>

                <div class="mb-4">
                    <div v-if="loading" class="text-center py-5">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        <p class="mt-2 text-muted small">Discovering available staff...</p>
                    </div>
                    <div v-else-if="availableStaff.length === 0" class="text-center py-5 bg-light rounded border">
                        <i class="ti ti-mood-empty fs-2 text-muted mb-2"></i>
                        <p class="text-muted small mb-0">Everyone is already assigned!</p>
                    </div>
                    <div v-else class="available-list pe-1" style="max-height: 300px; overflow-y: auto;">
                        <div v-for="user in availableStaff" :key="user.id" class="d-flex align-items-center justify-content-between p-3 rounded bg-white border transition-all mb-2 staff-item">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-circle rounded-circle avatar-online d-flex align-items-center justify-content-center fw-bold">
                                    {{ user.name.charAt(0).toUpperCase() }}
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-semibold small text-dark">{{ user.name }}</h6>
                                    <span class="text-muted role-badge text-uppercase fw-semibold">{{ user.role || 'Staff' }}</span>
                                </div>
                            </div>
                            <button @click="assignMember(user.id)" :disabled="processing === user.id" class="btn btn-sm btn-primary px-3 py-1">
                                <span v-if="processing === user.id" class="spinner-border spinner-border-sm me-1" role="status"></span>
                                Assign
                            </button>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button @click="showTeamModal = false" class="btn btn-light px-4 text-muted small fw-semibold">Done</button>
                </div>
            </div>
        </Modal>
    </div>
</template>

<style scoped>
.card-header-title {
    font-size: 0.65rem;
    letter-spacing: 0.1em;
    color: #6c757d;
}

.avatar-circle {
    width: 36px;
    height: 36px;
    font-size: 12px;
    border: 2px solid transparent;
    transition: all 0.2s ease;
}

.avatar-online {
    background-color: rgba(62, 96, 213, 0.08);
    color: var(--bs-primary, #3e60d5);
    border-color: rgba(62, 96, 213, 0.15);
}

.avatar-offline {
    background-color: #f8f9fa;
    color: #adb5bd;
    border-color: #e9ecef;
}

.status-dot-online {
    width: 9px;
    height: 9px;
    background-color: #22c55e;
    border: 2px solid #fff;
    box-shadow: 0 0 0 1px rgba(34, 197, 94, 0.15);
}

.member-item:hover {
    background-color: #f8f9fa;
}

.member-name {
    font-size: 0.8rem;
    transition: color 0.2s ease;
}

.role-badge {
    font-size: 0.55rem;
    letter-spacing: 0.05em;
    font-weight: 600;
}

.btn-add-member {
    width: 24px;
    height: 24px;
    padding: 0;
    border-radius: 4px;
}

.btn-remove {
    background: transparent;
    border: none;
    color: #ef4444;
    padding: 0;
}

.btn-remove:hover { color: #dc2626; transform: scale(1.1); }

.staff-item:hover { 
    box-shadow: 0 2px 4px rgba(0,0,0,0.04);
    border-color: #dee2e6 !important;
}

.transition-all {
    transition: all 0.2s ease;
}

.online-members-scroll::-webkit-scrollbar { width: 3px; }
.online-members-scroll::-webkit-scrollbar-thumb { background: #dee2e6; border-radius: 10px; }

.member-item:hover .btn-remove {
    opacity: 1 !important;
}
</style>
