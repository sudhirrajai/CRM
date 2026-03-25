<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const props = defineProps({
    analytics: {
        type: Object,
        required: true
    }
});

onMounted(() => {
    // Revenue Chart
    const revenueData = props.analytics.revenue_overview || [];
    const revenueLabels = revenueData.map(item => {
        const date = new Date(item.month + '-01');
        return date.toLocaleString('default', { month: 'short' });
    });
    const revenueValues = revenueData.map(item => parseFloat(item.total));

    const revenueOptions = {
        series: [{
            name: 'Total Revenue',
            type: 'bar',
            data: revenueValues
        }],
        chart: {
            height: 300,
            type: 'line',
            toolbar: { show: false }
        },
        stroke: {
            curve: 'smooth',
            width: [0, 2]
        },
        colors: ['#6ac75a', '#313a46'],
        xaxis: {
            categories: revenueLabels.length > 0 ? revenueLabels : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        },
        yaxis: {
            labels: {
                formatter: (val) => '$' + val.toLocaleString()
            }
        },
        grid: {
            show: true,
            padding: { top: 0, right: -15, bottom: 15, left: -15 }
        },
        legend: { show: true },
        plotOptions: {
            bar: { columnWidth: '50%', borderRadius: 3 }
        }
    };

    if (window.ApexCharts && document.querySelector("#revenue-chart")) {
        const chart = new window.ApexCharts(document.querySelector("#revenue-chart"), revenueOptions);
        chart.render();
    }

    // Project Status Chart (RadialBar)
    const projectStats = props.analytics.project_stats || [];
    const statsSeries = projectStats.map(s => s.count);
    const statsLabels = projectStats.map(s => s.status.charAt(0).toUpperCase() + s.status.slice(1).replace('_', ' '));

    const trafficOptions = {
        chart: {
            height: 330,
            type: 'radialBar'
        },
        plotOptions: {
            radialBar: {
                track: { background: 'rgba(170,184,197, 0.2)' },
                dataLabels: { 
                    name: { show: true }, 
                    value: { show: true } 
                }
            }
        },
        stroke: { lineCap: 'round' },
        colors: ['#6ac75a', '#313a46', '#ce7e7e', '#669776', '#f1b44c'],
        series: statsSeries.length > 0 ? statsSeries : [0],
        labels: statsLabels.length > 0 ? statsLabels : ['No Data']
    };

    if (window.ApexCharts && document.querySelector("#multiple-radialbar")) {
        const chart = new window.ApexCharts(document.querySelector("#multiple-radialbar"), trafficOptions);
        chart.render();
    }
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column mb-4 mt-2">
                    <div class="flex-grow-1">
                        <h4 class="fs-18 text-uppercase fw-bold m-0">Dashboard</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <!-- Main Content -->
            <div class="col-xl-9">
                <!-- Stats Cards -->
                <div class="row g-3 mb-3">
                    <div class="col-md-3" v-if="analytics.is_admin">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="text-muted fs-12 text-uppercase mb-3" title="Number of Clients">Total Clients</h5>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-md flex-shrink-0">
                                        <span class="avatar-title text-bg-primary rounded-circle fs-20">
                                            <iconify-icon icon="solar:users-group-rounded-bold-duotone"></iconify-icon>
                                        </span>
                                    </div>
                                    <div>
                                        <h3 class="mb-0 fw-bold">{{ analytics.total_clients }}</h3>
                                        <p class="mb-0 text-muted fs-12">
                                            <span class="text-success me-1"><i class="ti ti-caret-up-filled"></i> Active</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div :class="analytics.is_admin ? 'col-md-3' : 'col-md-4'">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="text-muted fs-12 text-uppercase mb-3" title="Number of Projects">Active Projects</h5>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-md flex-shrink-0">
                                        <span class="avatar-title text-bg-primary rounded-circle fs-20">
                                            <iconify-icon icon="solar:case-round-minimalistic-bold-duotone"></iconify-icon>
                                        </span>
                                    </div>
                                    <div>
                                        <h3 class="mb-0 fw-bold">{{ analytics.active_projects }}</h3>
                                        <p class="mb-0 text-muted fs-12">
                                            <span class="text-success me-1"><i class="ti ti-caret-up-filled"></i> In Progress</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div :class="analytics.is_admin ? 'col-md-3' : 'col-md-4'">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="text-muted fs-12 text-uppercase mb-3" title="Total Invoices">{{ analytics.is_admin ? 'Total Invoices' : 'Total Bills' }}</h5>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-md flex-shrink-0">
                                        <span class="avatar-title text-bg-primary rounded-circle fs-20">
                                            <iconify-icon icon="solar:bill-list-bold-duotone"></iconify-icon>
                                        </span>
                                    </div>
                                    <div>
                                        <h3 class="mb-0 fw-bold">{{ analytics.total_invoices }}</h3>
                                        <p class="mb-0 text-muted fs-12">
                                            <span class="text-success me-1"><i class="ti ti-caret-up-filled"></i> Billed</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div :class="analytics.is_admin ? 'col-md-3' : 'col-md-4'">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="text-muted fs-12 text-uppercase mb-3" title="Number of Servers">{{ analytics.is_admin ? 'Active Servers' : 'My Hostings' }}</h5>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-md flex-shrink-0">
                                        <span class="avatar-title text-bg-primary rounded-circle fs-20">
                                            <iconify-icon icon="solar:server-square-bold-duotone"></iconify-icon>
                                        </span>
                                    </div>
                                    <div>
                                        <h3 class="mb-0 fw-bold">{{ analytics.servers_count }}</h3>
                                        <p class="mb-0 text-muted fs-12">
                                            <span class="text-success me-1"><i class="ti ti-caret-up-filled"></i> Online</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="row">
                    <div :class="analytics.is_admin ? 'col-xxl-4' : 'col-xxl-12'">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center border-bottom border-dashed">
                                <h4 class="header-title">Project Status</h4>
                            </div>
                            <div class="card-body">
                                <div id="multiple-radialbar" class="apex-charts"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-8" v-if="analytics.is_admin">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="header-title">Revenue Overview</h4>
                            </div>
                            <div class="card-body pt-0">
                                <div dir="ltr">
                                    <div id="revenue-chart" class="apex-charts"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="header-title">Recent Projects</h4>
                                <Link v-if="analytics.is_admin" href="/projects" class="btn btn-sm btn-outline-primary">View All</Link>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-custom table-centered table-nowrap table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Project Name</th>
                                                <th v-if="analytics.is_admin">Client</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="project in analytics.recent_projects" :key="project.id">
                                                <td>
                                                    <Link :href="'/projects/' + project.id" class="text-reset fw-semibold">{{ project.name }}</Link>
                                                </td>
                                                <td v-if="analytics.is_admin">{{ project.client?.name || 'N/A' }}</td>
                                                <td>
                                                    <span class="badge text-bg-primary-subtle text-primary text-uppercase">{{ project.status }}</span>
                                                </td>
                                                <td>{{ new Date(project.created_at).toLocaleDateString() }}</td>
                                            </tr>
                                            <tr v-if="!analytics.recent_projects?.length">
                                                <td colspan="4" class="text-center py-3 text-muted">No recent projects found.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar / Info Sidebar -->
            <div class="col-xl-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="text-muted fs-12 text-uppercase mb-3">Recent Invoices</h5>
                        <div v-for="invoice in analytics.recent_invoices" :key="invoice.id" class="d-flex align-items-center gap-2 position-relative mb-3">
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-primary-subtle text-primary rounded-circle">
                                    <i class="ti ti-file-invoice fs-16"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <Link :href="'/invoices/' + invoice.id" class="link-reset">
                                    <h5 class="fs-13 my-0">#{{ invoice.invoice_number }}</h5>
                                </Link>
                                <span class="text-muted fs-11">${{ parseFloat(invoice.total_amount).toLocaleString() }} - {{ invoice.status }}</span>
                            </div>
                        </div>
                        <div v-if="!analytics.recent_invoices?.length" class="text-center text-muted fs-13 py-2">
                            No recent invoices.
                        </div>
                    </div>

                    <div class="card-body p-0 border-top border-dashed" v-if="analytics.is_admin">
                        <h5 class="text-muted fs-12 text-uppercase px-3 mb-3 mt-3">Recent Clients</h5>
                        <div class="mb-3 px-3">
                            <div v-for="client in analytics.recent_clients" :key="client.id" class="d-flex align-items-center gap-2 mb-3">
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-success-subtle text-success rounded-circle">
                                        <i class="ti ti-user fs-16"></i>
                                    </span>
                                </div>
                                <div>
                                    <Link :href="'/clients/' + client.id" class="link-reset">
                                        <h5 class="fs-13 my-0">{{ client.name }}</h5>
                                    </Link>
                                    <span class="text-muted fs-11">{{ client.company || 'Private' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
