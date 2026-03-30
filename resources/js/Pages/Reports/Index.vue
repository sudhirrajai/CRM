<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Layout from '@/Layouts/AuthenticatedLayout.vue';
import VueApexCharts from "vue3-apexcharts";

const props = defineProps({
    stats: Object,
    charts: Object
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(amount);
};

const revenueChartOptions = {
    chart: { type: 'area', toolbar: { show: false }, zoom: { enabled: false } },
    colors: ['#3e60d5', '#f15776'],
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 2 },
    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.3, opacityTo: 0.05, stops: [0, 90, 100] } },
    xaxis: { categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'] },
    tooltip: { y: { formatter: val => formatCurrency(val) } }
};

const revenueSeries = computed(() => {
    const revData = new Array(12).fill(0);
    const expData = new Array(12).fill(0);
    props.charts.revenue.forEach(item => revData[item.month - 1] = item.total);
    props.charts.expenses.forEach(item => expData[item.month - 1] = item.total);
    return [
        { name: 'Revenue', data: revData },
        { name: 'Expenses', data: expData }
    ];
});

const projectChartOptions = {
    chart: { type: 'donut' },
    labels: props.charts.projects.map(p => p.status.replace('_', ' ').toUpperCase()),
    colors: ['#3e60d5', '#47ad59', '#fab116', '#fa5c7c'],
    legend: { position: 'bottom' },
    plotOptions: { pie: { donut: { size: '70%', labels: { show: true, total: { show: true, label: 'Total' } } } } }
};

const projectSeries = computed(() => props.charts.projects.map(p => p.total));
</script>

<template>
    <Head title="Reports Dashboard" />

    <Layout>
        <div class="row">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h4 class="page-title mb-0">Reports Dashboard</h4>
                    <div class="d-flex gap-2">
                        <Link :href="route('reports.export', { type: 'summary' })" class="btn btn-primary btn-sm">
                            <i class="ti ti-download me-1"></i> Export Summary
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="card card-h-100 border-start border-primary border-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted fw-medium text-uppercase fs-12 mb-1">Total Revenue</p>
                                <h3 class="mb-0">{{ formatCurrency(stats.total_revenue) }}</h3>
                            </div>
                            <div class="avatar-md bg-soft-primary rounded-circle d-flex align-items-center justify-content-center">
                                <i class="ti ti-currency-dollar fs-2 text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-h-100 border-start border-danger border-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted fw-medium text-uppercase fs-12 mb-1">Total Expenses</p>
                                <h3 class="mb-0">{{ formatCurrency(stats.total_expenses) }}</h3>
                            </div>
                            <div class="avatar-md bg-soft-danger rounded-circle d-flex align-items-center justify-content-center">
                                <i class="ti ti-receipt-tax fs-2 text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-h-100 border-start border-success border-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted fw-medium text-uppercase fs-12 mb-1">Active Projects</p>
                                <h3 class="mb-0">{{ stats.active_projects }}</h3>
                            </div>
                            <div class="avatar-md bg-soft-success rounded-circle d-flex align-items-center justify-content-center">
                                <i class="ti ti-briefcase fs-2 text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-h-100 border-start border-info border-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted fw-medium text-uppercase fs-12 mb-1">Total Clients</p>
                                <h3 class="mb-0">{{ stats.total_clients }}</h3>
                            </div>
                            <div class="avatar-md bg-soft-info rounded-circle d-flex align-items-center justify-content-center">
                                <i class="ti ti-users fs-2 text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Revenue vs Expenses (Yearly)</h5>
                        <Link :href="route('reports.profit-loss')" class="btn btn-soft-primary btn-sm">View Details</Link>
                    </div>
                    <div class="card-body">
                        <VueApexCharts height="350" :options="revenueChartOptions" :series="revenueSeries" />
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Project Status</h5>
                        <Link :href="route('reports.projects')" class="btn btn-soft-primary btn-sm">View All</Link>
                    </div>
                    <div class="card-body">
                        <VueApexCharts height="350" :options="projectChartOptions" :series="projectSeries" />
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Available Reports</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <Link :href="route('reports.profit-loss')" class="card bg-light border-0 text-decoration-none h-100">
                                    <div class="card-body text-center p-4">
                                        <div class="avatar-lg bg-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm text-primary">
                                            <i class="ti ti-chart-bar fs-1"></i>
                                        </div>
                                        <h5 class="mb-1">Profit & Loss</h5>
                                        <p class="text-muted small mb-0">Detailed financial performance tracking.</p>
                                    </div>
                                </Link>
                            </div>
                            <div class="col-md-4">
                                <Link :href="route('reports.projects')" class="card bg-light border-0 text-decoration-none h-100">
                                    <div class="card-body text-center p-4">
                                        <div class="avatar-lg bg-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm text-success">
                                            <i class="ti ti-briefcase fs-1"></i>
                                        </div>
                                        <h5 class="mb-1">Project Performance</h5>
                                        <p class="text-muted small mb-0">Track milestones and project healthy.</p>
                                    </div>
                                </Link>
                            </div>
                            <div class="col-md-4">
                                <Link :href="route('reports.clients')" class="card bg-light border-0 text-decoration-none h-100">
                                    <div class="card-body text-center p-4">
                                        <div class="avatar-lg bg-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm text-info">
                                            <i class="ti ti-user-plus fs-1"></i>
                                        </div>
                                        <h5 class="mb-1">Client Analytics</h5>
                                        <p class="text-muted small mb-0">Monitor growth and hosting subscriptions.</p>
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>

<style scoped>
.bg-soft-primary { background-color: rgba(62, 96, 213, 0.1); }
.bg-soft-danger { background-color: rgba(241, 87, 118, 0.1); }
.bg-soft-success { background-color: rgba(71, 173, 89, 0.1); }
.bg-soft-info { background-color: rgba(26, 188, 156, 0.1); }

.avatar-md {
    height: 48px;
    width: 48px;
}
.avatar-lg {
    height: 64px;
    width: 64px;
}
</style>
