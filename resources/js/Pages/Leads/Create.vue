<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    stages: { type: Array, required: true },
    currencies: { type: Array, required: true },
    users: { type: Array, required: true },
    clients: { type: Array, required: true },
});

const defaultStage = props.stages.find(s => s.is_default) || props.stages[0];

const form = useForm({
    title: '',
    lead_pipeline_stage_id: defaultStage?.id || '',
    client_id: '',
    contact_name: '',
    contact_email: '',
    contact_phone: '',
    company: '',
    value: '',
    currency_id: '',
    source: 'other',
    priority: 'medium',
    assigned_to: '',
    expected_close_date: '',
    description: '',
    auto_convert: false,
});

const submit = () => {
    form.post(route('leads.store'), {
        preserveScroll: true,
    });
};

function onClientSelect() {
    if (form.client_id) {
        const client = props.clients.find(c => c.id === form.client_id);
        if (client) {
            form.contact_name = form.contact_name || client.name;
            form.contact_email = form.contact_email || client.email;
            form.company = form.company || client.company;
        }
    }
}
</script>

<template>
    <Head title="Create Lead" />

    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column mb-4 mt-2">
                    <div class="flex-grow-1">
                        <h4 class="fs-18 text-uppercase fw-bold m-0">Create New Lead</h4>
                        <p class="text-muted mb-0 mt-1">Add a new lead to your pipeline</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                <div class="card">
                    <div class="card-body">
                        <form @submit.prevent="submit">
                            <!-- Lead Info -->
                            <h5 class="text-uppercase fs-13 mb-3"><i class="ti ti-info-circle me-1"></i> Lead Information</h5>
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label for="title" class="form-label">Lead Title <span class="text-danger">*</span></label>
                                    <input type="text" id="title" v-model="form.title" class="form-control" :class="{ 'is-invalid': form.errors.title }" required placeholder="e.g. New Website Project for Acme Corp">
                                    <div class="invalid-feedback" v-if="form.errors.title">{{ form.errors.title }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label for="stage" class="form-label">Pipeline Stage <span class="text-danger">*</span></label>
                                    <select id="stage" v-model="form.lead_pipeline_stage_id" class="form-select" :class="{ 'is-invalid': form.errors.lead_pipeline_stage_id }" required>
                                        <option v-for="stage in stages" :key="stage.id" :value="stage.id">{{ stage.name }}</option>
                                    </select>
                                    <div class="invalid-feedback" v-if="form.errors.lead_pipeline_stage_id">{{ form.errors.lead_pipeline_stage_id }}</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="source" class="form-label">Source <span class="text-danger">*</span></label>
                                    <select id="source" v-model="form.source" class="form-select" :class="{ 'is-invalid': form.errors.source }" required>
                                        <option value="website">Website</option>
                                        <option value="referral">Referral</option>
                                        <option value="social_media">Social Media</option>
                                        <option value="cold_call">Cold Call</option>
                                        <option value="email">Email</option>
                                        <option value="advertisement">Advertisement</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                                    <select id="priority" v-model="form.priority" class="form-select" :class="{ 'is-invalid': form.errors.priority }" required>
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="assigned_to" class="form-label">Assigned To</label>
                                    <select id="assigned_to" v-model="form.assigned_to" class="form-select">
                                        <option value="">Unassigned</option>
                                        <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Contact Info -->
                            <hr class="my-4">
                            <h5 class="text-uppercase fs-13 mb-3"><i class="ti ti-user me-1"></i> Contact Information</h5>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="client_id" class="form-label">Link to Existing Client</label>
                                    <select id="client_id" v-model="form.client_id" class="form-select" @change="onClientSelect">
                                        <option value="">None</option>
                                        <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.name }} {{ client.company ? '(' + client.company + ')' : '' }}</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="company" class="form-label">Company</label>
                                    <input type="text" id="company" v-model="form.company" class="form-control" placeholder="Company name">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="contact_name" class="form-label">Contact Name</label>
                                    <input type="text" id="contact_name" v-model="form.contact_name" class="form-control" placeholder="Full name">
                                </div>
                                <div class="col-md-4">
                                    <label for="contact_email" class="form-label">Contact Email</label>
                                    <input type="email" id="contact_email" v-model="form.contact_email" class="form-control" placeholder="email@example.com">
                                </div>
                                <div class="col-md-4">
                                    <label for="contact_phone" class="form-label">Contact Phone</label>
                                    <input type="text" id="contact_phone" v-model="form.contact_phone" class="form-control" placeholder="+91 XXX XXX XXXX">
                                </div>
                            </div>

                            <!-- Deal Info -->
                            <hr class="my-4">
                            <h5 class="text-uppercase fs-13 mb-3"><i class="ti ti-coin me-1"></i> Deal Information</h5>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="value" class="form-label">Deal Value</label>
                                    <input type="number" id="value" v-model="form.value" class="form-control" step="0.01" min="0" placeholder="0.00">
                                </div>
                                <div class="col-md-4">
                                    <label for="currency_id" class="form-label">Currency</label>
                                    <select id="currency_id" v-model="form.currency_id" class="form-select">
                                        <option value="">Select Currency</option>
                                        <option v-for="currency in currencies" :key="currency.id" :value="currency.id">{{ currency.name }} ({{ currency.code }})</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="expected_close_date" class="form-label">Expected Close Date</label>
                                    <input type="date" id="expected_close_date" v-model="form.expected_close_date" class="form-control">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="description" class="form-label">Description / Notes</label>
                                    <textarea id="description" v-model="form.description" class="form-control" rows="3" placeholder="Additional details about this lead..."></textarea>
                                </div>
                            </div>

                            <!-- Auto Convert Toggle -->
                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input v-model="form.auto_convert" type="checkbox" class="form-check-input" id="autoConvert">
                                        <label class="form-check-label" for="autoConvert">
                                            <strong>Auto-convert to Client</strong> when lead reaches "Won" stage
                                        </label>
                                        <div class="text-muted fs-12 mt-1">When enabled, a new Client record will be automatically created when this lead is moved to a Won stage.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <Link :href="route('leads.index')" class="btn btn-light me-2">Cancel</Link>
                                <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-1" role="status"></span>
                                    Create Lead
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
