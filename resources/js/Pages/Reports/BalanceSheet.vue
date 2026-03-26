<script setup>
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import Layout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    income: Number,
    expense: Number,
    profit: Number,
    expenseBreakdown: Array,
    filters: Object
});

const filterForm = ref({
    filter_type: props.filters.filter_type,
    date: props.filters.date,
    start_date: props.filters.start_date,
    end_date: props.filters.end_date
});

const applyFilters = () => {
    router.get(route('reports.balance-sheet'), filterForm.value, { preserveState: true });
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(amount);
};

const profitPercent = computed(() => {
    if (props.income === 0) return 0;
    return (props.profit / props.income) * 100;
});
</script>

<template>
    <Head title="Balance Sheet" />

    <Layout>
        <div class="row">
            <div class="col-12">
                <h4 class="page-title mb-3">Balance Sheet Report</h4>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small text-uppercase fw-bold">Filter By</label>
                                <select v-model="filterForm.filter_type" class="form-select" @change="applyFilters">
                                    <option value="monthly">Monthly</option>
                                    <option value="yearly">Yearly</option>
                                    <option value="date_range">Date Range</option>
                                </select>
                            </div>
                            
                            <div v-if="filterForm.filter_type === 'monthly'" class="col-md-3">
                                <label class="form-label small text-uppercase fw-bold">Month</label>
                                <input v-model="filterForm.date" type="month" class="form-control" @change="applyFilters">
                            </div>
                            
                            <div v-if="filterForm.filter_type === 'yearly'" class="col-md-3">
                                <label class="form-label small text-uppercase fw-bold">Year</label>
                                <select v-model="filterForm.date" class="form-select" @change="applyFilters">
                                    <option v-for="year in [2024, 2025, 2026]" :key="year" :value="year">{{ year }}</option>
                                </select>
                            </div>

                            <template v-if="filterForm.filter_type === 'date_range'">
                                <div class="col-md-3">
                                    <label class="form-label small text-uppercase fw-bold">Start Date</label>
                                    <input v-model="filterForm.start_date" type="date" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small text-uppercase fw-bold">End Date</label>
                                    <input v-model="filterForm.end_date" type="date" class="form-control">
                                </div>
                                <div class="col-md-1">
                                    <button @click="applyFilters" class="btn btn-primary w-100"><i class="ti ti-search"></i></button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card bg-soft-success border-success-subtle">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-success rounded-circle d-flex align-items-center justify-content-center me-3">
                                <i class="ti ti-trending-up text-white fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-0 fw-medium">Total Income</p>
                                <h3 class="mb-0 text-success">{{ formatCurrency(income) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-soft-danger border-danger-subtle">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-danger rounded-circle d-flex align-items-center justify-content-center me-3">
                                <i class="ti ti-trending-down text-white fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-0 fw-medium">Total Expenses</p>
                                <h3 class="mb-0 text-danger">{{ formatCurrency(expense) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card" :class="profit >= 0 ? 'bg-soft-primary border-primary-subtle' : 'bg-soft-warning border-warning-subtle'">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center me-3" :class="profit >= 0 ? 'bg-primary' : 'bg-warning'">
                                <i class="ti text-white fs-4" :class="profit >= 0 ? 'ti-cash' : 'ti-alert-triangle'"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-0 fw-medium">Net Profit / Loss</p>
                                <h3 class="mb-0" :class="profit >= 0 ? 'text-primary' : 'text-warning'">{{ formatCurrency(profit) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Expense Breakdown by Category</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-centered mb-0 text-nowrap">
                                <thead class="table-light">
                                    <tr>
                                        <th>Category</th>
                                        <th class="text-end">Total Spent</th>
                                        <th class="text-end">% of Total Expense</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in expenseBreakdown" :key="item.name">
                                        <td><strong>{{ item.name }}</strong></td>
                                        <td class="text-end fw-bold">{{ formatCurrency(item.total) }}</td>
                                        <td class="text-end">
                                            <div class="d-flex align-items-center justify-content-end">
                                                <span class="me-2">{{ ((item.total / (expense || 1)) * 100).toFixed(1) }}%</span>
                                                <div class="progress progress-sm w-25">
                                                    <div class="progress-bar bg-info" :style="{ width: ((item.total / (expense || 1)) * 100) + '%' }"></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="expenseBreakdown.length === 0">
                                        <td colspan="3" class="text-center py-4 text-muted">No expenses found for this period.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Summary Breakdown</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                            <span class="text-muted">Income</span>
                            <span class="fw-bold text-success">{{ formatCurrency(income) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                            <span class="text-muted">Expense</span>
                            <span class="fw-bold text-danger">{{ formatCurrency(expense) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted font-16">Gross Profit</span>
                            <span class="fw-bold fs-4" :class="profit >= 0 ? 'text-primary' : 'text-danger'">{{ formatCurrency(profit) }}</span>
                        </div>
                        <div class="mt-4 pt-3 border-top">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Profit Margin</span>
                                <span class="fw-bold" :class="profitPercent >= 0 ? 'text-success' : 'text-danger'">{{ profitPercent.toFixed(2) }}%</span>
                            </div>
                            <div class="progress progress-sm">
                                <div class="progress-bar" :class="profitPercent >= 0 ? 'bg-success' : 'bg-danger'" :style="{ width: Math.min(Math.max(profitPercent, 0), 100) + '%' }"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>

<style scoped>
.bg-soft-success { background-color: rgba(30, 202, 113, 0.1); }
.bg-soft-danger { background-color: rgba(255, 91, 91, 0.1); }
.bg-soft-primary { background-color: rgba(54, 131, 252, 0.1); }
.bg-soft-warning { background-color: rgba(255, 187, 51, 0.1); }
.bg-soft-info { background-color: rgba(57, 181, 224, 0.1); }
</style>
