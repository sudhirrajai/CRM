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
    periodLabel: String,
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
    }).format(Math.abs(amount));

    const symbol = props.currency.symbol_position === 'prefix'
        ? `${props.currency.symbol}${formatted}`
        : `${formatted} ${props.currency.symbol}`;

    return amount < 0 ? `-${symbol}` : symbol;
};

const profitPercent = computed(() => {
    if (props.income === 0) return 0;
    return (props.profit / props.income) * 100;
});
</script>

<template>
    <Head title="Balance Sheet" />

    <Layout>
        <!-- Page Header with Filters inline -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h4 class="page-title mb-0">Balance Sheet</h4>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <select v-model="filterForm.filter_type" class="form-select form-select-sm" style="width: 130px;" @change="applyFilters">
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                            <option value="date_range">Date Range</option>
                        </select>
                        <input v-if="filterForm.filter_type === 'monthly'" v-model="filterForm.date" type="month" class="form-control form-control-sm" style="width: 170px;" @change="applyFilters">
                        <select v-if="filterForm.filter_type === 'yearly'" v-model="filterForm.date" class="form-select form-select-sm" style="width: 100px;" @change="applyFilters">
                            <option v-for="year in [2024, 2025, 2026]" :key="year" :value="year">{{ year }}</option>
                        </select>
                        <template v-if="filterForm.filter_type === 'date_range'">
                            <input v-model="filterForm.start_date" type="date" class="form-control form-control-sm" style="width: 150px;">
                            <span class="text-muted small">to</span>
                            <input v-model="filterForm.end_date" type="date" class="form-control form-control-sm" style="width: 150px;">
                            <button @click="applyFilters" class="btn btn-primary btn-sm"><i class="ti ti-search"></i></button>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats Row -->
        <div class="row mb-4 g-3">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                                <div class="avatar-sm rounded d-flex align-items-center justify-content-center" style="background: rgba(30,202,113,0.12);">
                                    <i class="ti ti-arrow-up-right text-success fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1 small">Total Income</p>
                                <h4 class="mb-0 fw-bold text-success">{{ formatCurrency(income) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                                <div class="avatar-sm rounded d-flex align-items-center justify-content-center" style="background: rgba(255,91,91,0.12);">
                                    <i class="ti ti-arrow-down-right text-danger fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1 small">Total Expenses</p>
                                <h4 class="mb-0 fw-bold text-danger">{{ formatCurrency(expense) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                                <div class="avatar-sm rounded d-flex align-items-center justify-content-center" :style="{ background: profit >= 0 ? 'rgba(62,96,213,0.12)' : 'rgba(255,187,51,0.12)' }">
                                    <i class="ti fs-4" :class="profit >= 0 ? 'ti-report-money text-primary' : 'ti-alert-triangle text-warning'"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1 small">Net Profit / Loss</p>
                                <h4 class="mb-0 fw-bold" :class="profit >= 0 ? 'text-primary' : 'text-warning'">{{ formatCurrency(profit) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Balance Sheet Table -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0 fw-bold">Balance Sheet</h5>
                            <p class="text-muted mb-0 small" v-if="periodLabel">As on {{ periodLabel }}</p>
                        </div>
                        <span v-if="currency" class="badge bg-light text-dark border px-2 py-1">
                            {{ currency.name }} ({{ currency.symbol }})
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <div class="row g-0 bs-sheet-row">
                            <!-- Left Column: Equity & Liabilities -->
                            <div class="col-lg-6 bs-col-left d-flex flex-column">
                                <div class="flex-grow-1">
                                    <table class="table table-borderless mb-0">
                                        <thead>
                                            <tr class="bg-light">
                                                <th class="ps-4 py-2 text-uppercase small fw-bold text-secondary" style="letter-spacing: 0.05em;">Equity & Liabilities</th>
                                                <th class="text-end pe-4 py-2 text-uppercase small fw-bold text-secondary" style="letter-spacing: 0.05em; width: 160px;">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody v-for="group in liabilities" :key="group.name">
                                            <!-- Group header -->
                                            <tr class="bs-group-header">
                                                <td class="ps-4 py-2 fw-bold text-dark">{{ group.name }}</td>
                                                <td class="text-end pe-4 py-2 fw-bold text-dark">{{ formatCurrency(group.total) }}</td>
                                            </tr>
                                            <!-- Line items -->
                                            <tr v-for="item in group.items" :key="item.name" class="bs-line-item">
                                                <td class="ps-5 py-2 text-muted">{{ item.name }}</td>
                                                <td class="text-end pe-4 py-2" :class="item.is_negative ? 'text-danger' : 'text-body'">
                                                    {{ item.is_negative ? '(' + formatCurrency(item.amount) + ')' : formatCurrency(item.amount) }}
                                                </td>
                                            </tr>
                                            <!-- Spacer -->
                                            <tr><td colspan="2" class="py-1 border-0"></td></tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- Pinned total bar -->
                                <div class="bs-total-bar mt-auto">
                                    <div class="d-flex justify-content-between align-items-center px-4 py-3">
                                        <span class="fw-bold">Total Equity & Liabilities</span>
                                        <span class="fw-bold fs-6 bs-total-val">{{ formatCurrency(totalVal) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Assets -->
                            <div class="col-lg-6 d-flex flex-column">
                                <div class="flex-grow-1">
                                    <table class="table table-borderless mb-0">
                                        <thead>
                                            <tr class="bg-light">
                                                <th class="ps-4 py-2 text-uppercase small fw-bold text-secondary" style="letter-spacing: 0.05em;">Assets</th>
                                                <th class="text-end pe-4 py-2 text-uppercase small fw-bold text-secondary" style="letter-spacing: 0.05em; width: 160px;">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody v-for="group in assets" :key="group.name">
                                            <tr class="bs-group-header">
                                                <td class="ps-4 py-2 fw-bold text-dark">{{ group.name }}</td>
                                                <td class="text-end pe-4 py-2 fw-bold text-dark">{{ formatCurrency(group.total) }}</td>
                                            </tr>
                                            <tr v-for="item in group.items" :key="item.name" class="bs-line-item">
                                                <td class="ps-5 py-2 text-muted">{{ item.name }}</td>
                                                <td class="text-end pe-4 py-2" :class="item.amount < 0 ? 'text-danger' : 'text-body'">
                                                    {{ item.amount < 0 ? '(' + formatCurrency(Math.abs(item.amount)) + ')' : formatCurrency(item.amount) }}
                                                </td>
                                            </tr>
                                            <tr><td colspan="2" class="py-1 border-0"></td></tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- Pinned total bar -->
                                <div class="bs-total-bar mt-auto">
                                    <div class="d-flex justify-content-between align-items-center px-4 py-3">
                                        <span class="fw-bold">Total Assets</span>
                                        <span class="fw-bold fs-6 bs-total-val">{{ formatCurrency(totalVal) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expense Breakdown & Summary -->
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold">Expense Breakdown</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-centered mb-0 text-nowrap align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Category</th>
                                        <th class="text-end">Amount</th>
                                        <th class="text-end pe-4">Share</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in expenseBreakdown" :key="item.name">
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-xs rounded-circle d-flex align-items-center justify-content-center me-2" style="background: rgba(62,96,213,0.1);">
                                                    <i class="ti ti-receipt text-primary" style="font-size: 14px;"></i>
                                                </div>
                                                <span class="fw-medium">{{ item.name }}</span>
                                            </div>
                                        </td>
                                        <td class="text-end fw-bold">{{ formatCurrency(item.total) }}</td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex align-items-center justify-content-end gap-2">
                                                <span class="small text-muted">{{ ((item.total / (expense || 1)) * 100).toFixed(1) }}%</span>
                                                <div class="progress rounded-pill" style="width: 60px; height: 5px;">
                                                    <div class="progress-bar bg-primary rounded-pill" :style="{ width: ((item.total / (expense || 1)) * 100) + '%' }"></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="expenseBreakdown.length === 0">
                                        <td colspan="3" class="text-center py-4 text-muted">
                                            <i class="ti ti-file-off d-block mb-1" style="font-size: 24px;"></i>
                                            No expenses found for this period.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold">Profit Summary</h5>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                                <div class="d-flex align-items-center">
                                    <i class="ti ti-arrow-up-right text-success me-2"></i>
                                    <span class="text-muted">Income</span>
                                </div>
                                <span class="fw-bold text-success">{{ formatCurrency(income) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                                <div class="d-flex align-items-center">
                                    <i class="ti ti-arrow-down-right text-danger me-2"></i>
                                    <span class="text-muted">Expenses</span>
                                </div>
                                <span class="fw-bold text-danger">{{ formatCurrency(expense) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-3">
                                <div class="d-flex align-items-center">
                                    <i class="ti ti-equal text-primary me-2"></i>
                                    <span class="fw-medium">Net Profit</span>
                                </div>
                                <span class="fw-bold fs-5" :class="profit >= 0 ? 'text-primary' : 'text-danger'">{{ formatCurrency(profit) }}</span>
                            </div>
                        </div>

                        <div class="mt-auto pt-3 border-top">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small">Profit Margin</span>
                                <span class="fw-bold small" :class="profitPercent >= 0 ? 'text-success' : 'text-danger'">{{ profitPercent.toFixed(1) }}%</span>
                            </div>
                            <div class="progress rounded-pill" style="height: 6px;">
                                <div class="progress-bar rounded-pill" :class="profitPercent >= 0 ? 'bg-success' : 'bg-danger'" :style="{ width: Math.min(Math.max(Math.abs(profitPercent), 0), 100) + '%' }"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>

<style scoped>
/* Column divider */
.bs-col-left {
    border-right: 1px solid #e9ecef;
}

/* Ensure both columns stretch to equal height */
.bs-sheet-row {
    align-items: stretch;
}

/* Group header row - subtle highlight */
.bs-group-header td {
    background-color: #f8f9fa;
    border-top: 1px solid #e9ecef;
}

/* Line items - clean and minimal */
.bs-line-item td {
    font-size: 0.875rem;
    border-bottom: 1px solid #f1f3f5;
}

/* Pinned total bar at bottom of each column */
.bs-total-bar {
    background-color: #212529;
    color: #fff;
    border-top: 2px solid #3e60d5;
}

.bs-total-val {
    border-bottom: 3px double #ffc107;
    padding-bottom: 2px;
}

/* Responsive stacking */
@media (max-width: 991.98px) {
    .bs-col-left {
        border-right: none;
        border-bottom: 1px solid #e9ecef;
    }
}
</style>
