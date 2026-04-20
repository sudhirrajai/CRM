<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    task: {
        type: Object,
        required: true,
    },
    results: {
        type: Array,
        default: () => [],
    },
    users: {
        type: Array,
        default: () => [],
    },
    stages: {
        type: Array,
        default: () => [],
    },
});

const filterStatus = ref('all');
const selectedResults = ref([]);
const showQualifyModal = ref(false);
const showBulkQualifyModal = ref(false);
const qualifyingResult = ref(null);

// Filter results
const filteredResults = computed(() => {
    if (filterStatus.value === 'all') return props.results;
    return props.results.filter(r => r.status === filterStatus.value);
});

// Stats
const stats = computed(() => ({
    total: props.results.length,
    new: props.results.filter(r => r.status === 'new').length,
    qualified: props.results.filter(r => r.status === 'qualified').length,
    disqualified: props.results.filter(r => r.status === 'disqualified').length,
}));

// Select / Deselect
const selectAll = ref(false);
function toggleSelectAll() {
    if (selectAll.value) {
        selectedResults.value = filteredResults.value
            .filter(r => r.status === 'new')
            .map(r => r.id);
    } else {
        selectedResults.value = [];
    }
}

function toggleSelect(id) {
    const idx = selectedResults.value.indexOf(id);
    if (idx > -1) {
        selectedResults.value.splice(idx, 1);
    } else {
        selectedResults.value.push(id);
    }
}

// Single Qualify
const qualifyForm = useForm({
    title: '',
    company: '',
    contact_name: '',
    contact_email: '',
    contact_phone: '',
    lead_pipeline_stage_id: '',
    assigned_to: '',
    value: '',
    source: 'other',
    priority: 'medium',
    description: '',
});

function openQualify(result) {
    qualifyingResult.value = result;
    qualifyForm.title = result.title || '';
    qualifyForm.company = result.company || result.title || '';
    qualifyForm.contact_name = result.contact_name || '';
    qualifyForm.contact_email = result.email || '';
    qualifyForm.contact_phone = result.phone || '';
    qualifyForm.value = '';
    qualifyForm.source = 'other';
    qualifyForm.priority = 'medium';
    qualifyForm.lead_pipeline_stage_id = props.stages.find(s => s.is_default)?.id || (props.stages[0]?.id || '');
    qualifyForm.assigned_to = '';
    qualifyForm.description = buildDescription(result);
    showQualifyModal.value = true;
}

function buildDescription(result) {
    let desc = [];
    if (result.website) desc.push(`Website: ${result.website}`);
    if (result.address) desc.push(`Address: ${result.address}`);
    if (result.rating) desc.push(`Rating: ${result.rating} (${result.reviews_count || 0} reviews)`);
    if (result.category) desc.push(`Category: ${result.category}`);
    return desc.join('\n');
}

function submitQualify() {
    qualifyForm.post(route('lead-getter.results.qualify', qualifyingResult.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showQualifyModal.value = false;
            qualifyingResult.value = null;
        },
    });
}

// Bulk Qualify
const bulkQualifyForm = useForm({
    result_ids: [],
    lead_pipeline_stage_id: '',
    assigned_to: '',
    source: 'other',
    priority: 'medium',
});

function openBulkQualify() {
    bulkQualifyForm.result_ids = [...selectedResults.value];
    bulkQualifyForm.lead_pipeline_stage_id = props.stages.find(s => s.is_default)?.id || (props.stages[0]?.id || '');
    bulkQualifyForm.assigned_to = '';
    bulkQualifyForm.source = 'other';
    bulkQualifyForm.priority = 'medium';
    showBulkQualifyModal.value = true;
}

function submitBulkQualify() {
    bulkQualifyForm.post(route('lead-getter.results.bulk-qualify'), {
        preserveScroll: true,
        onSuccess: () => {
            showBulkQualifyModal.value = false;
            selectedResults.value = [];
            selectAll.value = false;
        },
    });
}

// Disqualify
function disqualify(result) {
    if (confirm(`Disqualify "${result.title}"?`)) {
        router.post(route('lead-getter.results.disqualify', result.id), {}, {
            preserveScroll: true,
        });
    }
}

