<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Layout from '@/Layouts/AuthenticatedLayout.vue';
import VueApexCharts from "vue3-apexcharts";

const props = defineProps({
    clientAcquisition: Array,
    hostingDistribution: Array
});

const acquisitionChartOptions = {
    chart: { type: 'area', toolbar: { show: false } },
    colors: ['#3e60d5'],
    stroke: { curve: 'smooth', width: 2 },
    dataLabels: { enabled: false },
    xaxis: { categories: props.clientAcquisition.map(d => d.date) },
    fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.1 } }
};

const acquisitionSeries = [{ name: 'New Clients', data: props.clientAcquisition.map(d => d.total) }];

const hostingChartOptions = {
    chart: { type: 'pie' },
    labels: props.hostingDistribution.map(h => h.type.toUpperCase()),
    legend: { position: 'bottom' },
    colors: ['#3e60d5', '#47ad59', '#fab116', '#fa5c7c']
};

const hostingSeries = props.hostingDistribution.map(h => h.total);
</script>

<template>
    <Head title="Client & Hosting Analytics" />

    <Layout>
        <div class="row">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h4 class="page-title mb-0">Client & Hosting Analytics</h4>
                    <Link :href="route('reports.export', { type: 'clients' })" class="btn btn-primary btn-sm">
                        <i class="ti ti-download me-1"></i> Export Excel
                    </Link>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Client Acquisition (Last 30 Days)</h5>
                    </div>
                    <div class="card-body">
                        <VueApexCharts height="350" :options="acquisitionChartOptions" :series="acquisitionSeries" />
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Hosting Distribution</h5>
                    </div>
                    <div class="card-body">
                        <VueApexCharts height="350" :options="hostingChartOptions" :series="hostingSeries" />
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Client Overview Statistics</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="p-4 border rounded text-center bg-light border-0">
                                    <h1 class="mb-1 text-primary">{{ clientAcquisition.reduce((acc, curr) => acc + curr.total, 0) }}</h1>
                                    <p class="text-muted text-uppercase fs-12 mb-0">New Clients (30d)</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-4 border rounded text-center bg-light border-0">
                                    <h1 class="mb-1 text-success">{{ hostingDistribution.reduce((acc, curr) => acc + curr.total, 0) }}</h1>
                                    <p class="text-muted text-uppercase fs-12 mb-0">Total Active Subscriptions</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-4 border rounded text-center bg-light border-0">
                                    <h1 class="mb-1 text-info">{{ hostingDistribution.find(h => h.type === 'vps')?.total || 0 }}</h1>
                                    <p class="text-muted text-uppercase fs-12 mb-0">Total VPS Clients</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-4 border rounded text-center bg-light border-0">
                                    <h1 class="mb-1 text-warning">{{ hostingDistribution.find(h => h.type === 'shared')?.total || 0 }}</h1>
                                    <p class="text-muted text-uppercase fs-12 mb-0">Total Shared Hosting</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>

<style scoped>
.avatar-lg {
    height: 64px;
    width: 64px;
}
</style>
