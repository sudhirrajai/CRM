<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, computed } from 'vue';

const props = defineProps({
    analytics: {
        type: Object,
        required: true
    }
});

// Currency formatting helper
const formatCurrency = (amount, currency = null) => {
    const cur = currency || props.analytics.default_currency;
    const num = parseFloat(amount) || 0;
    const formatted = num.toLocaleString('en-IN', { 
        minimumFractionDigits: cur?.decimal_places || 0,
        maximumFractionDigits: cur?.decimal_places || 0 
    });
    if (!cur) return '$' + formatted;
    return cur.symbol_position === 'prefix' 
        ? cur.symbol + formatted 
        : formatted + ' ' + cur.symbol;
};

// Format individual invoice with its own currency
const formatInvoiceAmount = (invoice) => {
    return formatCurrency(invoice.total_amount, invoice.currency);
};

// Profit calculation
const netProfit = computed(() => {
    if (!props.analytics.is_admin) return 0;
    return (parseFloat(props.analytics.total_revenue) || 0) - (parseFloat(props.analytics.total_expenses) || 0);
});

const profitMargin = computed(() => {
    if (!props.analytics.is_admin) return 0;
    const revenue = parseFloat(props.analytics.total_revenue) || 0;
    if (revenue === 0) return 0;
    return ((netProfit.value / revenue) * 100).toFixed(1);
});

const getStatusClass = (status) => {
    const map = {
        'in_progress': 'bg-primary',
        'completed': 'bg-success', 
        'on_hold': 'bg-warning',
        'cancelled': 'bg-danger',
        'not_started': 'bg-secondary'
    };
    return map[status] || 'bg-secondary';
};

const getStatusLabel = (status) => {
    return (status || '').replace(/_/g, ' ');
};

const getInvoiceStatusClass = (status) => {
    const map = {
        'paid': 'text-bg-success',
        'sent': 'text-bg-info',
        'draft': 'text-bg-secondary',
        'overdue': 'text-bg-danger',
        'cancelled': 'text-bg-dark'
    };
    return map[status] || 'text-bg-secondary';
};

const getLeadStageColor = (stage) => {
    if (!stage) return '#6c757d';
    if (stage.is_won) return '#22c55e';
    if (stage.is_lost) return '#ef4444';
    return stage.color || '#3e60d5';
};

