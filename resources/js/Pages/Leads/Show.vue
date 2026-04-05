<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    lead: { type: Object, required: true },
    stages: { type: Array, required: true },
});

const activityForm = useForm({
    type: 'note',
    description: '',
});

const showConvertConfirm = ref(false);

const isWon = computed(() => props.lead.stage?.is_won);
const isLost = computed(() => props.lead.stage?.is_lost);
const isConverted = computed(() => !!props.lead.converted_client_id);

function submitActivity() {
    activityForm.post(route('leads.activities.store', props.lead.id), {
        preserveScroll: true,
        onSuccess: () => activityForm.reset('description'),
    });
}

function convertToClient() {
    router.post(route('leads.convert', props.lead.id));
    showConvertConfirm.value = false;
}

function deleteLead() {
    if (confirm(`Delete "${props.lead.title}"? This action cannot be undone.`)) {
        router.delete(route('leads.destroy', props.lead.id));
    }
}

function formatValue(value, currency) {
    if (!value) return '-';
    const sym = currency?.symbol || '$';
    const formatted = parseFloat(value).toLocaleString();
    return currency?.symbol_position === 'suffix' ? `${formatted} ${sym}` : `${sym}${formatted}`;
}

function formatDate(date) {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function formatDateTime(date) {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function getActivityIcon(type) {
    const map = {
        note: 'ti-note',
        call: 'ti-phone',
        email: 'ti-mail',
        meeting: 'ti-calendar-event',
        task: 'ti-checkbox',
        stage_change: 'ti-arrows-exchange',
    };
    return map[type] || 'ti-note';
}

function getActivityColor(type) {
    const map = {
        note: 'primary',
        call: 'success',
        email: 'info',
        meeting: 'warning',
        task: 'secondary',
        stage_change: 'dark',
    };
    return map[type] || 'primary';
}

function getSourceLabel(source) {
    const map = {
        website: 'Website', referral: 'Referral', social_media: 'Social Media',
        cold_call: 'Cold Call', email: 'Email', advertisement: 'Advertisement', other: 'Other'
    };
    return map[source] || source;
}

function getPriorityClass(priority) {
    const map = { urgent: 'danger', high: 'warning', medium: 'info', low: 'secondary' };
    return map[priority] || 'secondary';
}

// Pipeline progress
const currentStageIndex = computed(() => {
    return props.stages.findIndex(s => s.id === props.lead.lead_pipeline_stage_id);
});
</script>

<template>
    <Head :title="lead.title" />

    <AuthenticatedLayout>
        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column mb-4 mt-2">
                    <div class="flex-grow-1">
                        <h4 class="fs-18 text-uppercase fw-bold m-0">Lead Details</h4>
                        <p class="text-muted mb-0 mt-1">{{ lead.title }}</p>
                    </div>
                    <div class="d-flex gap-2">
                        <Link :href="route('leads.index')" class="btn btn-outline-secondary btn-sm">
                            <i class="ti ti-arrow-left me-1"></i> Back
                        </Link>
                        <Link :href="route('leads.edit', lead.id)" class="btn btn-primary btn-sm">
                            <i class="ti ti-edit me-1"></i> Edit
                        </Link>
                        <button @click="deleteLead" class="btn btn-outline-danger btn-sm">
                            <i class="ti ti-trash me-1"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pipeline Progress Bar -->
        <div class="card mb-3">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-1 overflow-auto">
                    <div
                        v-for="(stage, index) in stages"
                        :key="stage.id"
                        class="pipeline-step flex-fill text-center py-2 px-3 rounded position-relative"
                        :class="{
                            'pipeline-step--active': stage.id === lead.lead_pipeline_stage_id,
                            'pipeline-step--passed': index < currentStageIndex,
                            'pipeline-step--upcoming': index > currentStageIndex
                        }"
                        :style="{
                            backgroundColor: stage.id === lead.lead_pipeline_stage_id ? stage.color : (index < currentStageIndex ? stage.color + '30' : ''),
                            color: stage.id === lead.lead_pipeline_stage_id ? '#fff' : '',
                            borderColor: stage.color
                        }"
                    >
                        <span class="fs-12 fw-semibold">{{ stage.name }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <!-- Main Content -->
            <div class="col-xl-8">
                <!-- Status Alerts -->
                <div v-if="isWon && !isConverted" class="alert alert-success d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <i class="ti ti-trophy me-2 fs-18"></i>
                        <strong>This lead is Won!</strong> Convert it to a Client to start working on projects.
                    </div>
                    <button @click="showConvertConfirm = true" class="btn btn-success btn-sm">
                        <i class="ti ti-transform me-1"></i> Convert to Client
                    </button>
                </div>

                <div v-if="isConverted" class="alert alert-info d-flex align-items-center mb-3">
                    <i class="ti ti-check me-2 fs-18"></i>
                    <span>Converted to client: <Link :href="route('clients.show', lead.converted_client_id)" class="fw-semibold text-info">{{ lead.converted_client?.name || 'View Client' }}</Link></span>
                </div>

                <div v-if="isLost" class="alert alert-danger d-flex align-items-center mb-3">
                    <i class="ti ti-x me-2 fs-18"></i>
                    <span><strong>Lead Lost</strong>{{ lead.lost_reason ? ': ' + lead.lost_reason : '' }}</span>
                </div>

                <!-- Lead Info Card -->
                <div class="card mb-3">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0">Lead Information</h5>
                        <span class="badge" :style="{ backgroundColor: lead.stage?.color, color: '#fff' }">{{ lead.stage?.name }}</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted fs-12 text-uppercase d-block">Title</label>
                                    <span class="fw-semibold">{{ lead.title }}</span>
                                </div>
                                <div class="mb-3">
                                    <label class="text-muted fs-12 text-uppercase d-block">Company</label>
                                    <span>{{ lead.company || '-' }}</span>
                                </div>
                                <div class="mb-3">
                                    <label class="text-muted fs-12 text-uppercase d-block">Source</label>
                                    <span class="badge bg-light text-dark">{{ getSourceLabel(lead.source) }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted fs-12 text-uppercase d-block">Priority</label>
                                    <span class="badge" :class="'bg-' + getPriorityClass(lead.priority) + '-subtle text-' + getPriorityClass(lead.priority)">{{ lead.priority }}</span>
                                </div>
                                <div class="mb-3">
                                    <label class="text-muted fs-12 text-uppercase d-block">Deal Value</label>
                                    <span class="fw-semibold text-success fs-16">{{ formatValue(lead.value, lead.currency) }}</span>
                                </div>
                                <div class="mb-3">
                                    <label class="text-muted fs-12 text-uppercase d-block">Expected Close</label>
                                    <span>{{ formatDate(lead.expected_close_date) }}</span>
                                </div>
                            </div>
                        </div>
                        <div v-if="lead.description" class="mt-2">
                            <label class="text-muted fs-12 text-uppercase d-block mb-1">Description</label>
                            <p class="mb-0" style="white-space: pre-wrap;">{{ lead.description }}</p>
                        </div>
                    </div>
                </div>

                <!-- Activity Timeline -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Activity Timeline</h5>
                    </div>
                    <div class="card-body">
                        <!-- Add Activity Form -->
                        <div class="border rounded p-3 mb-4">
                            <form @submit.prevent="submitActivity">
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-2">
                                        <label class="form-label fs-12">Type</label>
                                        <select v-model="activityForm.type" class="form-select form-select-sm">
                                            <option value="note">📝 Note</option>
                                            <option value="call">📞 Call</option>
                                            <option value="email">📧 Email</option>
                                            <option value="meeting">📅 Meeting</option>
                                            <option value="task">✅ Task</option>
                                        </select>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label fs-12">Description</label>
                                        <input v-model="activityForm.description" type="text" class="form-control form-control-sm" placeholder="Log an activity..." required>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary btn-sm w-100" :disabled="activityForm.processing">
                                            <i class="ti ti-plus me-1"></i> Log
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Timeline -->
                        <div v-if="lead.activities && lead.activities.length > 0" class="timeline-alt">
                            <div v-for="activity in lead.activities" :key="activity.id" class="timeline-item d-flex gap-3 mb-3">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm">
                                        <span class="avatar-title rounded-circle" :class="'bg-' + getActivityColor(activity.type) + '-subtle text-' + getActivityColor(activity.type)">
                                            <i :class="getActivityIcon(activity.type)" class="fs-14"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <h6 class="mb-0 fs-13">
                                            <span class="fw-semibold">{{ activity.user?.name || 'System' }}</span>
                                            <span class="text-muted fw-normal ms-1">logged a {{ activity.type.replace('_', ' ') }}</span>
                                        </h6>
                                        <span class="text-muted fs-11">{{ formatDateTime(activity.created_at) }}</span>
                                    </div>
                                    <p class="text-muted mb-0 fs-13">{{ activity.description }}</p>
                                </div>
                            </div>
                        </div>

                        <div v-else class="text-center text-muted py-4">
                            <i class="ti ti-clock-off fs-24 d-block mb-2"></i>
                            No activities yet. Log your first interaction above.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-xl-4">
                <!-- Contact Card -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="ti ti-user me-1"></i> Contact</h5>
                    </div>
                    <div class="card-body">
                        <div v-if="lead.contact_name" class="d-flex align-items-center gap-2 mb-3">
                            <div class="avatar-sm">
                                <span class="avatar-title bg-primary rounded-circle text-white fs-14 fw-bold">
                                    {{ (lead.contact_name || '?').substring(0, 2).toUpperCase() }}
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-0 fs-14">{{ lead.contact_name }}</h6>
                                <span v-if="lead.company" class="text-muted fs-12">{{ lead.company }}</span>
                            </div>
                        </div>

                        <div v-if="lead.contact_email" class="mb-2">
                            <i class="ti ti-mail me-2 text-muted"></i>
                            <a :href="'mailto:' + lead.contact_email" class="text-reset">{{ lead.contact_email }}</a>
                        </div>
                        <div v-if="lead.contact_phone" class="mb-2">
                            <i class="ti ti-phone me-2 text-muted"></i>
                            <a :href="'tel:' + lead.contact_phone" class="text-reset">{{ lead.contact_phone }}</a>
                        </div>

                        <div v-if="lead.client" class="mt-3 pt-3 border-top">
                            <span class="text-muted fs-12 text-uppercase d-block mb-1">Linked Client</span>
                            <Link :href="route('clients.show', lead.client.id)" class="fw-semibold text-primary">{{ lead.client.name }}</Link>
                        </div>

                        <div v-if="!lead.contact_name && !lead.contact_email && !lead.contact_phone && !lead.client" class="text-center text-muted py-2">
                            No contact information provided
                        </div>
                    </div>
                </div>

                <!-- Assigned To -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="ti ti-user-check me-1"></i> Assigned To</h5>
                    </div>
                    <div class="card-body">
                        <div v-if="lead.assigned_user" class="d-flex align-items-center gap-2">
                            <div class="avatar-sm">
                                <span class="avatar-title bg-success rounded-circle text-white fs-12 fw-bold">
                                    {{ (lead.assigned_user.name || '?').substring(0, 2).toUpperCase() }}
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-0 fs-14">{{ lead.assigned_user.name }}</h6>
                                <span class="text-muted fs-12">{{ lead.assigned_user.email }}</span>
                            </div>
                        </div>
                        <div v-else class="text-muted">Unassigned</div>
                    </div>
                </div>

                <!-- Quick Info -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="ti ti-info-circle me-1"></i> Details</h5>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-muted">Auto Convert</td>
                                    <td class="text-end">
                                        <span class="badge" :class="lead.auto_convert ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'">
                                            {{ lead.auto_convert ? 'Enabled' : 'Disabled' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Created</td>
                                    <td class="text-end fs-12">{{ formatDate(lead.created_at) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Updated</td>
                                    <td class="text-end fs-12">{{ formatDate(lead.updated_at) }}</td>
                                </tr>
                                <tr v-if="lead.won_at">
                                    <td class="text-muted">Won At</td>
                                    <td class="text-end fs-12 text-success">{{ formatDateTime(lead.won_at) }}</td>
                                </tr>
                                <tr v-if="lead.lost_at">
                                    <td class="text-muted">Lost At</td>
                                    <td class="text-end fs-12 text-danger">{{ formatDateTime(lead.lost_at) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Convert Confirmation Modal -->
        <Teleport to="body">
            <div v-if="showConvertConfirm" class="modal show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Convert Lead to Client</h5>
                            <button type="button" class="btn-close" @click="showConvertConfirm = false"></button>
                        </div>
                        <div class="modal-body">
                            <p>This will create a new <strong>Client</strong> record from this lead's contact information:</p>
                            <ul class="list-unstyled mb-0">
                                <li v-if="lead.contact_name"><i class="ti ti-user me-1 text-primary"></i> {{ lead.contact_name }}</li>
                                <li v-if="lead.contact_email"><i class="ti ti-mail me-1 text-primary"></i> {{ lead.contact_email }}</li>
                                <li v-if="lead.company"><i class="ti ti-building me-1 text-primary"></i> {{ lead.company }}</li>
                            </ul>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-light" @click="showConvertConfirm = false">Cancel</button>
                            <button class="btn btn-success" @click="convertToClient">
                                <i class="ti ti-transform me-1"></i> Convert
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

<style scoped>
.pipeline-step {
    border: 2px solid transparent;
    transition: all 0.2s ease;
    white-space: nowrap;
    font-size: 12px;
}

.pipeline-step--active {
    font-weight: 700;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.pipeline-step--passed {
    opacity: 0.7;
}

.pipeline-step--upcoming {
    background: var(--bs-tertiary-bg, #f8f9fa) !important;
    color: var(--bs-secondary-color, #adb5bd) !important;
}

.timeline-item {
    position: relative;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 19px;
    top: 40px;
    bottom: -12px;
    width: 2px;
    background: var(--bs-border-color, #eee);
}
</style>
