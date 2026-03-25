<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    servers: {
        type: Array,
        required: true
    }
});

const deleteRecord = (id, name) => {
    if (confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
        router.delete(route('servers.destroy', id));
    }
};
</script>

<template>
    <Head title="Servers" />

    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">Root Servers</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="header-title">Managed Nodes</h4>
                            <Link :href="route('servers.create')" class="btn btn-primary" v-if="$page.props.auth.roles.includes('admin') || $page.props.auth.roles.includes('staff')">
                                Add New Server
                            </Link>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-centered mb-0 align-middle table-hover table-nowrap">
                                <thead class="table-light">
                                    <tr>
                                        <th>Server Name</th>
                                        <th>Provider</th>
                                        <th>IP Address</th>
                                        <th>Monthly Cost</th>
                                        <th>Status</th>
                                        <th style="width: 150px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="server in servers" :key="server.id">
                                        <td>{{ server.name }}</td>
                                        <td>{{ server.provider }}</td>
                                        <td>{{ server.ip_address }}</td>
                                        <td>
                                            {{ server.currency?.symbol_position === 'prefix' ? server.currency?.symbol : '' }}
                                            {{ server.monthly_cost }}
                                            {{ server.currency?.symbol_position === 'suffix' ? server.currency?.symbol : '' }}
                                        </td>
                                        <td>
                                            <span class="badge" :class="server.status === 'active' ? 'bg-success' : 'bg-danger'">
                                                {{ server.status }}
                                            </span>
                                        </td>
                                        <td>
                                            <Link :href="route('servers.edit', server.id)" class="action-icon" title="Edit"> <i class="ti ti-edit"></i></Link>
                                            <Link :href="route('servers.show', server.id)" class="action-icon" title="View"> <i class="ti ti-eye"></i></Link>
                                            <button @click="deleteRecord(server.id, server.name)" class="action-icon text-danger" title="Delete" v-if="$page.props.auth.roles.includes('admin')"> <i class="ti ti-trash"></i></button>
                                        </td>
                                    </tr>
                                    <tr v-if="servers.length === 0">
                                        <td colspan="6" class="text-center py-4">No active servers found.</td>
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