onMounted(() => {
    // Revenue Chart
    const revenueData = props.analytics.revenue_overview || [];
    const revenueLabels = revenueData.map(item => {
        const date = new Date(item.month + '-01');
        return date.toLocaleString('default', { month: 'short' });
    });
    const revenueValues = revenueData.map(item => parseFloat(item.total));

    const currencySymbol = props.analytics.default_currency?.symbol || '$';

    const revenueOptions = {
        series: [{
            name: 'Revenue',
            type: 'bar',
            data: revenueValues
        }],
        chart: {
            height: 280,
            type: 'bar',
            toolbar: { show: false },
            fontFamily: 'inherit'
        },
        stroke: {
            curve: 'smooth',
            width: [0]
        },
        colors: ['#3e60d5'],
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'light',
                type: 'vertical',
                shadeIntensity: 0.3,
                opacityFrom: 1,
                opacityTo: 0.8,
            }
        },
        xaxis: {
            categories: revenueLabels.length > 0 ? revenueLabels : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            labels: { style: { colors: '#8c98a4', fontSize: '11px' } }
        },
        yaxis: {
            labels: {
                formatter: (val) => currencySymbol + val.toLocaleString(),
                style: { colors: '#8c98a4', fontSize: '11px' }
            }
        },
        grid: {
            show: true,
            borderColor: '#f1f3f7',
            padding: { top: -10, right: 0, bottom: 0, left: 5 }
        },
        legend: { show: false },
        plotOptions: {
            bar: { columnWidth: '45%', borderRadius: 5 }
        },
        dataLabels: { enabled: false },
        tooltip: {
            y: {
                formatter: (val) => currencySymbol + val.toLocaleString()
            }
        }
    };

    if (window.ApexCharts && document.querySelector("#revenue-chart")) {
        const chart = new window.ApexCharts(document.querySelector("#revenue-chart"), revenueOptions);
        chart.render();
    }

    // Project Status Chart (Donut)
    const projectStats = props.analytics.project_stats || [];
    const statsSeries = projectStats.map(s => s.count);
    const statsLabels = projectStats.map(s => s.status.charAt(0).toUpperCase() + s.status.slice(1).replace('_', ' '));

    const donutOptions = {
        chart: {
            height: 260,
            type: 'donut',
            fontFamily: 'inherit'
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '72%',
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: 'Projects',
                            fontSize: '13px',
                            fontWeight: 600,
                            formatter: (w) => w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                        }
                    }
                }
            }
        },
        stroke: { width: 2, colors: ['#fff'] },
        colors: ['#3e60d5', '#22c55e', '#f59e0b', '#ef4444', '#8b5cf6'],
        series: statsSeries.length > 0 ? statsSeries : [0],
        labels: statsLabels.length > 0 ? statsLabels : ['No Data'],
        legend: {
            position: 'bottom',
            fontSize: '12px',
            markers: { size: 6, shape: 'circle' }
        },
        dataLabels: { enabled: false }
    };

    if (window.ApexCharts && document.querySelector("#project-donut-chart")) {
        const chart = new window.ApexCharts(document.querySelector("#project-donut-chart"), donutOptions);
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
                    <div class="d-flex gap-2">
                        <span class="badge text-bg-light text-muted border px-3 py-2 fs-12 fw-normal">
                            <i class="ti ti-calendar me-1"></i> {{ new Date().toLocaleDateString('en-IN', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' }) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <!-- Main Content -->
            <div class="col-xl-9">
                <!-- Stats Cards Row 1 -->
                <div class="row g-3 mb-3">
                    <div v-if="analytics.is_admin" class="col-md-3 col-6">
                        <div class="card h-100 mb-0">
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
                    <div :class="analytics.is_admin ? 'col-md-3 col-6' : 'col-md-4'">
                        <div class="card h-100 mb-0">
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
                    <div :class="analytics.is_admin ? 'col-md-3 col-6' : 'col-md-4'">
                        <div class="card h-100 mb-0">
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
                    <div :class="analytics.is_admin ? 'col-md-3 col-6' : 'col-md-4'">
                        <div class="card h-100 mb-0">
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

                <!-- Financial Stats (Admin Only) Row 2 -->
                <div v-if="analytics.is_admin" class="row g-3 mb-3">
                    <div class="col-md-3 col-6">
                        <div class="card border-start border-success border-3 mb-0">
                            <div class="card-body py-3">
                                <p class="text-muted fs-12 text-uppercase mb-1 fw-semibold">Total Revenue</p>
                                <h4 class="fw-bold text-success mb-0">{{ formatCurrency(analytics.total_revenue) }}</h4>
                                <p class="mb-0 text-muted fs-11 mt-1">From paid invoices</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card border-start border-danger border-3 mb-0">
                            <div class="card-body py-3">
                                <p class="text-muted fs-12 text-uppercase mb-1 fw-semibold">Total Expenses</p>
                                <h4 class="fw-bold text-danger mb-0">{{ formatCurrency(analytics.total_expenses) }}</h4>
                                <p class="mb-0 text-muted fs-11 mt-1">All time expenses</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card border-start border-warning border-3 mb-0">
                            <div class="card-body py-3">
                                <p class="text-muted fs-12 text-uppercase mb-1 fw-semibold">Outstanding</p>
                                <h4 class="fw-bold text-warning mb-0">{{ formatCurrency(analytics.outstanding_amount) }}</h4>
                                <p class="mb-0 text-muted fs-11 mt-1">{{ analytics.outstanding_count }} unpaid invoice(s)</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card border-start border-primary border-3 mb-0">
                            <div class="card-body py-3">
                                <p class="text-muted fs-12 text-uppercase mb-1 fw-semibold">Net Profit</p>
                                <h4 class="fw-bold mb-0" :class="netProfit >= 0 ? 'text-success' : 'text-danger'">{{ formatCurrency(netProfit) }}</h4>
                                <p class="mb-0 text-muted fs-11 mt-1">{{ profitMargin }}% margin</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="row g-3 mb-3">
                    <div :class="analytics.is_admin ? 'col-xxl-4 col-lg-5' : 'col-xxl-12'">
                        <div class="card h-100 mb-0">
                            <div class="card-header d-flex justify-content-between align-items-center border-bottom border-dashed py-3">
                                <h4 class="header-title mb-0">Project Status</h4>
                            </div>
                            <div class="card-body d-flex align-items-center justify-content-center">
                                <div id="project-donut-chart" class="apex-charts"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-8 col-lg-7" v-if="analytics.is_admin">
                        <div class="card h-100 mb-0">
                            <div class="card-header d-flex justify-content-between align-items-center py-3">
                                <h4 class="header-title mb-0">Revenue Overview</h4>
                                <span class="badge text-bg-light text-muted border fs-11 fw-normal">Last 12 months</span>
                            </div>
                            <div class="card-body pt-0">
                                <div dir="ltr">
                                    <div id="revenue-chart" class="apex-charts"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Leads Pipeline Summary (Admin Only) -->
                <div v-if="analytics.is_admin && analytics.total_leads > 0" class="row g-3 mb-3">
                    <div class="col-12">
                        <div class="card mb-0">
                            <div class="card-header d-flex justify-content-between align-items-center py-3">
                                <h4 class="header-title mb-0">Leads Pipeline</h4>
                                <Link href="/leads" class="btn btn-sm btn-outline-primary">View All</Link>
                            </div>
                            <div class="card-body">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar-md flex-shrink-0">
                                                <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-20">
                                                    <i class="ti ti-target"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <p class="text-muted fs-12 mb-0 text-uppercase">Total Leads</p>
                                                <h4 class="fw-bold mb-0">{{ analytics.total_leads }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar-md flex-shrink-0">
                                                <span class="avatar-title bg-success-subtle text-success rounded-circle fs-20">
                                                    <i class="ti ti-currency-rupee"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <p class="text-muted fs-12 mb-0 text-uppercase">Pipeline Value</p>
                                                <h4 class="fw-bold mb-0">{{ formatCurrency(analytics.leads_value) }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar-md flex-shrink-0">
                                                <span class="avatar-title bg-warning-subtle text-warning rounded-circle fs-20">
                                                    <i class="ti ti-chart-arrows"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <p class="text-muted fs-12 mb-0 text-uppercase">Conversion Rate</p>
                                                <h4 class="fw-bold mb-0">{{ analytics.leads_conversion_rate }}%</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Recent Leads List -->
                                <div v-if="analytics.recent_leads?.length" class="table-responsive">
                                    <table class="table table-sm table-centered table-nowrap table-hover mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="fs-12">Lead</th>
                                                <th class="fs-12">Stage</th>
                                                <th class="fs-12">Assigned</th>
                                                <th class="fs-12 text-end">Value</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="lead in analytics.recent_leads" :key="lead.id">
                                                <td>
                                                    <Link :href="'/leads/' + lead.id" class="text-reset fw-semibold">{{ lead.title }}</Link>
                                                </td>
                                                <td>
                                                    <span class="badge rounded-pill px-2 py-1" :style="{ backgroundColor: getLeadStageColor(lead.stage), color: '#fff', fontSize: '0.7rem' }">
                                                        {{ lead.stage?.name || 'N/A' }}
                                                    </span>
                                                </td>
                                                <td class="text-muted fs-12">{{ lead.assigned_user?.name || '-' }}</td>
                                                <td class="text-end fw-semibold">{{ lead.value ? formatCurrency(lead.value) : '-' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Projects Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center py-3">
                                <h4 class="header-title mb-0">Recent Projects</h4>
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
                                                    <span class="badge text-uppercase" :class="getStatusClass(project.status)" style="font-size: 0.65rem;">{{ getStatusLabel(project.status) }}</span>
                                                </td>
                                                <td class="text-muted fs-12">{{ new Date(project.created_at).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }) }}</td>
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

            <!-- Right Sidebar -->
            <div class="col-xl-3">
                <!-- Recent Invoices -->
                <div class="card">
                    <div class="card-header py-3 border-bottom border-dashed">
                        <h5 class="text-muted fs-12 text-uppercase mb-0 fw-semibold">Recent Invoices</h5>
                    </div>
                    <div class="card-body">
                        <div v-for="invoice in analytics.recent_invoices" :key="invoice.id" class="d-flex align-items-center gap-2 position-relative mb-3">
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-primary-subtle text-primary rounded-circle">
                                    <i class="ti ti-file-invoice fs-16"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <Link :href="'/invoices/' + invoice.id" class="link-reset">
                                    <h5 class="fs-13 my-0 text-truncate">#{{ invoice.invoice_number }}</h5>
                                </Link>
                                <div class="d-flex align-items-center gap-1">
                                    <span class="text-muted fs-11 fw-semibold">{{ formatInvoiceAmount(invoice) }}</span>
                                    <span class="badge fs-10 px-1" :class="getInvoiceStatusClass(invoice.status)">{{ invoice.status }}</span>
                                </div>
                            </div>
                        </div>
                        <div v-if="!analytics.recent_invoices?.length" class="text-center text-muted fs-13 py-2">
                            No recent invoices.
                        </div>
                    </div>
                </div>

                <!-- Recent Clients (Admin) -->
                <div class="card" v-if="analytics.is_admin">
                    <div class="card-header py-3 border-bottom border-dashed">
                        <h5 class="text-muted fs-12 text-uppercase mb-0 fw-semibold">Recent Clients</h5>
                    </div>
                    <div class="card-body">
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

                <!-- Quick Links (Admin) -->
                <div v-if="analytics.is_admin" class="card">
                    <div class="card-header py-3 border-bottom border-dashed">
                        <h5 class="text-muted fs-12 text-uppercase mb-0 fw-semibold">Quick Actions</h5>
                    </div>
                    <div class="card-body p-2">
                        <div class="d-grid gap-1">
                            <Link href="/invoices/create" class="btn btn-sm btn-light text-start d-flex align-items-center gap-2 py-2">
                                <i class="ti ti-file-plus text-primary"></i> Create Invoice
                            </Link>
                            <Link href="/projects/create" class="btn btn-sm btn-light text-start d-flex align-items-center gap-2 py-2">
                                <i class="ti ti-folder-plus text-success"></i> New Project
                            </Link>
                            <Link href="/clients/create" class="btn btn-sm btn-light text-start d-flex align-items-center gap-2 py-2">
                                <i class="ti ti-user-plus text-info"></i> Add Client
                            </Link>
                            <Link href="/leads/create" class="btn btn-sm btn-light text-start d-flex align-items-center gap-2 py-2">
                                <i class="ti ti-target text-warning"></i> Add Lead
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
