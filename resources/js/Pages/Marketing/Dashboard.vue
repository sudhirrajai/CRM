<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Icon } from '@iconify/vue';

const props = defineProps({
    stats: Object,
    recentCampaigns: Array,
    sendsOverTime: Array,
    opensOverTime: Array,
});

const kpis = computed(() => [
    { label: 'Total Sent', value: props.stats.total_sent, icon: 'solar:send-bold-duotone', color: '#3e60d5', bg: 'rgba(62, 96, 213, 0.1)' },
    { label: 'Open Rate', value: props.stats.open_rate + '%', icon: 'solar:letter-opened-bold-duotone', color: '#22c55e', bg: 'rgba(34, 197, 94, 0.1)' },
    { label: 'Click Rate', value: props.stats.click_rate + '%', icon: 'solar:cursor-bold-duotone', color: '#f59e0b', bg: 'rgba(245, 158, 11, 0.1)' },
    { label: 'Bounce Rate', value: props.stats.bounce_rate + '%', icon: 'solar:letter-unread-bold-duotone', color: '#ef4444', bg: 'rgba(239, 68, 68, 0.1)' },
    { label: 'Campaigns', value: props.stats.sent_campaigns, icon: 'solar:case-round-minimalistic-bold-duotone', color: '#8b5cf6', bg: 'rgba(139, 92, 246, 0.1)' },
    { label: 'Automations', value: props.stats.active_automations, icon: 'solar:clining-robot-bold-duotone', color: '#06b6d4', bg: 'rgba(6, 182, 212, 0.1)' },
]);

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
    <Head title="Marketing Dashboard" />
    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column mb-4 mt-2">
                    <div class="flex-grow-1">
                        <h4 class="fs-18 text-uppercase fw-bold m-0">Marketing Hub</h4>
                    </div>
                    <div class="d-flex gap-2">
                        <Link :href="route('marketing.campaigns.create')" class="btn btn-primary btn-sm">
                            <i class="ti ti-plus me-1"></i> New Campaign
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="row g-3 mb-4">
            <div v-for="kpi in kpis" :key="kpi.label" class="col-xl-2 col-md-4 col-6">
                <div class="card h-100 mb-0 shadow-none border">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title rounded-circle fs-18" :style="{ background: kpi.bg, color: kpi.color }">
                                    <Icon :icon="kpi.icon" />
                                </span>
                            </div>
                            <div class="overflow-hidden">
                                <h4 class="mb-0 fw-bold fs-16">{{ kpi.value }}</h4>
                                <p class="mb-0 text-muted fs-11 text-truncate">{{ kpi.label }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-xl-8">
                <div class="card border shadow-none mb-0 h-100">
                    <div class="card-header border-bottom border-dashed py-3 d-flex justify-content-between align-items-center">
                        <h4 class="header-title mb-0">Engagement Overview</h4>
                        <span class="badge text-bg-light text-muted border fs-11 fw-normal">Last 30 Days</span>
                    </div>
                    <div class="card-body py-5 text-center text-muted" v-if="!recentCampaigns?.length">
                        <Icon icon="solar:chart-2-bold-duotone" class="fs-48 opacity-25 mb-3" />
                        <p>No campaign data yet. Start sending to see analytics.</p>
                        <Link :href="route('marketing.campaigns.create')" class="btn btn-sm btn-primary">Create Campaign</Link>
                    </div>
                    <div class="card-body" v-else>
                        <!-- Chart would go here -->
                        <div class="alert alert-info py-2 fs-12">Analytics visualization is processing...</div>
                        <div class="table-responsive mt-3">
                             <table class="table table-custom table-centered table-nowrap table-hover mb-0">
                                <thead>
                                    <tr class="bg-light">
                                        <th class="fs-12">Campaign</th>
                                        <th class="fs-12">Status</th>
                                        <th class="fs-12 text-end">Sent</th>
                                        <th class="fs-12 text-end">Open Rate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="campaign in recentCampaigns" :key="campaign.id">
                                        <td>
                                            <Link :href="route('marketing.campaigns.analytics', campaign.id)" class="text-reset fw-semibold">{{ campaign.name }}</Link>
                                        </td>
                                        <td>
                                            <span class="badge" :class="getStatusBadgeClass(campaign.status)">{{ campaign.status }}</span>
                                        </td>
                                        <td class="text-end">{{ campaign.sent_count }}</td>
                                        <td class="text-end fw-bold" :class="campaign.open_rate > 20 ? 'text-success' : 'text-warning'">{{ campaign.open_rate }}%</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card border shadow-none mb-3">
                    <div class="card-header border-bottom border-dashed py-3">
                        <h4 class="header-title mb-0">Quick Actions</h4>
                    </div>
                    <div class="card-body p-2">
                        <div class="d-grid gap-1">
                            <Link :href="route('marketing.templates.create')" class="btn btn-sm btn-light text-start d-flex align-items-center gap-2 py-2">
                                <Icon icon="solar:palette-bold-duotone" class="text-primary fs-16" /> Design Template
                            </Link>
                            <Link :href="route('marketing.lists.create')" class="btn btn-sm btn-light text-start d-flex align-items-center gap-2 py-2">
                                <Icon icon="solar:users-group-rounded-bold-duotone" class="text-success fs-16" /> Create Mailing List
                            </Link>
                            <Link :href="route('marketing.automations.create')" class="btn btn-sm btn-light text-start d-flex align-items-center gap-2 py-2">
                                <Icon icon="solar:clining-robot-bold-duotone" class="text-warning fs-16" /> Build Automation
                            </Link>
                        </div>
                    </div>
                </div>

                <div class="card border shadow-none bg-primary text-white">
                    <div class="card-body text-center py-4">
                        <Icon icon="solar:medal-star-bold-duotone" class="fs-36 mb-2" />
                        <h5 class="fw-bold mb-1">Scale Your Outreach</h5>
                        <p class="fs-12 opacity-75 mb-3">Use automations to keep your leads engaged 24/7 without lifting a finger.</p>
                        <Link :href="route('marketing.automations.index')" class="btn btn-sm btn-light text-primary fw-bold">View Automations</Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
