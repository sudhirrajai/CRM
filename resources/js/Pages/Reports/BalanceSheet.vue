<script setup>
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import Layout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    income: Number,
    expense: Number,
    profit: Number,
    expenseBreakdown: Array,
    currency: Object,
    liabilities: Array,
    assets: Array,
    totalVal: Number,
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
    if (!props.currency) {
        return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(amount);
    }
    const formatted = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: props.currency.decimal_places,
        maximumFractionDigits: props.currency.decimal_places
    }).format(amount);

    return props.currency.symbol_position === 'prefix'
        ? `${props.currency.symbol}${formatted}`
        : `${formatted} ${props.currency.symbol}`;
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

        <!-- Filter Controls -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-3">
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

        <!-- Classic Overview Cards -->
        <div class="row mb-4 g-3">
            <div class="col-md-4">
                <div class="card bg-soft-success border-success-subtle h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-success rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm">
                                <i class="ti ti-trending-up text-white fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-0 fw-medium">Total Income</p>
                                <h3 class="mb-0 text-success fw-bold">{{ formatCurrency(income) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-soft-danger border-danger-subtle h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-danger rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm">
                                <i class="ti ti-trending-down text-white fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-0 fw-medium">Total Expenses</p>
                                <h3 class="mb-0 text-danger fw-bold">{{ formatCurrency(expense) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm" :class="profit >= 0 ? 'bg-soft-primary border-primary-subtle' : 'bg-soft-warning border-warning-subtle'">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" :class="profit >= 0 ? 'bg-primary' : 'bg-warning'">
                                <i class="ti text-white fs-4" :class="profit >= 0 ? 'ti-cash' : 'ti-alert-triangle'"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-0 fw-medium">Net Profit / Loss</p>
                                <h3 class="mb-0 fw-bold" :class="profit >= 0 ? 'text-primary' : 'text-warning'">{{ formatCurrency(profit) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dynamic Traditional Indian T-Shaped Balance Sheet -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-dark py-3 d-flex justify-content-between align-items-center border-bottom border-warning border-2">
                        <h5 class="text-white mb-0 fw-bold d-flex align-items-center">
                            <i class="ti ti-notebook me-2 text-warning fs-3"></i>
                            Traditional T-Shaped Balance Sheet (Indian Format)
                        </h5>
                        <span class="badge bg-warning text-dark px-3 py-2 fw-bold font-12 shadow-sm rounded-pill">
                            Active Currency: {{ currency?.name || 'INR' }} ({{ currency?.code || 'INR' }})
                        </span>
                    </div>
                    <div class="card-body p-0 bg-white">
                        <div class="row g-0">
                            <!-- Left Column: Capital & Liabilities -->
                            <div class="col-lg-6 border-end border-light-subtle">
                                <div class="p-4">
                                    <h5 class="fw-bold text-secondary mb-3 border-bottom pb-2 tracking-wider uppercase font-12 d-flex align-items-center">
                                        <i class="ti ti-scale me-2 text-danger"></i>
                                        LIABILITIES & CAPITAL
                                    </h5>
                                    
                                    <div v-for="group in liabilities" :key="group.name" class="mb-4">
                                        <div class="d-flex justify-content-between align-items-center py-2 bg-light px-3 rounded-3 mb-2 shadow-xs">
                                            <span class="fw-bold text-dark font-13">{{ group.name }}</span>
                                            <span class="fw-bold text-dark font-13">{{ formatCurrency(group.total) }}</span>
                                        </div>
                                        <div class="ps-3 pe-2">
                                            <div v-for="item in group.items" :key="item.name" class="d-flex justify-content-between align-items-center py-2 border-bottom border-light">
                                                <span class="text-muted font-13">{{ item.name }}</span>
                                                <span class="fw-medium font-13" :class="item.is_positive ? 'text-success' : (item.is_negative ? 'text-danger' : 'text-dark')">
                                                    {{ item.is_negative ? '-' : '' }}{{ formatCurrency(item.amount) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Right Column: Assets -->
                            <div class="col-lg-6">
                                <div class="p-4">
                                    <h5 class="fw-bold text-secondary mb-3 border-bottom pb-2 tracking-wider uppercase font-12 d-flex align-items-center">
                                        <i class="ti ti-briefcase me-2 text-success"></i>
                                        ASSETS
                                    </h5>
                                    
                                    <div v-for="group in assets" :key="group.name" class="mb-4">
                                        <div class="d-flex justify-content-between align-items-center py-2 bg-light px-3 rounded-3 mb-2 shadow-xs">
                                            <span class="fw-bold text-dark font-13">{{ group.name }}</span>
                                            <span class="fw-bold text-dark font-13">{{ formatCurrency(group.total) }}</span>
                                        </div>
                                        <div class="ps-3 pe-2">
                                            <div v-for="item in group.items" :key="item.name" class="d-flex justify-content-between align-items-center py-2 border-bottom border-light">
                                                <span class="text-muted font-13">{{ item.name }}</span>
                                                <span class="fw-medium font-13 text-dark">{{ formatCurrency(item.amount) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Traditional Matching Totals Row -->
                        <div class="row g-0 bg-dark text-white py-3 border-top border-warning border-3 font-14">
                            <div class="col-lg-6 border-end border-light-subtle d-flex justify-content-between align-items-center px-4 py-2">
                                <span class="fw-bold text-warning-emphasis tracking-wider">TOTAL LIABILITIES & CAPITAL</span>
                                <span class="fw-bold border-double-bottom text-warning fs-5">{{ formatCurrency(totalVal) }}</span>
                            </div>
                            <div class="col-lg-6 d-flex justify-content-between align-items-center px-4 py-2">
                                <span class="fw-bold text-warning-emphasis tracking-wider">TOTAL ASSETS</span>
                                <span class="fw-bold border-double-bottom text-warning fs-5">{{ formatCurrency(totalVal) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- breakdown section -->
        <div class="row">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-light-subtle py-3">
                        <h5 class="card-title mb-0 fw-bold text-dark">Expense Breakdown by Category</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-centered mb-0 text-nowrap align-middle">
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
                                                <div class="progress progress-sm w-25" style="height: 6px;">
                                                    <div class="progress-bar bg-info rounded-pill" :style="{ width: ((item.total / (expense || 1)) * 100) + '%' }"></div>
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
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-light-subtle py-3">
                        <h5 class="card-title mb-0 fw-bold text-dark">Summary Breakdown</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3 pb-3 border-bottom border-light">
                            <span class="text-muted">Income</span>
                            <span class="fw-bold text-success">{{ formatCurrency(income) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 pb-3 border-bottom border-light">
                            <span class="text-muted">Expense</span>
                            <span class="fw-bold text-danger">{{ formatCurrency(expense) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted font-16">Gross Profit</span>
                            <span class="fw-bold fs-4" :class="profit >= 0 ? 'text-primary' : 'text-danger'">{{ formatCurrency(profit) }}</span>
                        </div>
                        <div class="mt-4 pt-3 border-top border-light">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Profit Margin</span>
                                <span class="fw-bold" :class="profitPercent >= 0 ? 'text-success' : 'text-danger'">{{ profitPercent.toFixed(2) }}%</span>
                            </div>
                            <div class="progress progress-sm" style="height: 6px;">
                                <div class="progress-bar rounded-pill" :class="profitPercent >= 0 ? 'bg-success' : 'bg-danger'" :style="{ width: Math.min(Math.max(profitPercent, 0), 100) + '%' }"></div>
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

.border-double-bottom {
    border-bottom: 3px double currentColor;
    padding-bottom: 2px;
}
.shadow-xs {
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}
</style>