function getRowClass(result) {
    if (result.status === 'disqualified') return 'table-secondary opacity-50';
    if (result.status === 'qualified') return 'table-success-subtle';
    return '';
}
</script>

<template>
    <Head :title="task.query + ' Results - Lead Getter'" />

    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item"><Link :href="route('lead-getter.index')">Lead Getter</Link></li>
                            <li class="breadcrumb-item"><Link :href="route('lead-getter.groups.show', task.group.id)">{{ task.group.name }}</Link></li>
                            <li class="breadcrumb-item active">Results</li>
                        </ol>
                    </nav>
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="page-title mb-0">
                                <i class="ti ti-search me-1"></i> {{ task.query }}
                            </h4>
                            <p class="text-muted small mb-0">
                                <i class="ti ti-map-pin me-1"></i> {{ task.location }}
                                <span class="ms-2"><i class="ti ti-user me-1"></i> {{ task.user?.name }}</span>
                            </p>
                        </div>
                        <div class="d-flex gap-2">
                            <a :href="route('lead-getter.tasks.export', task.id)" class="btn btn-outline-secondary">
                                <i class="ti ti-download me-1"></i> Export CSV
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        <div v-if="$page.props.flash.success" class="alert alert-success alert-dismissible fade show" role="alert">
            {{ $page.props.flash.success }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <div v-if="$page.props.flash.error" class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ $page.props.flash.error }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <!-- Stats Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="card widget-inline">
                    <div class="card-body p-3 text-center">
                        <h4 class="fw-bold mb-0">{{ stats.total }}</h4>
                        <small class="text-muted text-uppercase">Total Found</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card widget-inline">
                    <div class="card-body p-3 text-center">
                        <h4 class="fw-bold text-primary mb-0">{{ stats.new }}</h4>
                        <small class="text-muted text-uppercase">New</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card widget-inline">
                    <div class="card-body p-3 text-center">
                        <h4 class="fw-bold text-success mb-0">{{ stats.qualified }}</h4>
                        <small class="text-muted text-uppercase">Qualified</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card widget-inline">
                    <div class="card-body p-3 text-center">
                        <h4 class="fw-bold text-secondary mb-0">{{ stats.disqualified }}</h4>
                        <small class="text-muted text-uppercase">Disqualified</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="card">
            <div class="card-body py-2">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex gap-2">
                        <button @click="filterStatus = 'all'" class="btn btn-sm" :class="filterStatus === 'all' ? 'btn-primary' : 'btn-outline-secondary'">
                            All ({{ stats.total }})
                        </button>
                        <button @click="filterStatus = 'new'" class="btn btn-sm" :class="filterStatus === 'new' ? 'btn-primary' : 'btn-outline-secondary'">
                            New ({{ stats.new }})
                        </button>
                        <button @click="filterStatus = 'qualified'" class="btn btn-sm" :class="filterStatus === 'qualified' ? 'btn-success' : 'btn-outline-secondary'">
                            Qualified ({{ stats.qualified }})
                        </button>
                        <button @click="filterStatus = 'disqualified'" class="btn btn-sm" :class="filterStatus === 'disqualified' ? 'btn-secondary' : 'btn-outline-secondary'">
                            Disqualified ({{ stats.disqualified }})
                        </button>
                    </div>
                    <div v-if="selectedResults.length > 0">
                        <button @click="openBulkQualify" class="btn btn-sm btn-success">
                            <i class="ti ti-check me-1"></i> Qualify Selected ({{ selectedResults.length }})
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results Table -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" class="form-check-input" v-model="selectAll" @change="toggleSelectAll">
                                </th>
                                <th>Business</th>
                                <th>Contact Info</th>
                                <th>Website</th>
                                <th>Location / Address</th>
                                <th style="width: 80px;">Rating</th>
                                <th style="width: 100px;">Status</th>
                                <th style="width: 150px;" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="result in filteredResults" :key="result.id" :class="getRowClass(result)">
                                <td>
                                    <input v-if="result.status === 'new'"
                                        type="checkbox"
                                        class="form-check-input"
                                        :checked="selectedResults.includes(result.id)"
                                        @change="toggleSelect(result.id)">
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ result.title }}</div>
                                    <small v-if="result.category" class="text-muted">{{ result.category }}</small>
                                </td>
                                <td>
                                    <div class="mb-1">
                                        <i class="ti ti-phone text-muted me-1"></i>
                                        <span v-if="result.phone">
                                            <a :href="'tel:' + result.phone" class="text-dark">{{ result.phone }}</a>
                                        </span>
                                        <span v-else class="text-muted small">No Phone</span>
                                    </div>
                                    <div>
                                        <i class="ti ti-mail text-muted me-1"></i>
                                        <span v-if="result.email">
                                            <a :href="'mailto:' + result.email" class="text-dark">{{ result.email }}</a>
                                        </span>
                                        <span v-else class="text-muted small">No Email</span>
                                    </div>
                                </td>
                                <td>
                                    <a v-if="result.website" :href="result.website" target="_blank" class="text-primary text-truncate d-inline-block" style="max-width: 180px;">
                                        {{ result.website.replace(/^https?:\/\//, '').replace(/\/$/, '') }}
                                    </a>
                                    <span v-else class="text-muted">—</span>
                                </td>
                                <td>
                                    <div v-if="result.address" class="text-truncate d-inline-block" style="max-width: 250px;" :title="result.address">
                                        <i class="ti ti-map-pin text-muted me-1"></i>
                                        {{ result.address }}
                                    </div>
                                    <div v-else class="text-muted small">—</div>
                                </td>
                                <td>
                                    <span v-if="result.rating" class="text-warning">
                                        <i class="ti ti-star-filled me-1"></i>{{ result.rating }}
                                    </span>
                                    <small v-if="result.reviews_count" class="text-muted ms-1">({{ result.reviews_count }})</small>
                                    <span v-if="!result.rating" class="text-muted">—</span>
                                </td>
                                <td>
                                    <span v-if="result.status === 'new'" class="badge bg-info-subtle text-info">New</span>
                                    <span v-else-if="result.status === 'qualified'" class="badge bg-success-subtle text-success">
                                        <i class="ti ti-check me-1"></i>Qualified
                                    </span>
                                    <span v-else-if="result.status === 'disqualified'" class="badge bg-secondary-subtle text-secondary">
                                        <i class="ti ti-x me-1"></i>Rejected
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div v-if="result.status === 'new'" class="d-flex gap-1 justify-content-end">
                                        <button @click="openQualify(result)" class="btn btn-sm btn-success" title="Qualify Lead">
                                            <i class="ti ti-check"></i>
                                        </button>
                                        <button @click="disqualify(result)" class="btn btn-sm btn-outline-secondary" title="Disqualify">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                    <div v-else-if="result.status === 'qualified' && result.lead">
                                        <Link :href="route('leads.show', result.lead.id)" class="btn btn-sm btn-outline-primary" title="View Lead">
                                            <i class="ti ti-external-link me-1"></i> View Lead
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredResults.length === 0">
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="ti ti-search-off" style="font-size: 2rem;"></i>
                                    <p class="mt-2 mb-0">No results found with the selected filter.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Qualify Modal -->
        <div v-if="showQualifyModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-success-subtle">
                        <h5 class="modal-title text-success"><i class="ti ti-check me-2"></i> Qualify Lead</h5>
                        <button type="button" class="btn-close" @click="showQualifyModal = false"></button>
                    </div>
                    <form @submit.prevent="submitQualify">
                        <div class="modal-body">
                            <div class="alert alert-light border py-2 mb-3">
                                <small><i class="ti ti-info-circle me-1"></i>
                                This will create a CRM Lead entry and add it to your pipeline. Fields are pre-filled from the search result.</small>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Lead Title <span class="text-danger">*</span></label>
                                    <input type="text" v-model="qualifyForm.title" class="form-control" required>
                                    <div v-if="qualifyForm.errors.title" class="text-danger small mt-1">{{ qualifyForm.errors.title }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Company</label>
                                    <input type="text" v-model="qualifyForm.company" class="form-control">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Contact Name</label>
                                    <input type="text" v-model="qualifyForm.contact_name" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Contact Email</label>
                                    <input type="email" v-model="qualifyForm.contact_email" class="form-control">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Contact Phone</label>
                                    <input type="text" v-model="qualifyForm.contact_phone" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Deal Value</label>
                                    <input type="number" v-model="qualifyForm.value" class="form-control" step="0.01" min="0" placeholder="0.00">
                                </div>
                            </div>

                            <hr class="my-3">

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Pipeline Stage <span class="text-danger">*</span></label>
                                    <select v-model="qualifyForm.lead_pipeline_stage_id" class="form-select" required>
                                        <option value="">Select Stage</option>
                                        <option v-for="stage in stages" :key="stage.id" :value="stage.id">{{ stage.name }}</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Assign To</label>
                                    <select v-model="qualifyForm.assigned_to" class="form-select">
                                        <option value="">Unassigned</option>
                                        <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Source</label>
                                    <select v-model="qualifyForm.source" class="form-select">
                                        <option value="website">Website</option>
                                        <option value="referral">Referral</option>
                                        <option value="social_media">Social Media</option>
                                        <option value="cold_call">Cold Call</option>
                                        <option value="email">Email</option>
                                        <option value="advertisement">Advertisement</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Priority</label>
                                    <select v-model="qualifyForm.priority" class="form-select">
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description / Notes</label>
                                <textarea v-model="qualifyForm.description" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" @click="showQualifyModal = false">Cancel</button>
                            <button type="submit" class="btn btn-success" :disabled="qualifyForm.processing">
                                <i class="ti ti-check me-1"></i> Qualify & Create Lead
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Bulk Qualify Modal -->
        <div v-if="showBulkQualifyModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-success-subtle">
                        <h5 class="modal-title text-success"><i class="ti ti-checks me-2"></i> Bulk Qualify {{ bulkQualifyForm.result_ids.length }} Leads</h5>
                        <button type="button" class="btn-close" @click="showBulkQualifyModal = false"></button>
                    </div>
                    <form @submit.prevent="submitBulkQualify">
                        <div class="modal-body">
                            <div class="alert alert-warning py-2 mb-3">
                                <small><i class="ti ti-alert-triangle me-1"></i>
                                This will create {{ bulkQualifyForm.result_ids.length }} CRM Lead entries using the fetched data. Lead titles, phone, and address will be auto-filled.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Pipeline Stage <span class="text-danger">*</span></label>
                                <select v-model="bulkQualifyForm.lead_pipeline_stage_id" class="form-select" required>
                                    <option value="">Select Stage</option>
                                    <option v-for="stage in stages" :key="stage.id" :value="stage.id">{{ stage.name }}</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Assign To</label>
                                <select v-model="bulkQualifyForm.assigned_to" class="form-select">
                                    <option value="">Unassigned</option>
                                    <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                                </select>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Source</label>
                                    <select v-model="bulkQualifyForm.source" class="form-select">
                                        <option value="website">Website</option>
                                        <option value="referral">Referral</option>
                                        <option value="social_media">Social Media</option>
                                        <option value="cold_call">Cold Call</option>
                                        <option value="email">Email</option>
                                        <option value="advertisement">Advertisement</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Priority</label>
                                    <select v-model="bulkQualifyForm.priority" class="form-select">
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" @click="showBulkQualifyModal = false">Cancel</button>
                            <button type="submit" class="btn btn-success" :disabled="bulkQualifyForm.processing">
                                <i class="ti ti-checks me-1"></i> Qualify All Selected
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.table-success-subtle {
    background-color: rgba(var(--bs-success-rgb), 0.07);
}
.widget-inline .card-body {
    border-left: 3px solid var(--bs-primary);
}
.widget-inline:nth-child(2) .card-body {
    border-left-color: var(--bs-info);
}
.widget-inline:nth-child(3) .card-body {
    border-left-color: var(--bs-success);
}
.widget-inline:nth-child(4) .card-body {
    border-left-color: var(--bs-secondary);
}
</style>
