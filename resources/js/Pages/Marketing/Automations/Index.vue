<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, Head, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';

const props = defineProps({
    automations: Array,
    triggerEvents: Object,
});

function deleteAutomation(id) {
    if (confirm('Delete this automation?')) {
        router.delete(route('marketing.automations.destroy', id));
    }
}

function toggleStatus(automation) {
    if (automation.status === 'active') {
        router.post(route('marketing.automations.deactivate', automation.id));
    } else {
        router.post(route('marketing.automations.activate', automation.id));
    }
}

function getStatusBadgeClass(status) {
    const map = {
        active: 'text-bg-success',
        inactive: 'text-bg-secondary',
        draft: 'text-bg-warning',
    };
    return map[status] || 'text-bg-secondary border border-dashed';
}

function getTriggerIcon(event) {
    const map = {
        'lead.created': 'solar:user-plus-bold-duotone',
        'lead.stage_changed': 'solar:alt-arrow-right-bold-duotone',
        'lead.converted': 'solar:magic-stick-bold-duotone',
        'client.created': 'solar:users-group-rounded-bold-duotone',
        'invoice.created': 'solar:bill-list-bold-duotone',
        'invoice.overdue': 'solar:bell-bing-bold-duotone',
        'ticket.created': 'solar:case-round-minimalistic-bold-duotone',
        'project.created': 'solar:folder-plus-bold-duotone',
    };
    return map[event] || 'solar:bolt-bold-duotone';
}
</script>

<template>
    <Head title="Automations" />
    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column mb-4 mt-2">
                    <div class="flex-grow-1">
                        <h4 class="fs-18 text-uppercase fw-bold m-0">Email Automations</h4>
                    </div>
                    <div class="d-flex gap-2">
                        <Link :href="route('marketing.automations.create')" class="btn btn-primary btn-sm">
                            <i class="ti ti-plus me-1"></i> New Automation
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div v-for="automation in automations" :key="automation.id" class="col-xl-6">
                <div class="card border shadow-none h-100 mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-md flex-shrink-0">
                                    <span class="avatar-title rounded-circle fs-24 bg-primary-subtle text-primary">
                                        <Icon :icon="getTriggerIcon(automation.trigger_event)" />
                                    </span>
                                </div>
                                <div class="overflow-hidden">
                                    <h5 class="mb-1 text-truncate fs-16">{{ automation.name }}</h5>
                                    <span class="badge fs-10" :class="getStatusBadgeClass(automation.status)">{{ automation.status }}</span>
                                </div>
                            </div>
                            <div class="form-check form-switch custom-switch-primary">
                                <input @change="toggleStatus(automation)" class="form-check-input" type="checkbox" :checked="automation.status === 'active'" style="height: 18px; width: 36px; cursor: pointer;" />
                            </div>
                        </div>

                        <p v-if="automation.description" class="text-muted fs-12 mb-3 text-truncate-2" style="height: 36px;">{{ automation.description }}</p>

                        <div class="d-flex gap-3 mb-4 flex-wrap bg-light-subtle rounded p-2">
                            <div class="d-flex align-items-center gap-1 text-muted fs-11">
                                <Icon icon="solar:bolt-bold-duotone" class="text-primary" /> <strong>Trigger:</strong> {{ automation.trigger_label }}
                            </div>
                            <div class="d-flex align-items-center gap-1 text-muted fs-11">
                                <Icon icon="solar:list-bold-duotone" class="text-primary" /> <strong>Steps:</strong> {{ automation.steps_count }}
                            </div>
                            <div class="d-flex align-items-center gap-1 text-muted fs-11">
                                <Icon icon="solar:users-group-rounded-bold-duotone" class="text-primary" /> <strong>Active:</strong> {{ automation.active_enrollments_count }}
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <Link :href="route('marketing.automations.edit', automation.id)" class="btn btn-sm btn-icon btn-light flex-fill" title="Configure Sequence">
                                <Icon icon="solar:settings-bold-duotone" class="me-1" /> Configure
                            </Link>
                            <button @click="deleteAutomation(automation.id)" class="btn btn-sm btn-icon btn-light text-danger" title="Delete Automation">
                                <Icon icon="solar:trash-bin-trash-bold-duotone" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="automations.length === 0" class="col-12 text-center py-5 text-muted">
                <Icon icon="solar:clining-robot-bold-duotone" class="fs-48 opacity-25 mb-2" />
                <p class="mb-0">No automations yet. Create automated sequences triggered by CRM events!</p>
                <Link :href="route('marketing.automations.create')" class="btn btn-sm btn-primary mt-2">Create Automation</Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.text-truncate-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>
