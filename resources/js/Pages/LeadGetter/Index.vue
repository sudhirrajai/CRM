<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    groups: {
        type: Array,
        default: () => []
    }
});

const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingGroup = ref(null);

const createForm = useForm({
    name: '',
    description: '',
});

const editForm = useForm({
    name: '',
    description: '',
});

function openCreate() {
    createForm.reset();
    showCreateModal.value = true;
}

function submitCreate() {
    createForm.post(route('lead-getter.groups.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        },
    });
}

function openEdit(group) {
    editingGroup.value = group;
    editForm.name = group.name;
    editForm.description = group.description || '';
    showEditModal.value = true;
}

function submitEdit() {
    editForm.put(route('lead-getter.groups.update', editingGroup.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showEditModal.value = false;
            editingGroup.value = null;
        },
    });
}

function deleteGroup(group) {
    if (confirm(`Delete group "${group.name}" and all its tasks and results?`)) {
        router.delete(route('lead-getter.groups.destroy', group.id));
    }
}
</script>

<template>
    <Head title="Lead Getter" />

    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="page-title mb-0">Lead Getter</h4>
                    <button @click="openCreate" class="btn btn-primary">
                        <i class="ti ti-plus me-1"></i> New Group
                    </button>
                </div>
            </div>
        </div>

        <!-- Success/Error Alerts -->
        <div v-if="$page.props.flash.success" class="alert alert-success alert-dismissible fade show" role="alert">
            {{ $page.props.flash.success }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <div v-if="$page.props.flash.error" class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ $page.props.flash.error }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <!-- Info Banner -->
        <div v-if="groups.length === 0" class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="ti ti-radar" style="font-size: 4rem; color: var(--bs-primary); opacity: 0.6;"></i>
                        </div>
                        <h4 class="text-muted mb-2">No Lead Groups Yet</h4>
                        <p class="text-muted mb-4">Create a group to organize your lead generation searches.<br>Each group acts as a category for your search tasks.</p>
                        <button @click="openCreate" class="btn btn-primary">
                            <i class="ti ti-plus me-1"></i> Create Your First Group
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Groups Grid -->
        <div v-else class="row">
            <div v-for="group in groups" :key="group.id" class="col-md-6 col-xl-4">
                <div class="card card-hover">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div>
                                <h5 class="card-title mb-1">
                                    <Link :href="route('lead-getter.groups.show', group.id)" class="text-dark">
                                        {{ group.name }}
                                    </Link>
                                </h5>
                                <p class="text-muted small mb-0" v-if="group.description">{{ group.description }}</p>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                    <i class="ti ti-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="#" @click.prevent="openEdit(group)"><i class="ti ti-edit me-1"></i> Edit</a></li>
                                    <li><a class="dropdown-item text-danger" href="#" @click.prevent="deleteGroup(group)"><i class="ti ti-trash me-1"></i> Delete</a></li>
                                </ul>
                            </div>
                        </div>

                        <div class="row text-center mt-3">
                            <div class="col-4">
                                <h5 class="fw-bold mb-0">{{ group.tasks_count || 0 }}</h5>
                                <small class="text-muted">Searches</small>
                            </div>
                            <div class="col-4">
                                <h5 class="fw-bold mb-0">{{ group.total_results_count || 0 }}</h5>
                                <small class="text-muted">Results</small>
                            </div>
                            <div class="col-4">
                                <h5 class="fw-bold text-success mb-0">{{ group.qualified_results_count || 0 }}</h5>
                                <small class="text-muted">Qualified</small>
                            </div>
                        </div>

                        <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="ti ti-user me-1"></i> {{ group.user?.name || 'N/A' }}
                            </small>
                            <Link :href="route('lead-getter.groups.show', group.id)" class="btn btn-sm btn-outline-primary">
                                <i class="ti ti-arrow-right me-1"></i> View
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <div v-if="showCreateModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Create New Group</h5>
                        <button type="button" class="btn-close" @click="showCreateModal = false"></button>
                    </div>
                    <form @submit.prevent="submitCreate">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Group Name <span class="text-danger">*</span></label>
                                <input type="text" v-model="createForm.name" class="form-control" placeholder="e.g., Web Design Prospects - NYC" required>
                                <div v-if="createForm.errors.name" class="text-danger small mt-1">{{ createForm.errors.name }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea v-model="createForm.description" class="form-control" rows="3" placeholder="Optional description for this group..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" @click="showCreateModal = false">Cancel</button>
                            <button type="submit" class="btn btn-primary" :disabled="createForm.processing">
                                <i class="ti ti-plus me-1"></i> Create Group
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div v-if="showEditModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Group</h5>
                        <button type="button" class="btn-close" @click="showEditModal = false"></button>
                    </div>
                    <form @submit.prevent="submitEdit">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Group Name <span class="text-danger">*</span></label>
                                <input type="text" v-model="editForm.name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea v-model="editForm.description" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" @click="showEditModal = false">Cancel</button>
                            <button type="submit" class="btn btn-primary" :disabled="editForm.processing">
                                <i class="ti ti-device-floppy me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.card-hover {
    transition: all 0.2s ease;
    border: 1px solid transparent;
}
.card-hover:hover {
    border-color: var(--bs-primary);
    box-shadow: 0 0.25rem 1rem rgba(var(--bs-primary-rgb), 0.15);
    transform: translateY(-2px);
}
</style>
