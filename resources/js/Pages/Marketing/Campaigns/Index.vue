<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Icon } from '@iconify/vue';

const props = defineProps({
    campaigns: Array,
    filters: Object,
});

const statusFilter = ref(props.filters?.status || '');

const filteredCampaigns = computed(() => {
    if (!statusFilter.value) return props.campaigns;
    return props.campaigns.filter(c => c.status === statusFilter.value);
});

function deleteCampaign(id) {
    if (confirm('Are you sure you want to delete this campaign?')) {
        router.delete(route('marketing.campaigns.destroy', id));
    }
}

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
    <Head title="Email Campaigns" />
    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column mb-4 mt-2">
                    <div class="flex-grow-1">
                        <h4 class="fs-18 text-uppercase fw-bold m-0">Email Campaigns</h4>
                    </div>
                    <div class="d-flex gap-2">
                        <Link :href="route('marketing.campaigns.create')" class="btn btn-primary btn-sm">
                            <i class="ti ti-plus me-1"></i> New Campaign
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="d-flex gap-2 flex-wrap">
                    <button @click="statusFilter = ''" :class="['btn btn-sm', !statusFilter ? 'btn-primary' : 'btn-outline-secondary border-dashed']">All</button>
                    <button v-for="s in ['draft', 'scheduled', 'sending', 'sent', 'paused']" :key="s" @click="statusFilter = s" :class="['btn btn-sm', statusFilter === s ? 'btn-primary' : 'btn-outline-secondary border-dashed']">
                        {{ s.charAt(0).toUpperCase() + s.slice(1) }}
                    </button>
                </div>
            </div>
        </div>

        <div class="card border shadow-none mb-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom table-centered table-nowrap table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="fs-12 ps-3">Campaign</th>
                                <th class="fs-12">Status</th>
                                <th class="fs-12 text-end">Sent</th>
                                <th class="fs-12 text-end">Opened</th>
                                <th class="fs-12 text-end">Clicked</th>
                                <th class="fs-12">Created</th>
                                <th class="fs-12 text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="campaign in filteredCampaigns" :key="campaign.id">
                                <td class="ps-3">
                                    <Link :href="route('marketing.campaigns.analytics', campaign.id)" class="fw-semibold text-reset">
                                        {{ campaign.name }}
                                    </Link>
                                    <div class="text-muted fs-11">{{ campaign.subject }}</div>
                                </td>
                                <td>
                                    <span class="badge" :class="getStatusBadgeClass(campaign.status)">{{ campaign.status }}</span>
                                </td>
                                <td class="text-end fw-bold fs-12">{{ campaign.sent_count }}</td>
                                <td class="text-end fw-bold fs-12 text-success">{{ campaign.opened_count }}</td>
                                <td class="text-end fw-bold fs-12 text-warning">{{ campaign.clicked_count }}</td>
                                <td class="text-muted fs-11">{{ new Date(campaign.created_at).toLocaleDateString() }}</td>
                                <td class="text-end pe-3">
                                    <div class="d-flex justify-content-end gap-1">
                                        <Link :href="route('marketing.campaigns.analytics', campaign.id)" class="btn btn-sm btn-icon btn-light" title="Analytics">
                                            <Icon icon="solar:chart-2-bold-duotone" />
                                        </Link>
                                        <Link v-if="campaign.status === 'draft'" :href="route('marketing.campaigns.edit', campaign.id)" class="btn btn-sm btn-icon btn-light" title="Edit">
                                            <Icon icon="solar:pen-new-square-bold-duotone" />
                                        </Link>
                                        <button v-if="campaign.status === 'draft'" @click="router.post(route('marketing.campaigns.send', campaign.id))" class="btn btn-sm btn-icon btn-light text-success" title="Send Now">
                                            <Icon icon="solar:send-bold-duotone" />
                                        </button>
                                        <button @click="deleteCampaign(campaign.id)" class="btn btn-sm btn-icon btn-light text-danger" title="Delete">
                                            <Icon icon="solar:trash-bin-trash-bold-duotone" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredCampaigns.length === 0">
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <Icon icon="solar:letter-unread-bold-duotone" class="fs-48 opacity-25 mb-2" />
                                    <p class="mb-0">No campaigns found. Start your first outreach today!</p>
                                    <Link :href="route('marketing.campaigns.create')" class="btn btn-sm btn-primary mt-2">New Campaign</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
