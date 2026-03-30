<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    tickets: {
        type: Array,
        required: true
    },
    canManage: {
        type: Boolean,
        default: false
    }
});

const getPriorityClass = (priority) => {
    const classes = {
        'low': 'bg-success-subtle text-success',
        'medium': 'bg-info-subtle text-info',
        'high': 'bg-warning-subtle text-warning',
        'urgent': 'bg-danger-subtle text-danger'
    };
    return classes[priority] || 'bg-secondary';
};

const getStatusClass = (status) => {
    const classes = {
        'open': 'bg-primary',
        'in-progress': 'bg-info',
        'closed': 'bg-secondary',
        'pending': 'bg-warning'
    };
    return classes[status] || 'bg-secondary';
};
</script>

<template>
    <Head title="Support Tickets" />

    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <Link :href="route('tickets.create')" class="btn btn-primary">
                            Create New Ticket
                        </Link>
                    </div>
                    <h4 class="page-title">Support Tickets</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div v-if="$page.props.flash.success" class="alert alert-success mt-2">
                            {{ $page.props.flash.success }}
                        </div>

                        <div class="table-responsive">
                            <table class="table table-centered table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Ticket ID</th>
                                        <th>Subject</th>
                                        <th v-if="canManage">Client</th>
                                        <th>Project</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Assigned To</th>
                                        <th>Date Created</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="ticket in tickets" :key="ticket.id">
                                        <td><span class="fw-bold">#{{ ticket.id.substring(0, 8) }}</span></td>
                                        <td>{{ ticket.subject }}</td>
                                        <td v-if="canManage">{{ ticket.client?.name || 'N/A' }}</td>
                                        <td>{{ ticket.project?.name || 'General Support' }}</td>
                                        <td>
                                            <span class="badge text-capitalize" :class="getPriorityClass(ticket.priority)">
                                                {{ ticket.priority }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge text-capitalize" :class="getStatusClass(ticket.status)">
                                                {{ ticket.status }}
                                            </span>
                                        </td>
                                        <td>
                                            <span v-if="ticket.assignee" class="badge bg-soft-primary text-primary">
                                                {{ ticket.assignee.name }}
                                            </span>
                                            <span v-else class="text-muted small">Unassigned</span>
                                        </td>
                                        <td>{{ new Date(ticket.created_at).toLocaleDateString() }}</td>
                                        <td class="text-end">
                                            <Link :href="route('tickets.show', ticket.id)" class="btn btn-soft-primary btn-sm">
                                                View
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="tickets.length === 0">
                                        <td colspan="9" class="text-center p-5 text-muted">
                                            No tickets found.
                                        </td>
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
