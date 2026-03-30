<script setup>
import { computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import Layout from '@/Layouts/AuthenticatedLayout.vue';
import VueApexCharts from "vue3-apexcharts";

const props = defineProps({
    data: Array,
    filters: Object
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(amount);
};

const chartOptions = {
    chart: { type: 'bar', toolbar: { show: false } },
    plotOptions: { bar: { horizontal: false, columnWidth: '55%', endingShape: 'rounded' } },
    dataLabels: { enabled: false },
    stroke: { show: true, width: 2, colors: ['transparent'] },
    xaxis: { categories: props.data.map(d => d.month) },
    yaxis: { title: { text: 'INR' }, labels: { formatter: val => formatCurrency(val) } },
    fill: { opacity: 1 },
    tooltip: { y: { formatter: val => formatCurrency(val) } },
    colors: ['#3e60d5', '#f15776', '#47ad59']
};

const series = computed(() => [
    { name: 'Revenue', data: props.data.map(d => d.revenue) },
    { name: 'Expenses', data: props.data.map(d => d.expenses) },
    { name: 'Profit', data: props.data.map(d => d.profit) }
]);

const updateYear = (e) => {
    router.get(route('reports.profit-loss'), { year: e.target.value }, { preserveState: true });
};
</script>

<template>
    <Head title="Profit & Loss Report" />

    <Layout>
        <div class="row">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h4 class="page-title mb-0">Profit & Loss Report</h4>
                    <div class="d-flex align-items-center gap-2">
                        <select :value="filters.year" @change="updateYear" class="form-select form-select-sm" style="width: 100px;">
                            <option v-for="year in [2024, 2025, 2026]" :key="year" :value="year">{{ year }}</option>
                        </select>
                        <button class="btn btn-primary btn-sm">
                            <i class="ti ti-download me-1"></i> Export Excel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <VueApexCharts height="400" :options="chartOptions" :series="series" />
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Monthly Breakdown</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-centered mb-0 text-nowrap">
                                <thead class="table-light">
                                    <tr>
                                        <th>Month</th>
                                        <th class="text-end">Revenue</th>
                                        <th class="text-end">Expenses</th>
                                        <th class="text-end">Net Profit / Loss</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in data" :key="item.month">
                                        <td class="fw-bold">{{ item.month }}</td>
                                        <td class="text-end text-success fw-medium">{{ formatCurrency(item.revenue) }}</td>
                                        <td class="text-end text-danger fw-medium">{{ formatCurrency(item.expenses) }}</td>
                                        <td class="text-end fw-bold" :class="item.profit >= 0 ? 'text-primary' : 'text-warning'">
                                            {{ formatCurrency(item.profit) }}
                                        </td>
                                        <td class="text-center">
                                            <span v-if="item.profit > 0" class="badge bg-soft-success text-success">Profit</span>
                                            <span v-else-if="item.profit < 0" class="badge bg-soft-danger text-danger">Loss</span>
                                            <span v-else class="badge bg-soft-secondary text-secondary">Balanced</span>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr class="fw-bold">
                                        <td>TOTAL</td>
                                        <td class="text-end">{{ formatCurrency(data.reduce((acc, curr) => acc + curr.revenue, 0)) }}</td>
                                        <td class="text-end">{{ formatCurrency(data.reduce((acc, curr) => acc + curr.expenses, 0)) }}</td>
                                        <td class="text-end" :class="data.reduce((acc, curr) => acc + curr.profit, 0) >= 0 ? 'text-primary' : 'text-danger'">
                                            {{ formatCurrency(data.reduce((acc, curr) => acc + curr.profit, 0)) }}
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>

<style scoped>
.bg-soft-success { background-color: rgba(71, 173, 89, 0.1); }
.bg-soft-danger { background-color: rgba(241, 87, 118, 0.1); }
.bg-soft-secondary { background-color: rgba(108, 117, 125, 0.1); }
</style>
