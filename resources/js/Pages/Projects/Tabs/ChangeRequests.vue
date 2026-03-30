<script setup>
import { ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    project: {
        type: Object,
        required: true
    }
});

const page = usePage();
const isAdminOrStaff = page.props.auth.roles.includes('admin') || page.props.auth.roles.includes('staff');

const showCreateModal = ref(false);
const editingCR = ref(null);

const form = useForm({
    title: '',
    description: '',
    amount: '',
    status: 'pending'
});

const openCreateModal = () => {
    editingCR.value = null;
    form.reset();
    form.clearErrors();
    showCreateModal.value = true;
};

const openEditModal = (cr) => {
    editingCR.value = cr;
    form.title = cr.title;
    form.description = cr.description;
    form.amount = cr.amount;
    form.status = cr.status;
    form.clearErrors();
    showCreateModal.value = true;
};

const submit = () => {
    if (editingCR.value) {
        form.put(route('change-requests.update', editingCR.value.id), {
            onSuccess: () => {
                showCreateModal.value = false;
                form.reset();
            },
        });
    } else {
        form.post(route('projects.change-requests.store', props.project.id), {
            onSuccess: () => {
                showCreateModal.value = false;
                form.reset();
            },
        });
    }
};

const deleteCR = (id) => {
    if (confirm('Are you sure you want to delete this change request?')) {
        form.delete(route('change-requests.destroy', id));
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString();
};

const formatCurrency = (amount, currencyCode) => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: currencyCode || 'USD',
    }).format(amount);
};

const getStatusBadge = (status) => {
    switch (status) {
        case 'paid': return 'bg-success';
        case 'invoiced': return 'bg-info';
        default: return 'bg-warning';
    }
};
</script>

<template>
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="header-title mb-0">Change Requests</h4>
                <button v-if="isAdminOrStaff" @click="openCreateModal" class="btn btn-sm btn-primary">
                    <i class="ti ti-plus me-1"></i> Add CR
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-centered table-nowrap mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Invoice</th>
                            <th>Date Created</th>
                            <th v-if="isAdminOrStaff" style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="cr in project.change_requests" :key="cr.id">
                            <td>
                                <span class="fw-bold text-dark">{{ cr.title }}</span>
                                <p class="text-muted mb-0 small text-wrap" style="max-width: 300px;">{{ cr.description }}</p>
                            </td>
                            <td>{{ formatCurrency(cr.amount, project.client?.currency?.code) }}</td>
                            <td>
                                <span class="badge" :class="getStatusBadge(cr.status)">{{ cr.status.toUpperCase() }}</span>
                            </td>
                            <td>
                                <span v-if="cr.invoice_id" class="text-info">#{{ cr.invoice_id.substring(0, 8) }}...</span>
                                <span v-else class="text-muted">Not Invoiced</span>
                            </td>
                            <td>{{ formatDate(cr.created_at) }}</td>
                            <td v-if="isAdminOrStaff">
                                <button @click="openEditModal(cr)" class="btn btn-sm text-primary p-0 me-2">
                                    <i class="ti ti-edit fs-4"></i>
                                </button>
                                <button @click="deleteCR(cr.id)" class="btn btn-sm text-danger p-0">
                                    <i class="ti ti-trash fs-4"></i>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!project.change_requests || project.change_requests.length === 0">
                            <td colspan="6" class="text-center py-3 text-muted">No change requests added yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal implementation would normally be separate or use a lib, 
         using a simple conditional overlay for now since I don't know the exact modal component -->
    <div v-if="showCreateModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5)">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ editingCR ? 'Edit Change Request' : 'Add Change Request' }}</h5>
                    <button type="button" class="btn-close" @click="showCreateModal = false"></button>
                </div>
                <form @submit.prevent="submit">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" v-model="form.title" class="form-control" required>
                            <div v-if="form.errors.title" class="text-danger small">{{ form.errors.title }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amount ({{ project.client?.currency?.code || 'USD' }}) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" v-model="form.amount" class="form-control" required>
                            <div v-if="form.errors.amount" class="text-danger small">{{ form.errors.amount }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea v-model="form.description" class="form-control" rows="3" required></textarea>
                            <div v-if="form.errors.description" class="text-danger small">{{ form.errors.description }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select v-model="form.status" class="form-select">
                                <option value="pending">Pending</option>
                                <option value="invoiced">Invoiced</option>
                                <option value="paid">Paid</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" @click="showCreateModal = false">Cancel</button>
                        <button type="submit" class="btn btn-primary" :disabled="form.processing">
                            {{ editingCR ? 'Update' : 'Save' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
