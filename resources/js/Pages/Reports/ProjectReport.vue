<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Layout from '@/Layouts/AuthenticatedLayout.vue';
import VueApexCharts from "vue3-apexcharts";

const props = defineProps({
    statusCounts: Array,
    upcomingDeadlines: Array
});

const chartOptions = {
    chart: { type: 'bar' },
    plotOptions: { bar: { borderRadius: 4, horizontal: true } },
    dataLabels: { enabled: false },
    xaxis: { categories: props.statusCounts.map(s => s.status.replace('_', ' ').toUpperCase()) },
    colors: ['#3e60d5']
};

const series = computed(() => [
    { name: 'Projects', data: props.statusCounts.map(s => s.total) }
]);

const formatDate = (date) => {
    return new Intl.DateTimeFormat('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(date));
};

const getDaysDiff = (date) => {
    const d1 = new Date();
    const d2 = new Date(date);
    const timeDiff = d2.getTime() - d1.getTime();
    return Math.ceil(timeDiff / (1000 * 3600 * 24));
};
</script>

<template>
    <Head title="Project Performance Report" />

    <Layout>
        <div class="row">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h4 class="page-title mb-0">Project Performance Report</h4>
                    <Link :href="route('reports.export', { type: 'projects' })" class="btn btn-primary btn-sm">
                        <i class="ti ti-download me-1"></i> Export Excel
                    </Link>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Project Status Distribution</h5>
                    </div>
                    <div class="card-body">
                        <VueApexCharts height="400" :options="chartOptions" :series="series" />
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Summary Highlights</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div v-for="status in statusCounts" :key="status.status" class="col-6">
                                <div class="p-3 border rounded text-center">
                                    <h2 class="mb-1 text-primary">{{ status.total }}</h2>
                                    <p class="text-muted text-uppercase fs-12 mb-0">{{ status.status.replace('_', ' ') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Upcoming End Dates & Project Tracking</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-centered mb-0 text-nowrap">
                                <thead class="table-light">
                                    <tr>
                                        <th>Project Name</th>
                                        <th>Client</th>
                                        <th>End Date</th>
                                        <th>Status</th>
                                        <th>Urgency</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="project in upcomingDeadlines" :key="project.id">
                                        <td class="fw-bold">{{ project.name }}</td>
                                        <td><span class="badge bg-light text-dark">ClientID: {{ project.client_id }}</span></td>
                                        <td>{{ formatDate(project.end_date) }}</td>
                                        <td>
                                            <span class="badge" :class="{
                                                'bg-soft-primary text-primary': project.status === 'in_progress',
                                                'bg-soft-warning text-warning': project.status === 'pending',
                                                'bg-soft-danger text-danger': project.status === 'on_hold',
                                                'bg-soft-success text-success': project.status === 'completed'
                                            }">{{ project.status.replace('_', ' ') }}</span>
                                        </td>
                                        <td>
                                            <span v-if="getDaysDiff(project.end_date) < 0" class="text-danger fw-bold">Overdue</span>
                                            <span v-else-if="getDaysDiff(project.end_date) < 7" class="text-warning fw-bold">Ends in {{ getDaysDiff(project.end_date) }} Days</span>
                                            <span v-else class="text-success fw-bold">{{ getDaysDiff(project.end_date) }} Days Left</span>
                                        </td>
                                        <td>
                                            <Link :href="route('projects.show', project.id)" class="btn btn-soft-primary btn-sm">View</Link>
                                        </td>
                                    </tr>
                                    <tr v-if="upcomingDeadlines.length === 0">
                                        <td colspan="6" class="text-center py-4">No upcoming projects found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>

<style scoped>
.bg-soft-primary { background-color: rgba(62, 96, 213, 0.1); }
.bg-soft-warning { background-color: rgba(250, 177, 22, 0.1); }
.bg-soft-danger { background-color: rgba(241, 87, 118, 0.1); }
.bg-soft-success { background-color: rgba(71, 173, 89, 0.1); }
</style>
