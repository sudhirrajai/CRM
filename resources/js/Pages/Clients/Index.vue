<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    clients: {
        type: Array,
        required: true
    }
});

const deleteRecord = (id, name) => {
    if (confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
        router.delete(route('clients.destroy', id));
    }
};
</script>

<template>
    <Head title="Clients" />

    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">Clients Management</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="header-title">All Clients</h4>
                            <Link :href="route('clients.create')" class="btn btn-primary" v-if="$page.props.auth.roles.includes('admin') || $page.props.auth.roles.includes('staff')">
                                Add New Client
                            </Link>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-centered mb-0 align-middle table-hover table-nowrap">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Company</th>
                                        <th>Status</th>
                                        <th style="width: 150px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="client in clients" :key="client.id">
                                        <td>{{ client.name }}</td>
                                        <td>{{ client.email }}</td>
                                        <td>{{ client.company || '-' }}</td>
                                        <td>
                                            <span class="badge" :class="client.status === 'active' ? 'bg-success' : 'bg-danger'">
                                                {{ client.status }}
                                            </span>
                                        </td>
                                        <td>
                                            <Link :href="route('clients.edit', client.id)" class="action-icon" title="Edit"> <i class="ti ti-edit"></i></Link>
                                            <Link :href="route('clients.show', client.id)" class="action-icon" title="View"> <i class="ti ti-eye"></i></Link>
                                            <button @click="deleteRecord(client.id, client.name)" class="action-icon text-danger" title="Delete" v-if="$page.props.auth.roles.includes('admin')"> <i class="ti ti-trash"></i></button>
                                        </td>
                                    </tr>
                                    <tr v-if="clients.length === 0">
                                        <td colspan="5" class="text-center py-4">No clients found.</td>
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
