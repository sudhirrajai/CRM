<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    projects: {
        type: Array,
        required: true
    }
});

const formatDate = (dateStr) => {
    if (!dateStr) return 'TBD';
    return new Date(dateStr).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};

const deleteRecord = (id, name) => {
    if (confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
        router.delete(route('projects.destroy', id));
    }
};
</script>

<template>
    <Head title="Projects" />

    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">Projects</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="header-title">Active Projects</h4>
                            <Link :href="route('projects.create')" class="btn btn-primary" v-if="$page.props.auth.roles.includes('admin') || $page.props.auth.roles.includes('staff')">
                                Create Project
                            </Link>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-centered mb-0 align-middle table-hover table-nowrap">
                                <thead class="table-light">
                                    <tr>
                                        <th>Project Name</th>
                                        <th>Client</th>
                                        <th>Start Date</th>
                                        <th>Status</th>
                                        <th style="width: 150px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="project in projects" :key="project.id">
                                        <td>{{ project.name }}</td>
                                        <td>{{ project.client?.name || '-' }}</td>
                                        <td>{{ formatDate(project.start_date) }}</td>
                                        <td>
                                            <span class="badge bg-primary">
                                                {{ project.status }}
                                            </span>
                                        </td>
                                        <td>
                                            <Link :href="route('projects.edit', project.id)" class="action-icon" title="Edit" v-if="$page.props.auth.roles.includes('admin') || $page.props.auth.roles.includes('staff')"> <i class="ti ti-edit"></i></Link>
                                            <Link :href="route('projects.show', project.id)" class="action-icon" title="View"> <i class="ti ti-eye"></i></Link>
                                            <button @click="deleteRecord(project.id, project.name)" class="action-icon text-danger" title="Delete" v-if="$page.props.auth.roles.includes('admin')"> <i class="ti ti-trash"></i></button>
                                        </td>
                                    </tr>
                                    <tr v-if="projects.length === 0">
                                        <td colspan="5" class="text-center py-4">No projects found.</td>
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
