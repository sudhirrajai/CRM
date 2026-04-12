<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    lead: { type: Object, required: true },
    stages: { type: Array, required: true },
    currencies: { type: Array, required: true },
    users: { type: Array, required: true },
    clients: { type: Array, required: true },
});

const defaultCurrencyId = usePage().props.defaultCurrency?.id || '';

const form = useForm({
    title: props.lead.title || '',
    lead_pipeline_stage_id: props.lead.lead_pipeline_stage_id || '',
    client_id: props.lead.client_id || '',
    contact_name: props.lead.contact_name || '',
    contact_email: props.lead.contact_email || '',
    contact_phone: props.lead.contact_phone || '',
    company: props.lead.company || '',
    value: props.lead.value || '',
    currency_id: props.lead.currency_id || defaultCurrencyId,
    source: props.lead.source || 'other',
    priority: props.lead.priority || 'medium',
    assigned_to: props.lead.assigned_to || '',
    expected_close_date: props.lead.expected_close_date || '',
    description: props.lead.description || '',
    auto_convert: props.lead.auto_convert || false,
    lost_reason: props.lead.lost_reason || '',
});

const submit = () => {
    form.put(route('leads.update', props.lead.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="'Edit Lead: ' + lead.title" />

    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column mb-4 mt-2">
                    <div class="flex-grow-1">
                        <h4 class="fs-18 text-uppercase fw-bold m-0">Edit Lead</h4>
                        <p class="text-muted mb-0 mt-1">{{ lead.title }}</p>
                    </div>
                    <Link :href="route('leads.show', lead.id)" class="btn btn-outline-secondary btn-sm">
                        <i class="ti ti-arrow-left me-1"></i> Back to Lead
                    </Link>
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
                                    <input type="text" id="title" v-model="form.title" class="form-control" :class="{ 'is-invalid': form.errors.title }" required>
                                    <div class="invalid-feedback" v-if="form.errors.title">{{ form.errors.title }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label for="stage" class="form-label">Pipeline Stage <span class="text-danger">*</span></label>
                                    <select id="stage" v-model="form.lead_pipeline_stage_id" class="form-select" required>
                                        <option v-for="stage in stages" :key="stage.id" :value="stage.id">{{ stage.name }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="source" class="form-label">Source <span class="text-danger">*</span></label>
                                    <select id="source" v-model="form.source" class="form-select" required>
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
                                    <select id="priority" v-model="form.priority" class="form-select" required>
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
                                    <select id="client_id" v-model="form.client_id" class="form-select">
                                        <option value="">None</option>
                                        <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.name }} {{ client.company ? '(' + client.company + ')' : '' }}</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="company" class="form-label">Company</label>
                                    <input type="text" id="company" v-model="form.company" class="form-control">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="contact_name" class="form-label">Contact Name</label>
                                    <input type="text" id="contact_name" v-model="form.contact_name" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label for="contact_email" class="form-label">Contact Email</label>
                                    <input type="email" id="contact_email" v-model="form.contact_email" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label for="contact_phone" class="form-label">Contact Phone</label>
                                    <input type="text" id="contact_phone" v-model="form.contact_phone" class="form-control">
                                </div>
                            </div>

                            <!-- Deal Info -->
                            <hr class="my-4">
                            <h5 class="text-uppercase fs-13 mb-3"><i class="ti ti-coin me-1"></i> Deal Information</h5>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="value" class="form-label">Deal Value</label>
                                    <input type="number" id="value" v-model="form.value" class="form-control" step="0.01" min="0">
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
                                    <textarea id="description" v-model="form.description" class="form-control" rows="3"></textarea>
                                </div>
                            </div>

                            <!-- Lost Reason (show only when in a 'lost' stage) -->
                            <div v-if="stages.find(s => s.id === form.lead_pipeline_stage_id)?.is_lost" class="row mb-3">
                                <div class="col-12">
                                    <label for="lost_reason" class="form-label text-danger">Reason for Loss</label>
                                    <input type="text" id="lost_reason" v-model="form.lost_reason" class="form-control" placeholder="Why was this lead lost?">
                                </div>
                            </div>

                            <!-- Auto Convert -->
                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input v-model="form.auto_convert" type="checkbox" class="form-check-input" id="autoConvert">
                                        <label class="form-check-label" for="autoConvert">
                                            <strong>Auto-convert to Client</strong> when lead reaches "Won" stage
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <Link :href="route('leads.show', lead.id)" class="btn btn-light me-2">Cancel</Link>
                                <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-1" role="status"></span>
                                    Update Lead
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
