<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

defineProps({
    clients: { type: Array, required: true },
    projects: { type: Array, required: true },
    currencies: { type: Array, required: true },
});

const form = useForm({
    client_id: '',
    project_id: '',
    currency_id: '',
    invoice_number: 'INV-' + Math.floor(1000 + Math.random() * 9000),
    issue_date: new Date().toISOString().split('T')[0],
    due_date: '',
    total_amount: 0,
    status: 'draft',
    notes: '',
    payment_mode: '',
    payment_reference: '',
    payment_note: '',
    send_email: false,
    selected_crs: [], // To track selected CR IDs
});

const selectedProject = ref(null);

watch(() => form.project_id, (newProjectId) => {
    if (newProjectId) {
        selectedProject.value = props.projects.find(p => p.id === newProjectId);
        form.selected_crs = [];
    } else {
        selectedProject.value = null;
        form.selected_crs = [];
    }
});

const toggleCR = (cr) => {
    const index = form.selected_crs.indexOf(cr.id);
    if (index > -1) {
        form.selected_crs.splice(index, 1);
        form.total_amount = Math.max(0, parseFloat(form.total_amount) - parseFloat(cr.amount)).toFixed(2);
    } else {
        form.selected_crs.push(cr.id);
        form.total_amount = (parseFloat(form.total_amount) + parseFloat(cr.amount)).toFixed(2);
    }
};

