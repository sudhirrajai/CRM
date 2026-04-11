<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';

const props = defineProps({
    campaign: Object,
    stats: Object,
    opens: Array,
    clicks: Array,
});

function getStatusBadgeClass(status) {
    const map = {
        draft: 'text-bg-secondary',
        scheduled: 'text-bg-info',
        sending: 'text-bg-warning',
        sent: 'text-bg-success',
        paused: 'text-bg-dark',
        cancelled: 'text-bg-danger',
    };
    return map[status] || 'text-bg-secondary';
}
</script>

<template>
    <Head :title="'Analytics: ' + campaign.name" />
    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column mb-4 mt-2">
                    <div class="flex-grow-1">
                        <Link :href="route('marketing.campaigns.index')" class="text-muted fs-12 mb-1 d-block"><i class="ti ti-arrow-left me-1"></i> Back to Campaigns</Link>
                        <h4 class="fs-18 text-uppercase fw-bold m-0">{{ campaign.name }} <span class="badge ms-2" :class="getStatusBadgeClass(campaign.status)">{{ campaign.status }}</span></h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div v-for="(val, key) in { 'Total Sent': stats.sent, 'Opened': stats.opened, 'Clicked': stats.clicked, 'Unsubscribed': stats.unsubscribed, 'Bounced': stats.bounced }" :key="key" class="col-xl col-md-4 col-6">
                 <div class="card border shadow-none mb-0 h-100">
                    <div class="card-body p-3">
                        <p class="text-muted fs-11 text-uppercase fw-bold m-0">{{ key }}</p>
                        <h3 class="fw-bold m-0 fs-20">{{ val }}</h3>
                    </div>
                 </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-xl-4 col-md-6">
                <div class="card border shadow-none mb-0 h-100">
                    <div class="card-header border-bottom border-dashed py-3">
                        <h5 class="card-title fs-16 mb-0">Performance Rates</h5>
                    </div>
                    <div class="card-body">
                         <div class="mb-4">
                            <div class="d-flex justify-content-between mb-1 fs-12">
                                <span>Open Rate</span>
                                <span class="fw-bold">{{ stats.open_rate }}%</span>
                            </div>
                            <div class="progress progress-sm" style="height: 6px;">
                                <div class="progress-bar bg-success" :style="{ width: stats.open_rate + '%' }"></div>
                            </div>
                         </div>
                         <div class="mb-4">
                            <div class="d-flex justify-content-between mb-1 fs-12">
                                <span>Click-Through Rate (CTR)</span>
                                <span class="fw-bold">{{ stats.click_rate }}%</span>
                            </div>
                            <div class="progress progress-sm" style="height: 6px;">
                                <div class="progress-bar bg-primary" :style="{ width: stats.click_rate + '%' }"></div>
                            </div>
                         </div>
                         <div class="mb-0">
                            <div class="d-flex justify-content-between mb-1 fs-12">
                                <span>Bounce Rate</span>
                                <span class="fw-bold">{{ stats.bounce_rate }}%</span>
                            </div>
                            <div class="progress progress-sm" style="height: 6px;">
                                <div class="progress-bar bg-danger" :style="{ width: stats.bounce_rate + '%' }"></div>
                            </div>
                         </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-8 col-md-6">
                <div class="card border shadow-none mb-0 h-100">
                     <div class="card-header border-bottom border-dashed py-3">
                        <h5 class="card-title fs-16 mb-0">Engagement Logs</h5>
                    </div>
                    <div class="card-body p-0 overflow-auto" style="max-height: 400px;">
                         <table class="table table-custom table-centered table-nowrap mb-0">
                            <thead class="bg-light position-sticky top-0 z-1">
                                <tr>
                                    <th class="fs-11">Subscriber</th>
                                    <th class="fs-11">Action</th>
                                    <th class="fs-11">Time</th>
                                    <th class="fs-11">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="log in opens" :key="'open-' + log.id">
                                    <td>{{ log.subscriber_email }}</td>
                                    <td><span class="badge text-bg-success">Opened</span></td>
                                    <td class="text-muted fs-11">{{ new Date(log.opened_at).toLocaleString() }}</td>
                                    <td class="text-muted fs-11">{{ log.user_agent }}</td>
                                </tr>
                                <tr v-for="log in clicks" :key="'click-' + log.id">
                                    <td>{{ log.subscriber_email }}</td>
                                    <td><span class="badge text-bg-primary">Clicked</span></td>
                                    <td class="text-muted fs-11">{{ new Date(log.clicked_at).toLocaleString() }}</td>
                                    <td class="text-muted fs-11">{{ log.url }}</td>
                                </tr>
                                <tr v-if="opens.length === 0 && clicks.length === 0">
                                    <td colspan="4" class="text-center py-4 text-muted">No engagement data recorded yet.</td>
                                </tr>
                            </tbody>
                         </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
