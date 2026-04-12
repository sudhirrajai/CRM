<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import KanbanBoard from '@/Components/Leads/KanbanBoard.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    stages: { type: Array, required: true },
    groupedLeads: { type: Array, required: true },
    allLeads: { type: Array, required: true },
    users: { type: Array, required: true },
    stats: { type: Object, required: true },
});

const viewMode = ref('kanban'); // 'kanban' or 'table'
const search = ref('');
const filterStage = ref('');
const filterSource = ref('');
const filterPriority = ref('');
const filterAssigned = ref('');
const showImportModal = ref(false);
const importForm = useForm({ file: null });

// Pipeline stage management
const showStageModal = ref(false);
const editingStage = ref(null);
const stageForm = useForm({
    name: '',
    color: '#6c757d',
    is_won: false,
    is_lost: false,
});

const localGrouped = ref(JSON.parse(JSON.stringify(props.groupedLeads)));

const filteredLeads = computed(() => {
    let leads = props.allLeads;
    if (search.value) {
        const q = search.value.toLowerCase();
        leads = leads.filter(l =>
            l.title?.toLowerCase().includes(q) ||
            l.company?.toLowerCase().includes(q) ||
            l.contact_name?.toLowerCase().includes(q) ||
            l.contact_email?.toLowerCase().includes(q)
        );
    }
    if (filterStage.value) {
        leads = leads.filter(l => l.lead_pipeline_stage_id === filterStage.value);
    }
    if (filterSource.value) {
        leads = leads.filter(l => l.source === filterSource.value);
    }
    if (filterPriority.value) {
        leads = leads.filter(l => l.priority === filterPriority.value);
    }
    if (filterAssigned.value) {
        leads = leads.filter(l => l.assigned_to === filterAssigned.value);
    }
    return leads;
});

function formatValue(value, currency) {
    if (!value) return '-';
    const sym = currency?.symbol || '$';
    const formatted = parseFloat(value).toLocaleString();
    return currency?.symbol_position === 'suffix' ? `${formatted} ${sym}` : `${sym}${formatted}`;
}

const defaultCurrency = computed(() => usePage().props.defaultCurrency);

function formatTotalValue(value) {
    const sym = defaultCurrency.value?.symbol || '$';
    const formatted = parseFloat(value || 0).toLocaleString();
    return defaultCurrency.value?.symbol_position === 'suffix' ? `${formatted} ${sym}` : `${sym}${formatted}`;
}

function getPriorityClass(priority) {
    const map = { urgent: 'danger', high: 'warning', medium: 'info', low: 'secondary' };
    return map[priority] || 'secondary';
}

function getSourceLabel(source) {
    const map = {
        website: 'Website', referral: 'Referral', social_media: 'Social Media',
        cold_call: 'Cold Call', email: 'Email', advertisement: 'Ad', other: 'Other'
    };
    return map[source] || source;
}

async function handleStageChanged({ leadId, fromStageId, toStageId, position }) {
    // Optimistic update
    const fromStage = localGrouped.value.find(s => s.id === fromStageId);
    const toStage = localGrouped.value.find(s => s.id === toStageId);
    if (fromStage && toStage) {
        const leadIndex = fromStage.leads.findIndex(l => l.id === leadId);
        if (leadIndex !== -1) {
            const [lead] = fromStage.leads.splice(leadIndex, 1);
            lead.lead_pipeline_stage_id = toStageId;
            lead.position = position;
            toStage.leads.push(lead);
        }
    }

    try {
        await axios.put(route('leads.update-stage', leadId), {
            lead_pipeline_stage_id: toStageId,
            position: position,
        });
    } catch (err) {
        // Revert on failure
        localGrouped.value = JSON.parse(JSON.stringify(props.groupedLeads));
    }
}

function deleteRecord(id, title) {
    if (confirm(`Are you sure you want to delete "${title}"? This action cannot be undone.`)) {
        router.delete(route('leads.destroy', id));
    }
}

function handleImport() {
    importForm.post(route('leads.import'), {
        preserveScroll: true,
        onSuccess: () => {
            showImportModal.value = false;
            importForm.reset();
        },
    });
}