const submit = () => {
    form.post(route('invoices.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Generate Invoice" />

    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">Generate Invoice</h4>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-9 col-xl-8">
                <div class="card">
                    <div class="card-body">
                        <form @submit.prevent="submit">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="invoice_number" class="form-label">Invoice Number <span class="text-danger">*</span></label>
                                    <input type="text" id="invoice_number" v-model="form.invoice_number" class="form-control" :class="{ 'is-invalid': form.errors.invoice_number }" required>
                                    <div class="invalid-feedback" v-if="form.errors.invoice_number">{{ form.errors.invoice_number }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="client_id" class="form-label">Client <span class="text-danger">*</span></label>
                                    <select id="client_id" v-model="form.client_id" class="form-select" :class="{ 'is-invalid': form.errors.client_id }" required>
                                        <option value="" disabled>Select Client</option>
                                        <option v-for="client in clients" :key="client.id" :value="client.id">
                                            {{ client.name }} ({{ client.company || 'Individual' }})
                                        </option>
                                    </select>
                                    <div class="invalid-feedback" v-if="form.errors.client_id">{{ form.errors.client_id }}</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="project_id" class="form-label">Related Project</label>
                                    <select id="project_id" v-model="form.project_id" class="form-select" :class="{ 'is-invalid': form.errors.project_id }">
                                        <option value="">None (General Billing)</option>
                                        <option v-for="project in projects" :key="project.id" :value="project.id">
                                            {{ project.name }}
                                        </option>
                                    </select>
                                    <div class="invalid-feedback" v-if="form.errors.project_id">{{ form.errors.project_id }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="currency_id" class="form-label">Currency <span class="text-danger">*</span></label>
                                    <select id="currency_id" v-model="form.currency_id" class="form-select" :class="{ 'is-invalid': form.errors.currency_id }" required>
                                        <option value="" disabled>Select Currency</option>
                                        <option v-for="currency in currencies" :key="currency.id" :value="currency.id">
                                            {{ currency.name }} ({{ currency.code }})
                                        </option>
                                    </select>
                                    <div class="invalid-feedback" v-if="form.errors.currency_id">{{ form.errors.currency_id }}</div>
                                </div>
                            </div>

                            <div v-if="selectedProject && selectedProject.change_requests?.length > 0" class="row mb-3">
                                <div class="col-12">
                                    <label class="form-label">Select Change Requests to Include</label>
                                    <div class="list-group">
                                        <label v-for="cr in selectedProject.change_requests" :key="cr.id" class="list-group-item d-flex justify-content-between align-items-center cursor-pointer">
                                            <div>
                                                <input type="checkbox" class="form-check-input me-2" :checked="form.selected_crs.includes(cr.id)" @change="toggleCR(cr)">
                                                <span>{{ cr.title }}</span>
                                                <small class="text-muted d-block">{{ cr.description }}</small>
                                            </div>
                                            <span class="badge bg-primary rounded-pill">{{ selectedProject.client?.currency?.code || 'USD' }} {{ cr.amount }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="issue_date" class="form-label">Issue Date <span class="text-danger">*</span></label>
                                    <input type="date" id="issue_date" v-model="form.issue_date" class="form-control" :class="{ 'is-invalid': form.errors.issue_date }" required>
                                    <div class="invalid-feedback" v-if="form.errors.issue_date">{{ form.errors.issue_date }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="due_date" class="form-label">Due Date <span class="text-danger">*</span></label>
                                    <input type="date" id="due_date" v-model="form.due_date" class="form-control" :class="{ 'is-invalid': form.errors.due_date }" required>
                                    <div class="invalid-feedback" v-if="form.errors.due_date">{{ form.errors.due_date }}</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="total_amount" class="form-label">Total Amount <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" id="total_amount" v-model="form.total_amount" class="form-control" :class="{ 'is-invalid': form.errors.total_amount }" required>
                                    <div class="invalid-feedback" v-if="form.errors.total_amount">{{ form.errors.total_amount }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select id="status" v-model="form.status" class="form-select" :class="{ 'is-invalid': form.errors.status }" required>
                                        <option value="draft">Draft</option>
                                        <option value="sent">Sent</option>
                                        <option value="paid">Paid</option>
                                        <option value="overdue">Overdue</option>
                                    </select>
                                    <div class="invalid-feedback" v-if="form.errors.status">{{ form.errors.status }}</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea id="notes" v-model="form.notes" class="form-control" :class="{ 'is-invalid': form.errors.notes }" rows="3"></textarea>
                                    <div class="invalid-feedback" v-if="form.errors.notes">{{ form.errors.notes }}</div>
                                </div>
                            </div>

                            <div v-if="form.status === 'paid'" class="card border border-light mt-4 mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Payment Details</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="payment_mode" class="form-label">Payment Mode</label>
                                            <select id="payment_mode" v-model="form.payment_mode" class="form-select" :class="{ 'is-invalid': form.errors.payment_mode }">
                                                <option value="">Select Mode</option>
                                                <option value="bank_transfer">Bank Transfer</option>
                                                <option value="credit_card">Credit Card</option>
                                                <option value="paypal">PayPal</option>
                                                <option value="cash">Cash</option>
                                                <option value="other">Other</option>
                                            </select>
                                            <div class="invalid-feedback" v-if="form.errors.payment_mode">{{ form.errors.payment_mode }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="payment_reference" class="form-label">Payment Reference</label>
                                            <input type="text" id="payment_reference" v-model="form.payment_reference" class="form-control" :class="{ 'is-invalid': form.errors.payment_reference }" placeholder="e.g. Transaction ID">
                                            <div class="invalid-feedback" v-if="form.errors.payment_reference">{{ form.errors.payment_reference }}</div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <label for="payment_note" class="form-label">Payment Note</label>
                                            <textarea id="payment_note" v-model="form.payment_note" class="form-control" :class="{ 'is-invalid': form.errors.payment_note }" rows="2" placeholder="Optional details..."></textarea>
                                            <div class="invalid-feedback" v-if="form.errors.payment_note">{{ form.errors.payment_note }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="form-check">
                                        <input type="checkbox" id="send_email" v-model="form.send_email" class="form-check-input">
                                        <label for="send_email" class="form-check-label user-select-none">Send copy to client via email</label>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <Link :href="route('invoices.index')" class="btn btn-light me-2">Cancel</Link>
                                <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                    Generate Invoice
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
