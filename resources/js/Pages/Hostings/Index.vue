<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    hostings: {
        type: Array,
        required: true
    }
});

const deleteRecord = (id, domain) => {
    if (confirm(`Are you sure you want to delete "${domain}"? This action cannot be undone.`)) {
        router.delete(route('hostings.destroy', id));
    }
};
</script>

<template>
    <Head title="Hostings" />

    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">Client Hosting Packages</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="header-title">VPS / Hosting Allocations</h4>
                            <Link :href="route('hostings.create')" class="btn btn-primary" v-if="$page.props.auth.roles.includes('admin') || $page.props.auth.roles.includes('staff')">
                                Allocate Hosting
                            </Link>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-centered mb-0 align-middle table-hover table-nowrap">
                                <thead class="table-light">
                                    <tr>
                                        <th>Domain</th>
                                        <th>Client</th>
                                        <th>Server</th>
                                        <th>Billing Cycle</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th style="width: 150px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="hosting in hostings" :key="hosting.id">
                                        <td>{{ hosting.domain }}</td>
                                        <td>{{ hosting.client?.name || '-' }}</td>
                                        <td>{{ hosting.server?.name || '-' }}</td>
                                        <td><span class="text-capitalize">{{ hosting.billing_cycle }}</span></td>
                                        <td>
                                            {{ hosting.currency?.symbol_position === 'prefix' ? hosting.currency?.symbol : '' }}
                                            {{ hosting.price }}
                                            {{ hosting.currency?.symbol_position === 'suffix' ? hosting.currency?.symbol : '' }}
                                        </td>
                                        <td>
                                            <span class="badge" :class="hosting.status === 'active' ? 'bg-success' : 'bg-danger'">
                                                {{ hosting.status }}
                                            </span>
                                        </td>
                                        <td>
                                            <Link :href="route('hostings.edit', hosting.id)" class="action-icon" title="Edit" v-if="!$page.props.auth.roles.includes('client')"> <i class="ti ti-edit"></i></Link>
                                            <Link :href="route('hostings.show', hosting.id)" class="action-icon" title="View"> <i class="ti ti-eye"></i></Link>
                                            <button @click="deleteRecord(hosting.id, hosting.domain)" class="action-icon text-danger" title="Delete" v-if="$page.props.auth.roles.includes('admin')"> <i class="ti ti-trash"></i></button>
                                        </td>
                                    </tr>
                                    <tr v-if="hostings.length === 0">
                                        <td colspan="7" class="text-center py-4">No hosting allocations found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