function onFileChange(e) {
    importForm.file = e.target.files[0];
}

function clearFilters() {
    search.value = '';
    filterStage.value = '';
    filterSource.value = '';
    filterPriority.value = '';
    filterAssigned.value = '';
}

function openStageModal(stage = null) {
    editingStage.value = stage;
    if (stage) {
        stageForm.name = stage.name;
        stageForm.color = stage.color;
        stageForm.is_won = stage.is_won;
        stageForm.is_lost = stage.is_lost;
    } else {
        stageForm.reset();
        stageForm.color = '#6c757d';
    }
    showStageModal.value = true;
}

function saveStage() {
    if (editingStage.value) {
        stageForm.put(route('pipeline-stages.update', editingStage.value.id), {
            preserveScroll: true,
            onSuccess: () => { showStageModal.value = false; },
        });
    } else {
        stageForm.post(route('pipeline-stages.store'), {
            preserveScroll: true,
            onSuccess: () => { showStageModal.value = false; },
        });
    }
}

function deleteStage(stage) {
    if (confirm(`Delete "${stage.name}" stage? Leads in this stage must be moved first.`)) {
        router.delete(route('pipeline-stages.destroy', stage.id));
    }
}
</script>

<template>
    <Head title="Leads" />

    <AuthenticatedLayout>
        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column mb-4 mt-2">
                    <div class="flex-grow-1">
                        <h4 class="fs-18 text-uppercase fw-bold m-0">Leads Pipeline</h4>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a :href="route('leads.export')" class="btn btn-outline-secondary btn-sm">
                            <i class="ti ti-download me-1"></i> Export
                        </a>
                        <button @click="showImportModal = true" class="btn btn-outline-secondary btn-sm">
                            <i class="ti ti-upload me-1"></i> Import
                        </button>
                        <button
                            v-if="$page.props.auth.roles.includes('admin')"
                            @click="openStageModal()"
                            class="btn btn-outline-primary btn-sm"
                        >
                            <i class="ti ti-columns me-1"></i> Manage Stages
                        </button>
                        <Link :href="route('leads.create')" class="btn btn-primary btn-sm">
                            <i class="ti ti-plus me-1"></i> New Lead
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-3">
            <div class="col-md-3 col-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="text-muted fs-12 text-uppercase mb-2">Total Leads</h5>
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-md flex-shrink-0">
                                <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-20">
                                    <i class="ti ti-target-arrow"></i>
                                </span>
                            </div>
                            <h3 class="mb-0 fw-bold">{{ stats.total }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="text-muted fs-12 text-uppercase mb-2">Pipeline Value</h5>
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-md flex-shrink-0">
                                <span class="avatar-title bg-success-subtle text-success rounded-circle fs-20 fw-bold">
                                    {{ defaultCurrency?.symbol || '$' }}
                                </span>
                            </div>
                            <h3 class="mb-0 fw-bold">{{ formatTotalValue(stats.total_value) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="text-muted fs-12 text-uppercase mb-2">Won</h5>
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-md flex-shrink-0">
                                <span class="avatar-title bg-success-subtle text-success rounded-circle fs-20">
                                    <i class="ti ti-trophy"></i>
                                </span>
                            </div>
                            <div>
                                <h3 class="mb-0 fw-bold">{{ stats.won }}</h3>
                                <span class="text-success fs-12">{{ stats.conversion_rate }}% rate</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="text-muted fs-12 text-uppercase mb-2">Lost</h5>
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-md flex-shrink-0">
                                <span class="avatar-title bg-danger-subtle text-danger rounded-circle fs-20">
                                    <i class="ti ti-x"></i>
                                </span>
                            </div>
                            <h3 class="mb-0 fw-bold">{{ stats.lost }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- View Toggle + Filters -->
        <div class="card mb-3">
            <div class="card-body py-2">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <div class="btn-group btn-group-sm" role="group">
                            <button @click="viewMode = 'kanban'" :class="viewMode === 'kanban' ? 'btn-primary' : 'btn-outline-primary'" class="btn">
                                <i class="ti ti-layout-board me-1"></i> Board
                            </button>
                            <button @click="viewMode = 'table'" :class="viewMode === 'table' ? 'btn-primary' : 'btn-outline-primary'" class="btn">
                                <i class="ti ti-table me-1"></i> Table
                            </button>
                        </div>

                        <div class="input-group input-group-sm" style="max-width: 220px;">
                            <span class="input-group-text"><i class="ti ti-search"></i></span>
                            <input v-model="search" type="text" class="form-control" placeholder="Search leads...">
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <select v-model="filterStage" class="form-select form-select-sm" style="width: auto;">
                            <option value="">All Stages</option>
                            <option v-for="s in stages" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                        <select v-model="filterSource" class="form-select form-select-sm" style="width: auto;">
                            <option value="">All Sources</option>
                            <option value="website">Website</option>
                            <option value="referral">Referral</option>
                            <option value="social_media">Social Media</option>
                            <option value="cold_call">Cold Call</option>
                            <option value="email">Email</option>
                            <option value="advertisement">Advertisement</option>
                            <option value="other">Other</option>
                        </select>
                        <select v-model="filterPriority" class="form-select form-select-sm" style="width: auto;">
                            <option value="">All Priorities</option>
                            <option value="urgent">Urgent</option>
                            <option value="high">High</option>
                            <option value="medium">Medium</option>
                            <option value="low">Low</option>
                        </select>
                        <select v-model="filterAssigned" class="form-select form-select-sm" style="width: auto;">
                            <option value="">All Assigned</option>
                            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                        </select>
                        <button v-if="search || filterStage || filterSource || filterPriority || filterAssigned" @click="clearFilters" class="btn btn-sm btn-outline-danger">
                            <i class="ti ti-x"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kanban View -->
        <div v-if="viewMode === 'kanban'" class="mb-3">
            <KanbanBoard
                :stages="stages"
                :modelValue="localGrouped"
                @stage-changed="handleStageChanged"
            />
        </div>

        <!-- Table View -->
        <div v-else class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-centered mb-0 align-middle table-hover table-nowrap">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Company</th>
                                <th>Contact</th>
                                <th>Stage</th>
                                <th>Value</th>
                                <th>Source</th>
                                <th>Priority</th>
                                <th>Assigned</th>
                                <th>Close Date</th>
                                <th style="width: 120px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="lead in filteredLeads" :key="lead.id">
                                <td>
                                    <Link :href="route('leads.show', lead.id)" class="text-reset fw-semibold">{{ lead.title }}</Link>
                                </td>
                                <td>{{ lead.company || '-' }}</td>
                                <td>
                                    <div v-if="lead.contact_name" class="fs-13">{{ lead.contact_name }}</div>
                                    <div v-if="lead.contact_email" class="text-muted fs-11">{{ lead.contact_email }}</div>
                                </td>
                                <td>
                                    <span class="badge" :style="{ backgroundColor: lead.stage?.color, color: '#fff' }">
                                        {{ lead.stage?.name }}
                                    </span>
                                </td>
                                <td>{{ formatValue(lead.value, lead.currency) }}</td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ getSourceLabel(lead.source) }}</span>
                                </td>
                                <td>
                                    <span class="badge" :class="'bg-' + getPriorityClass(lead.priority) + '-subtle text-' + getPriorityClass(lead.priority)">
                                        {{ lead.priority }}
                                    </span>
                                </td>
                                <td>{{ lead.assigned_user?.name || '-' }}</td>
                                <td>{{ lead.expected_close_date ? new Date(lead.expected_close_date).toLocaleDateString() : '-' }}</td>
                                <td>
                                    <Link :href="route('leads.show', lead.id)" class="action-icon text-info" title="View"><i class="ti ti-eye"></i></Link>
                                    <Link :href="route('leads.edit', lead.id)" class="action-icon text-primary" title="Edit"><i class="ti ti-edit"></i></Link>
                                    <button @click="deleteRecord(lead.id, lead.title)" class="action-icon text-danger" title="Delete"><i class="ti ti-trash"></i></button>
                                </td>
                            </tr>
                            <tr v-if="filteredLeads.length === 0">
                                <td colspan="10" class="text-center py-4 text-muted">No leads found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Import Modal -->
        <Teleport to="body">
            <div v-if="showImportModal" class="modal show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Import Leads from CSV</h5>
                            <button type="button" class="btn-close" @click="showImportModal = false"></button>
                        </div>
                        <form @submit.prevent="handleImport">
                            <div class="modal-body">
                                <p class="text-muted fs-13 mb-3">Upload a CSV file with columns: Title, Company, Contact Name, Contact Email, Contact Phone, Stage, Value, Currency, Source, Priority, Assigned To, Expected Close Date</p>
                                <div class="mb-3">
                                    <label class="form-label">CSV File</label>
                                    <input type="file" @change="onFileChange" accept=".csv,.txt" class="form-control" :class="{ 'is-invalid': importForm.errors.file }">
                                    <div class="invalid-feedback" v-if="importForm.errors.file">{{ importForm.errors.file }}</div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" @click="showImportModal = false">Cancel</button>
                                <button type="submit" class="btn btn-primary" :disabled="importForm.processing || !importForm.file">
                                    <span v-if="importForm.processing" class="spinner-border spinner-border-sm me-1"></span>
                                    Import
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Stage Management Modal -->
        <Teleport to="body">
            <div v-if="showStageModal" class="modal show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Manage Pipeline Stages</h5>
                            <button type="button" class="btn-close" @click="showStageModal = false"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Existing Stages -->
                            <div class="table-responsive mb-4">
                                <table class="table table-sm table-centered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Color</th>
                                            <th>Name</th>
                                            <th>Leads</th>
                                            <th>Type</th>
                                            <th style="width: 120px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="stage in stages" :key="stage.id">
                                            <td>
                                                <span class="d-inline-block rounded-circle" :style="{ width: '16px', height: '16px', backgroundColor: stage.color }"></span>
                                            </td>
                                            <td class="fw-semibold">{{ stage.name }}</td>
                                            <td><span class="badge bg-light text-dark">{{ stage.leads_count || 0 }}</span></td>
                                            <td>
                                                <span v-if="stage.is_default" class="badge bg-primary-subtle text-primary">Default</span>
                                                <span v-else-if="stage.is_won" class="badge bg-success-subtle text-success">Won</span>
                                                <span v-else-if="stage.is_lost" class="badge bg-danger-subtle text-danger">Lost</span>
                                                <span v-else class="badge bg-secondary-subtle text-secondary">Normal</span>
                                            </td>
                                            <td>
                                                <button @click="openStageModal(stage); showStageModal = false; $nextTick(() => showStageModal = true)" class="action-icon text-primary" title="Edit"><i class="ti ti-edit"></i></button>
                                                <button @click="deleteStage(stage)" class="action-icon text-danger" title="Delete" :disabled="stage.is_default"><i class="ti ti-trash"></i></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Add/Edit Stage Form -->
                            <div class="border rounded p-3">
                                <h6 class="mb-3">{{ editingStage ? 'Edit Stage' : 'Add New Stage' }}</h6>
                                <form @submit.prevent="saveStage">
                                    <div class="row g-3">
                                        <div class="col-md-5">
                                            <label class="form-label">Stage Name</label>
                                            <input v-model="stageForm.name" type="text" class="form-control form-control-sm" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Color</label>
                                            <input v-model="stageForm.color" type="color" class="form-control form-control-sm form-control-color w-100">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Type</label>
                                            <div class="d-flex gap-3 mt-1">
                                                <div class="form-check">
                                                    <input v-model="stageForm.is_won" type="checkbox" class="form-check-input" id="isWon" @change="stageForm.is_won && (stageForm.is_lost = false)">
                                                    <label class="form-check-label" for="isWon">Won</label>
                                                </div>
                                                <div class="form-check">
                                                    <input v-model="stageForm.is_lost" type="checkbox" class="form-check-input" id="isLost" @change="stageForm.is_lost && (stageForm.is_won = false)">
                                                    <label class="form-check-label" for="isLost">Lost</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary btn-sm w-100" :disabled="stageForm.processing">
                                                {{ editingStage ? 'Update' : 'Add' }}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>
