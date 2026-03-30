<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    ticket: {
        type: Object,
        required: true
    },
    users: {
        type: Array,
        required: true
    },
    canManage: {
        type: Boolean,
        default: false
    }
});

const replyForm = useForm({
    message: '',
});

const assignForm = useForm({
    user_id: props.ticket.assigned_to || '',
});

const statusForm = useForm({
    status: props.ticket.status,
});

const submitReply = () => {
    replyForm.post(route('tickets.update', props.ticket.id), {
        onSuccess: () => replyForm.reset(),
    });
};

const assignTicket = () => {
    assignForm.post(route('tickets.assign', props.ticket.id));
};

const updateStatus = () => {
    statusForm.post(route('tickets.update-status', props.ticket.id));
};

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
        'open': 'bg-primary text-white',
        'in-progress': 'bg-info text-white',
        'closed': 'bg-secondary text-white',
        'pending': 'bg-warning text-white'
    };
    return classes[status] || 'bg-secondary';
};
</script>

<template>
    <Head :title="`Ticket: ${ticket.subject}`" />

    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <Link :href="route('tickets.index')" class="btn btn-light">
                            Back to Tickets
                        </Link>
                    </div>
                    <h4 class="page-title">Support Ticket Detail</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 text-white">#{{ ticket.id.substring(0, 8) }} - {{ ticket.subject }}</h5>
                        <span class="badge text-capitalize" :class="getStatusClass(ticket.status)">{{ ticket.status }}</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="px-4 py-3 bg-light border-bottom">
                            <div class="row small">
                                <div class="col-md-4">
                                    <span class="text-muted">Client:</span> <span class="fw-semibold">{{ ticket.client?.name }}</span>
                                </div>
                                <div class="col-md-4">
                                    <span class="text-muted">Project:</span> <span class="fw-semibold">{{ ticket.project?.name || 'General Support' }}</span>
                                </div>
                                <div class="col-md-4">
                                    <span class="text-muted">Priority:</span> 
                                    <span class="badge text-capitalize ms-1" :class="getPriorityClass(ticket.priority)">{{ ticket.priority }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Ticket Conversation -->
                        <div class="p-4" style="max-height: 500px; overflow-y: auto;">
                            <div v-for="message in ticket.messages" :key="message.id" class="mb-4 d-flex" 
                                 :class="{'justify-content-end': message.user_id === $page.props.auth.user.id}">
                                
                                <div class="d-flex" :class="{'flex-row-reverse': message.user_id === $page.props.auth.user.id}" style="max-width: 80%;">
                                    <div class="avatar-sm me-2 ms-2">
                                        <span class="avatar-title rounded-circle" :class="message.user_id === $page.props.auth.user.id ? 'bg-primary' : 'bg-info'">
                                            {{ message.user.name.charAt(0) }}
                                        </span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="card shadow-sm mb-1" :class="{'bg-primary text-white': message.user_id === $page.props.auth.user.id}">
                                            <div class="card-body py-2 px-3">
                                                <p class="mb-0">{{ message.message }}</p>
                                            </div>
                                        </div>
                                        <div class="small text-muted" :class="{'text-end': message.user_id === $page.props.auth.user.id}">
                                            {{ message.user.name }} - {{ new Date(message.created_at).toLocaleString() }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Reply Form -->
                        <div class="p-4 border-top">
                            <form @submit.prevent="submitReply">
                                <div class="mb-3">
                                    <textarea v-model="replyForm.message" class="form-control" rows="4" placeholder="Type your reply here..." required></textarea>
                                    <div v-if="replyForm.errors.message" class="text-danger small mt-1">{{ replyForm.errors.message }}</div>
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary" :disabled="replyForm.processing">
                                        Send Reply
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4" v-if="canManage">
                <div class="card mb-4">
                    <div class="card-header bg-soft-primary">
                        <h5 class="card-title mb-0">Management Controls</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label">Update Status</label>
                            <div class="input-group">
                                <select v-model="statusForm.status" class="form-select">
                                    <option value="open">Open</option>
                                    <option value="in-progress">In-Progress</option>
                                    <option value="closed">Closed</option>
                                    <option value="pending">Pending</option>
                                </select>
                                <button @click="updateStatus" class="btn btn-primary" :disabled="statusForm.processing">Update</button>
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label">Assign To Staff</label>
                            <div class="input-group">
                                <select v-model="assignForm.user_id" class="form-select">
                                    <option value="">Select Staff</option>
                                    <option v-for="user in users" :key="user.id" :value="user.id">
                                        {{ user.name }}
                                    </option>
                                </select>
                                <button @click="assignTicket" class="btn btn-primary" :disabled="assignForm.processing">Assign</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-soft-info">
                        <h5 class="card-title mb-0">Client Information</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-1 fw-semibold text-primary">{{ ticket.client?.name }}</p>
                        <p class="mb-3 text-muted small"><i class="ti ti-mail"></i> {{ ticket.client?.email }}</p>
                        
                        <div v-if="ticket.project">
                            <hr>
                            <p class="mb-1 text-muted small">Related Project:</p>
                            <p class="mb-0 fw-semibold">{{ ticket.project.name }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
