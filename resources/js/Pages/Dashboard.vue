<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const props = defineProps({
    analytics: {
        type: Object,
        required: true
    }
});

onMounted(() => {
    // Revenue Chart
    const revenueOptions = {
        series: [{
            name: 'Total Income',
            type: 'bar',
            data: [89.25, 98.58, 68.74, 108.87, 77.54, 84.03, 51.24, 28.57, 92.57, 42.36, 88.51, 36.57]
        }, {
            name: 'Total Expenses',
            type: 'bar',
            data: [22.25, 24.58, 36.74, 22.87, 19.54, 25.03, 29.24, 10.57, 24.57, 35.36, 20.51, 17.57]
        }, {
            name: 'Investments',
            type: 'area',
            data: [34, 65, 46, 68, 49, 61, 42, 44, 78, 52, 63, 67]
        }, {
            name: 'Savings',
            type: 'line',
            data: [8, 12, 7, 17, 21, 11, 5, 9, 7, 29, 12, 35]
        }],
        chart: {
            height: 300,
            type: 'line',
            toolbar: { show: false }
        },
        stroke: {
            dashArray: [0, 0, 0, 8],
            width: [0, 0, 2, 2],
            curve: 'smooth'
        },
        colors: ['#6ac75a', '#313a46', '#ce7e7e', '#669776'],
        xaxis: {
            categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        },
        yaxis: {
            labels: {
                formatter: (val) => val + 'k'
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

    // Traffic Source Chart (RadialBar)
    const trafficOptions = {
        chart: {
            height: 330,
            type: 'radialBar'
        },
        plotOptions: {
            radialBar: {
                track: { background: 'rgba(170,184,197, 0.2)' },
                dataLabels: { name: { show: false }, value: { show: false } }
            }
        },
        stroke: { lineCap: 'round' },
        colors: ['#6ac75a', '#313a46', '#ce7e7e', '#669776'],
        series: [44, 55, 67, 22],
        labels: ['Direct', 'Social', 'Email', 'Referral']
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
                    <div class="mt-3 mt-sm-0">
                        <form action="javascript:void(0);">
                            <div class="row g-2 mb-0 align-items-center">
                                <div class="col-auto">
                                    <a href="javascript: void(0);" class="btn btn-outline-primary">
                                        <i class="ti ti-sort-ascending me-1"></i> Sort By
                                    </a>
                                </div>
                                <div class="col-sm-auto">
                                    <div class="input-group">
                                        <input type="text" class="form-control" data-provider="flatpickr" data-deafult-date="01 May to 31 May" data-date-format="d M" data-range-date="true">
                                        <span class="input-group-text bg-primary border-primary text-white">
                                            <i class="ti ti-calendar fs-15"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <!-- Stats Cards -->
                <div class="row row-cols-xxl-4 row-cols-md-2 row-cols-1 text-center">
                    <div class="col">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-muted fs-13 text-uppercase" title="Number of Clients">Total Clients</h5>
                                <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                                    <div class="user-img fs-42 flex-shrink-0">
                                        <span class="avatar-title text-bg-primary rounded-circle fs-22">
                                            <iconify-icon icon="solar:users-group-rounded-bold-duotone"></iconify-icon>
                                        </span>
                                    </div>
                                    <h3 class="mb-0 fw-bold">{{ analytics.total_clients }}</h3>
                                </div>
                                <p class="mb-0 text-muted">
                                    <span class="text-success me-2"><i class="ti ti-caret-up-filled"></i> Active</span>
                                    <span class="text-nowrap">Registered clients</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-muted fs-13 text-uppercase" title="Number of Projects">Active Projects</h5>
                                <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                                    <div class="user-img fs-42 flex-shrink-0">
                                        <span class="avatar-title text-bg-primary rounded-circle fs-22">
                                            <iconify-icon icon="solar:case-round-minimalistic-bold-duotone"></iconify-icon>
                                        </span>
                                    </div>
                                    <h3 class="mb-0 fw-bold">{{ analytics.active_projects }}</h3>
                                </div>
                                <p class="mb-0 text-muted">
                                    <span class="text-success me-2"><i class="ti ti-caret-up-filled"></i> In Progress</span>
                                    <span class="text-nowrap">Total active</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-muted fs-13 text-uppercase" title="Total Invoices">Total Invoices</h5>
                                <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                                    <div class="user-img fs-42 flex-shrink-0">
                                        <span class="avatar-title text-bg-primary rounded-circle fs-22">
                                            <iconify-icon icon="solar:bill-list-bold-duotone"></iconify-icon>
                                        </span>
                                    </div>
                                    <h3 class="mb-0 fw-bold">{{ analytics.total_invoices }}</h3>
                                </div>
                                <p class="mb-0 text-muted">
                                    <span class="text-success me-2"><i class="ti ti-caret-up-filled"></i> Billed</span>
                                    <span class="text-nowrap">Across system</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-muted fs-13 text-uppercase" title="Number of Servers">Active Servers</h5>
                                <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                                    <div class="user-img fs-42 flex-shrink-0">
                                        <span class="avatar-title text-bg-primary rounded-circle fs-22">
                                            <iconify-icon icon="solar:server-square-bold-duotone"></iconify-icon>
                                        </span>
                                    </div>
                                    <h3 class="mb-0 fw-bold">{{ analytics.servers_count }}</h3>
                                </div>
                                <p class="mb-0 text-muted">
                                    <span class="text-success me-2"><i class="ti ti-caret-up-filled"></i> Online</span>
                                    <span class="text-nowrap">Servers managed</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="row">
                    <div class="col-xxl-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center border-bottom border-dashed">
                                <h4 class="header-title">Top Traffic by Source</h4>
                            </div>
                            <div class="card-body">
                                <div id="multiple-radialbar" class="apex-charts"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-8">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="header-title">Overview</h4>
                            </div>
                            <div class="card-body pt-0">
                                <div dir="ltr">
                                    <div id="revenue-chart" class="apex-charts"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Brands Listing and Top Selling Products -->
                <div class="row">
                    <div class="col-xxl-6">
                        <div class="card">
                            <div class="d-flex card-header justify-content-between align-items-center">
                                <h4 class="header-title">Brands Listing</h4>
                                <a href="javascript:void(0);" class="btn btn-sm btn-secondary">Add Brand <i class="ti ti-plus ms-1"></i></a>
                            </div>
                            <div class="card-body p-0">
                                <div class="bg-success bg-opacity-10 py-1 text-center">
                                    <p class="m-0"><b>69</b> Active brands out of <span class="fw-medium">102</span></p>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-custom table-centered table-sm table-nowrap table-hover mb-0">
                                        <tbody>
                                            <!-- Brand Items -->
                                            <tr v-for="i in 5" :key="i">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-md flex-shrink-0 me-2">
                                                            <span class="avatar-title bg-primary-subtle rounded-circle">
                                                                <img :src="'/assets/images/products/logo/logo-' + i + '.svg'" alt="" height="22">
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <span class="text-muted fs-12">Clothing</span> <br />
                                                            <h5 class="fs-14 mt-1">Brand Name - Region</h5>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="text-muted fs-12">Established</span>
                                                    <h5 class="fs-14 mt-1 fw-normal">Since 2020</h5>
                                                </td>
                                                <td>
                                                    <span class="text-muted fs-12">Stores</span> <br />
                                                    <h5 class="fs-14 mt-1 fw-normal">1.5k</h5>
                                                </td>
                                                <td>
                                                    <span class="text-muted fs-12">Status</span>
                                                    <h5 class="fs-14 mt-1 fw-normal"><i class="ti ti-circle-filled fs-12 text-success"></i> Active</h5>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-6">
                        <div class="card">
                            <div class="card-header d-flex flex-wrap align-items-center gap-2 border-bottom border-dashed">
                                <h4 class="header-title me-auto">Top Selling Products</h4>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-custom align-middle table-nowrap table-hover mb-0">
                                        <tbody>
                                            <!-- Product Items -->
                                            <tr v-for="i in 5" :key="i">
                                                <td>
                                                    <div class="avatar-lg">
                                                        <img :src="'/assets/images/products/p-' + i + '.png'" alt="Product" class="img-fluid rounded-2">
                                                    </div>
                                                </td>
                                                <td class="ps-0">
                                                    <h5 class="fs-14 my-1">Product Title</h5>
                                                    <span class="text-muted fs-12">07 April 2024</span>
                                                </td>
                                                <td>
                                                    <h5 class="fs-14 my-1">$79.49</h5>
                                                    <span class="text-muted fs-12">Price</span>
                                                </td>
                                                <td>
                                                    <h5 class="fs-14 my-1">82</h5>
                                                    <span class="text-muted fs-12">Quantity</span>
                                                </td>
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
            <div class="col-auto info-sidebar">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title mb-3">Recent Orders:</h4>
                        <!-- Recent Orders -->
                        <div v-for="i in 5" :key="i" class="d-flex align-items-center gap-2 position-relative mb-2">
                            <div class="avatar-md flex-shrink-0">
                                <img :src="'/assets/images/products/p-' + i + '.png'" alt="product-pic" height="36">
                            </div>
                            <div>
                                <h5 class="fs-14 my-1">Order Item</h5>
                                <span class="text-muted fs-12">$29.99 x 1</span>
                            </div>
                            <div class="ms-auto">
                                <span class="badge badge-soft-success">Sold</span>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-0 border-top border-dashed">
                        <h4 class="header-title px-3 mb-2 mt-3">Recent Activity:</h4>
                        <div class="my-3 px-3" data-simplebar style="max-height: 370px;">
                            <div class="timeline-alt py-0">
                                <!-- Activity Items -->
                                <div v-for="i in 5" :key="i" class="timeline-item">
                                    <i class="ti ti-basket bg-info-subtle text-info timeline-icon"></i>
                                    <div class="timeline-item-info">
                                        <a href="javascript:void(0);" class="link-reset fw-semibold mb-1 d-block">Activity Title</a>
                                        <span class="mb-1">Details about the activity</span>
                                        <p class="mb-0 pb-3">
                                            <small class="text-muted">5 minutes ago</small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
