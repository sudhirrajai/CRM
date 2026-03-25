<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    hosting: {
        type: Object,
        required: true
    }
});
</script>

<template>
    <Head title="Hosting Details" />

    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">Hosting Allocation Details</h4>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="header-title">Package Information</h4>
                            <Link :href="route('hostings.edit', hosting.id)" 
                                  class="btn btn-primary btn-sm" 
                                  v-if="$page.props.auth.roles.includes('admin') || $page.props.auth.roles.includes('staff')">
                                Edit Allocation
                            </Link>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">Domain / Hostname</label>
                                <p class="fw-bold">{{ hosting.domain }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Project</label>
                                <p class="fw-bold">{{ hosting.project?.name || '-' }}</p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">Client</label>
                                <p class="fw-bold">{{ hosting.client?.name || '-' }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Server Node</label>
                                <p class="fw-bold">{{ hosting.server?.name || '-' }} ({{ hosting.server?.ip_address }})</p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label text-muted">Billing Cycle</label>
                                <p class="fw-bold text-capitalize">{{ hosting.billing_cycle }}</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted">Price</label>
                                <p class="fw-bold">
                                    {{ hosting.currency?.symbol_position === 'prefix' ? hosting.currency?.symbol : '' }}
                                    {{ hosting.price }}
                                    {{ hosting.currency?.symbol_position === 'suffix' ? hosting.currency?.symbol : '' }}
                                </p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted">Next Due Date</label>
                                <p class="fw-bold text-danger">{{ hosting.next_due_date ? new Date(hosting.next_due_date).toLocaleDateString() : 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">Status</label>
                                <p>
                                    <span class="badge" :class="hosting.status === 'active' ? 'bg-success' : 'bg-danger'">
                                        {{ hosting.status }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <div v-if="hosting.plan_details" class="row mb-3">
                            <div class="col-12">
                                <label class="form-label text-muted">Plan Details / Notes</label>
                                <pre class="p-2 bg-light border rounded">{{ hosting.plan_details }}</pre>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <Link :href="route('hostings.index')" class="btn btn-light">Back to List</Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
