<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    group: {
        type: Object,
        required: true,
    },
    tasks: {
        type: Array,
        default: () => [],
    },
});

const showSearchModal = ref(false);

const searchForm = useForm({
    query: '',
    location: '',
});

function openSearch() {
    searchForm.reset();
    showSearchModal.value = true;
}

function submitSearch() {
    searchForm.post(route('lead-getter.tasks.store', props.group.id), {
        preserveScroll: true,
        onSuccess: () => {
            showSearchModal.value = false;
            searchForm.reset();
        },
    });
}

function getStatusBadge(status) {
    const map = {
        pending: { class: 'bg-warning', icon: 'ti-clock', label: 'Pending' },
        running: { class: 'bg-info', icon: 'ti-loader', label: 'Running...' },
        completed: { class: 'bg-success', icon: 'ti-check', label: 'Completed' },
        failed: { class: 'bg-danger', icon: 'ti-x', label: 'Failed' },
    };
    return map[status] || map.pending;
}

// Auto-refresh if any task is pending or running
const hasRunningTasks = computed(() => {
    return props.tasks.some(t => t.status === 'pending' || t.status === 'running');
});

let refreshInterval = null;
if (hasRunningTasks.value) {
    refreshInterval = setInterval(() => {
        router.reload({ only: ['tasks'] });
    }, 5000);
}

// Clean up on unmount
import { onUnmounted } from 'vue';
onUnmounted(() => {
    if (refreshInterval) clearInterval(refreshInterval);
});
</script>

<template>
    <Head :title="group.name + ' - Lead Getter'" />

    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <div>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-1">
                                <li class="breadcrumb-item"><Link :href="route('lead-getter.index')">Lead Getter</Link></li>
                                <li class="breadcrumb-item active">{{ group.name }}</li>
                            </ol>
                        </nav>
                        <h4 class="page-title mb-0">{{ group.name }}</h4>
                        <p class="text-muted small mb-0" v-if="group.description">{{ group.description }}</p>
                    </div>
                    <button @click="openSearch" class="btn btn-primary">
                        <i class="ti ti-search me-1"></i> New Search
                    </button>
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

        <!-- Running Tasks Banner -->
        <div v-if="hasRunningTasks" class="alert alert-info d-flex align-items-center py-2">
            <div class="spinner-border spinner-border-sm me-2" role="status"></div>
            <small>Some tasks are still being processed. This page will auto-refresh.</small>
        </div>

        <!-- Empty State -->
        <div v-if="tasks.length === 0" class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="ti ti-search" style="font-size: 4rem; color: var(--bs-primary); opacity: 0.6;"></i>
                        </div>
                        <h4 class="text-muted mb-2">No Search Tasks Yet</h4>
                        <p class="text-muted mb-4">Start a new search to find potential business leads in your target area.</p>
                        <button @click="openSearch" class="btn btn-primary">
                            <i class="ti ti-search me-1"></i> Start Your First Search
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tasks List -->
        <div v-else class="row">
            <div v-for="task in tasks" :key="task.id" class="col-md-6 col-xl-4">
                <div class="card card-hover">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between mb-2">
                            <div>
                                <h5 class="card-title mb-1">
                                    <i class="ti ti-search me-1 text-muted"></i>
                                    {{ task.query }}
                                </h5>
                                <p class="text-muted small mb-0">
                                    <i class="ti ti-map-pin me-1"></i> {{ task.location }}
                                </p>
                            </div>
                            <span class="badge" :class="getStatusBadge(task.status).class">
                                <i class="me-1" :class="'ti ' + getStatusBadge(task.status).icon"></i>
                                {{ getStatusBadge(task.status).label }}
                            </span>
                        </div>

                        <!-- Error message -->
                        <div v-if="task.status === 'failed' && task.error_message" class="alert alert-danger py-1 px-2 mt-2 mb-2">
                            <small><i class="ti ti-alert-triangle me-1"></i> {{ task.error_message }}</small>
                        </div>

                        <div class="row text-center mt-3 pt-3 border-top">
                            <div class="col-4">
                                <h5 class="fw-bold mb-0">{{ task.results_count || 0 }}</h5>
                                <small class="text-muted">Total</small>
                            </div>
                            <div class="col-4">
                                <h5 class="fw-bold text-primary mb-0">{{ task.new_count || 0 }}</h5>
                                <small class="text-muted">New</small>
                            </div>
                            <div class="col-4">
                                <h5 class="fw-bold text-success mb-0">{{ task.qualified_count || 0 }}</h5>
                                <small class="text-muted">Qualified</small>
                            </div>
                        </div>

                        <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="ti ti-user me-1"></i> {{ task.user?.name || 'N/A' }}
                            </small>
                            <Link v-if="task.status === 'completed' && task.results_count > 0" :href="route('lead-getter.tasks.show', task.id)" class="btn btn-sm btn-outline-primary">
                                <i class="ti ti-list-details me-1"></i> View Results
                            </Link>
                            <span v-else-if="task.status === 'running'" class="text-info small">
                                <div class="spinner-border spinner-border-sm me-1" role="status"></div> Fetching...
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Search Modal -->
        <div v-if="showSearchModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="ti ti-search me-2"></i> New Lead Search</h5>
                        <button type="button" class="btn-close" @click="showSearchModal = false"></button>
                    </div>
                    <form @submit.prevent="submitSearch">
                        <div class="modal-body">
                            <div class="alert alert-light border py-2 mb-3">
                                <small><i class="ti ti-info-circle me-1"></i>
                                Search for businesses using Google Maps. Results will include business name, phone, website, address, and ratings.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Search Query <span class="text-danger">*</span></label>
                                <input type="text" v-model="searchForm.query" class="form-control" placeholder="e.g., web design agency, plumber, restaurant" required>
                                <div v-if="searchForm.errors.query" class="text-danger small mt-1">{{ searchForm.errors.query }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Location <span class="text-danger">*</span></label>
                                <input type="text" v-model="searchForm.location" class="form-control" placeholder="e.g., New York, USA or Mumbai, India" required>
                                <div v-if="searchForm.errors.location" class="text-danger small mt-1">{{ searchForm.errors.location }}</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" @click="showSearchModal = false">Cancel</button>
                            <button type="submit" class="btn btn-primary" :disabled="searchForm.processing">
                                <i class="ti ti-search me-1"></i> Start Search
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
