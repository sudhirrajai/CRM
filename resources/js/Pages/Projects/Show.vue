<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import ChangeRequestTab from './Tabs/ChangeRequests.vue';
import SharedFilesTab from './Tabs/SharedFiles.vue';

const props = defineProps({
    project: {
        type: Object,
        required: true
    }
});

const activeTab = ref('overview');

onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');
    if (tab && ['overview', 'change-requests', 'shared-files'].includes(tab)) {
        activeTab.value = tab;
    }
});

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
};

const formatStatus = (status) => {
    if (!status) return '';
    return status.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
};

const formatCurrency = (amount, currencyCode) => {
    if (amount === null || amount === undefined) return '-';
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: currencyCode || 'USD',
        minimumFractionDigits: 2
    }).format(amount);
};

const getPriorityBadgeClass = (priority) => {
    switch (priority) {
        case 'high': return 'bg-danger';
        case 'medium': return 'bg-warning';
        case 'low': return 'bg-info';
        default: return 'bg-primary';
    }
};
</script>

<template>
    <Head :title="project.name" />

    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex justify-content-between align-items-center py-3">
                    <h4 class="page-title mb-0">Project Details: {{ project.name }}</h4>
                    <div class="page-title-right" v-if="$page.props.auth.roles.includes('admin') || $page.props.auth.roles.includes('staff')">
                        <Link :href="route('projects.edit', project.id)" class="btn btn-primary">Edit Project</Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Custom Tabs -->
        <ul class="nav nav-tabs nav-bordered mb-3">
            <li class="nav-item">
                <a href="javascript:void(0)" @click="activeTab = 'overview'" class="nav-link" :class="{ 'active': activeTab === 'overview' }">
                    <i class="ti ti-info-circle me-1"></i> Overview
                </a>
            </li>
            <li class="nav-item">
                <a href="javascript:void(0)" @click="activeTab = 'change-requests'" class="nav-link" :class="{ 'active': activeTab === 'change-requests' }">
                    <i class="ti ti-git-pull-request me-1"></i> Change Requests
                </a>
            </li>
            <li class="nav-item">
                <a href="javascript:void(0)" @click="activeTab = 'shared-files'" class="nav-link" :class="{ 'active': activeTab === 'shared-files' }">
                    <i class="ti ti-folder me-1"></i> Shared Files
                </a>
            </li>
        </ul>

        <div v-show="activeTab === 'overview'">
            <div class="row">
                <div class="col-xl-4 col-lg-5">
                    <!-- Project Info Card -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Project Overview</h4>
                            
                            <div class="text-start">
                                <p class="text-muted mb-2 font-13"><strong>Project Name :</strong> <span class="ms-2">{{ project.name }}</span></p>
                                <p class="text-muted mb-2 font-13"><strong>Client :</strong> <span class="ms-2">{{ project.client?.name || '-' }}</span></p>
                                <p class="text-muted mb-2 font-13"><strong>Status :</strong>
                                    <span class="badge ms-2" :class="project.status === 'completed' ? 'bg-success' : 'bg-primary'">{{ formatStatus(project.status) }}</span>
                                </p>
                                <p class="text-muted mb-2 font-13"><strong>Priority :</strong>
                                    <span class="badge ms-2" :class="getPriorityBadgeClass(project.priority)">{{ formatStatus(project.priority) }}</span>
                                </p>
                                <p class="text-muted mb-2 font-13"><strong>Budget :</strong> <span class="ms-2 text-dark fw-bold">{{ formatCurrency(project.budget, project.client?.currency?.code) }}</span></p>
                                <p class="text-muted mb-2 font-13"><strong>Start Date :</strong> <span class="ms-2">{{ formatDate(project.start_date) }}</span></p>
                                <p class="text-muted mb-2 font-13"><strong>End Date :</strong> <span class="ms-2">{{ formatDate(project.end_date) }}</span></p>
                            </div>

                            <div class="mt-3" v-if="project.tech_stack">
                                <h5 class="font-14">Tech Stack:</h5>
                                <div class="d-flex flex-wrap gap-1">
                                    <span v-for="tech in project.tech_stack.split(',')" :key="tech" class="badge bg-light text-dark border">{{ tech.trim() }}</span>
                                </div>
                            </div>

                            <div class="mt-3">
                                <h5 class="font-14">Description:</h5>
                                <p class="text-muted font-13">
                                    {{ project.description || 'No description provided.' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div> <!-- end col-->

                <div class="col-xl-8 col-lg-7">
                    <!-- Project Milestones Card -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Project Milestones</h4>
                            <div class="table-responsive">
                                <table class="table table-centered table-nowrap mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Milestone Name</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Hours</th>
                                            <th style="width: 150px;">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="milestone in project.milestones" :key="milestone.id">
                                            <td>
                                                <h5 class="font-14 my-1 fw-bold text-primary">{{ milestone.name }}</h5>
                                                <span class="text-muted font-13 d-block text-wrap" style="max-width: 300px;">{{ milestone.description }}</span>
                                            </td>
                                            <td>{{ formatDate(milestone.start_date) }}</td>
                                            <td>{{ formatDate(milestone.end_date) }}</td>
                                            <td><span class="badge bg-light text-dark">{{ milestone.hours || 'N/A' }} hrs</span></td>
                                            <td>
                                                <span class="badge bg-info-subtle text-info border border-info">Active</span>
                                            </td>
                                        </tr>
                                        <tr v-if="!project.milestones || project.milestones.length === 0">
                                            <td colspan="5" class="text-center py-3">No milestones defined for this project.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Related Invoices Card -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Project Invoices</h4>
                            <div class="table-responsive">
                                <table class="table table-centered table-nowrap mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Invoice #</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th style="width: 100px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="invoice in project.invoices" :key="invoice.id">
                                            <td>{{ invoice.invoice_number }}</td>
                                            <td>{{ formatDate(invoice.issue_date) }}</td>
                                            <td>{{ formatCurrency(invoice.total_amount, invoice.currency?.code) }}</td>
                                            <td>
                                                <span class="badge" :class="invoice.status === 'paid' ? 'bg-success' : 'bg-warning'">
                                                    {{ formatStatus(invoice.status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <a :href="route('invoices.view-pdf', invoice.id)" class="action-icon" title="View PDF" target="_blank"> <i class="ti ti-eye"></i></a>
                                            </td>
                                        </tr>
                                        <tr v-if="!project.invoices || project.invoices.length === 0">
                                            <td colspan="5" class="text-center py-3">No invoices found for this project.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div> <!-- end col -->
            </div> <!-- end row -->
        </div>


        <div v-if="activeTab === 'change-requests'">
            <ChangeRequestTab :project="project" />
        </div>

        <div v-if="activeTab === 'shared-files'">
            <SharedFilesTab :project="project" />
        </div>
    </AuthenticatedLayout>
</template>
