<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const activeTab = ref('projects');

defineProps({
    client: {
        type: Object,
        required: true
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
</script>

<template>
    <Head :title="client.name" />

    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex justify-content-between align-items-center py-3">
                    <h4 class="page-title mb-0">Client Details: {{ client.name }}</h4>
                    <div class="page-title-right">
                        <Link :href="route('clients.edit', client.id)" class="btn btn-primary">Edit Client</Link>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Client Info Card -->
            <div class="col-xl-4 col-lg-5">
                <div class="card text-center">
                    <div class="card-body">
                        <h4 class="mb-0 mt-2">{{ client.name }}</h4>
                        <p class="text-muted font-14">{{ client.company || 'No Company' }}</p>

                        <div class="text-start mt-3">
                            <p class="text-muted mb-2 font-13"><strong>Email :</strong> <span class="ms-2">{{ client.email }}</span></p>
                            <p class="text-muted mb-2 font-13"><strong>Phone :</strong><span class="ms-2">{{ client.phone || '-' }}</span></p>
                            <p class="text-muted mb-2 font-13"><strong>Address :</strong> <span class="ms-2">{{ client.address || '-' }}</span></p>
                            <p class="text-muted mb-2 font-13"><strong>Status :</strong>
                                <span class="badge ms-2" :class="client.status === 'active' ? 'bg-success' : 'bg-danger'">{{ formatStatus(client.status) }}</span>
                            </p>
                            <p class="text-muted mb-2 font-13" v-if="client.currency"><strong>Currency :</strong> <span class="ms-2">{{ client.currency.code }}</span></p>
                        </div>
                    </div>
                </div>
            </div> <!-- end col-->

            <div class="col-xl-8 col-lg-7">
                <!-- Projects, Invoices, Hostings tabs -->
                <div class="card">
                    <div class="card-body">
                        <ul class="nav nav-pills bg-nav-pills nav-justified mb-3">
                            <li class="nav-item">
                                <button @click="activeTab = 'projects'" class="nav-link rounded-0" :class="{ 'active': activeTab === 'projects' }">
                                    Projects
                                </button>
                            </li>
                            <li class="nav-item">
                                <button @click="activeTab = 'invoices'" class="nav-link rounded-0" :class="{ 'active': activeTab === 'invoices' }">
                                    Invoices
                                </button>
                            </li>
                            <li class="nav-item">
                                <button @click="activeTab = 'hostings'" class="nav-link rounded-0" :class="{ 'active': activeTab === 'hostings' }">
                                    Hostings
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <!-- Projects Tab -->
                            <div class="tab-pane show active" v-if="activeTab === 'projects'">
                                <div class="table-responsive">
                                    <table class="table table-borderless table-nowrap mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Project Name</th>
                                                <th>Status</th>
                                                <th>Start Date</th>
                                                <th>End Date</th>
                                                <th style="width: 100px;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="project in client.projects" :key="project.id">
                                                <td><Link :href="route('projects.edit', project.id)">{{ project.name }}</Link></td>
                                                <td><span class="badge" :class="project.status === 'completed' ? 'bg-success' : 'bg-primary'">{{ formatStatus(project.status) }}</span></td>
                                                <td>{{ formatDate(project.start_date) }}</td>
                                                <td>{{ formatDate(project.end_date) }}</td>
                                                <td>
                                                    <Link :href="route('projects.edit', project.id)" class="action-icon" title="Edit" v-if="!$page.props.auth.roles.includes('client')"> <i class="ti ti-edit"></i></Link>
                                                    <Link :href="route('projects.show', project.id)" class="action-icon" title="View"> <i class="ti ti-eye"></i></Link>
                                                </td>
                                            </tr>
                                            <tr v-if="!client.projects || client.projects.length === 0">
                                                <td colspan="5" class="text-center py-3">No projects found for this client.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Invoices Tab -->
                            <div class="tab-pane show active" v-if="activeTab === 'invoices'">
                                <div class="table-responsive">
                                    <table class="table table-borderless table-nowrap mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Invoice #</th>
                                                <th>Issue Date</th>
                                                <th>Total Amount</th>
                                                <th>Status</th>
                                                <th style="width: 100px;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="invoice in client.invoices" :key="invoice.id">
                                                <td><a :href="route('invoices.view-pdf', invoice.id)" target="_blank">{{ invoice.invoice_number }}</a></td>
                                                <td>{{ formatDate(invoice.issue_date) }}</td>
                                                <td>{{ formatCurrency(invoice.total_amount, client.currency?.code) }}</td>
                                                <td><span class="badge" :class="invoice.status === 'paid' ? 'bg-success' : 'bg-warning'">{{ formatStatus(invoice.status) }}</span></td>
                                                <td>
                                                    <a :href="route('invoices.view-pdf', invoice.id)" class="action-icon" title="View PDF" target="_blank"> <i class="ti ti-eye"></i></a>
                                                </td>
                                            </tr>
                                            <tr v-if="!client.invoices || client.invoices.length === 0">
                                                <td colspan="5" class="text-center py-3">No invoices found for this client.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Hostings Tab -->
                            <div class="tab-pane show active" v-if="activeTab === 'hostings'">
                                <div class="table-responsive">
                                    <table class="table table-borderless table-nowrap mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Domain</th>
                                                <th>Status</th>
                                                <th>Next Due Date</th>
                                                <th style="width: 100px;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="hosting in client.hostings" :key="hosting.id">
                                                <td><Link :href="route('hostings.edit', hosting.id)">{{ hosting.domain }}</Link></td>
                                                <td><span class="badge" :class="hosting.status === 'active' ? 'bg-success' : 'bg-danger'">{{ formatStatus(hosting.status) }}</span></td>
                                                <td>{{ formatDate(hosting.next_due_date) }}</td>
                                                <td>
                                                    <Link :href="route('hostings.edit', hosting.id)" class="action-icon" title="Edit"> <i class="ti ti-edit"></i></Link>
                                                </td>
                                            </tr>
                                            <tr v-if="!client.hostings || client.hostings.length === 0">
                                                <td colspan="4" class="text-center py-3">No hosting services found for this client.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- end col -->
        </div> <!-- end row -->
    </AuthenticatedLayout>
</template>
