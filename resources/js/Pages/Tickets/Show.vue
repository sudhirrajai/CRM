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
    _method: 'put',
    message: '',
    attachments: [],
});

const handleReplyFileUpload = (e) => {
    replyForm.attachments = e.target.files;
};

const assignForm = useForm({
    user_id: props.ticket.assigned_to || '',
});

const statusForm = useForm({
    status: props.ticket.status,
});

const submitReply = () => {
    replyForm.post(route('tickets.update', props.ticket.id), {
        preserveScroll: true,
        onSuccess: () => {
            replyForm.reset();
            const fileInput = document.getElementById('reply-attachments');
            if (fileInput) fileInput.value = '';
        },
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

        <div class="row" :class="{ 'justify-content-center': !canManage }">
            <div :class="canManage ? 'col-lg-8' : 'col-lg-10'">
                <div class="card mb-4 shadow-sm border-0">
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
                                        <div class="card shadow-sm mb-1" :class="{'bg-primary text-white border-0': message.user_id === $page.props.auth.user.id, 'bg-light border-0': message.user_id !== $page.props.auth.user.id}">
                                            <div class="card-body py-2 px-3">
                                                <p class="mb-0 text-break" style="white-space: pre-wrap;">{{ message.message }}</p>

                                                <div v-if="message.attachments && message.attachments.length > 0" class="mt-2 pt-2 border-top" :class="message.user_id === $page.props.auth.user.id ? 'border-primary-subtle' : ''">
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <a v-for="attachment in message.attachments" :key="attachment.id" 
                                                           :href="route('tickets.attachments.download', { ticket: ticket.id, attachment: attachment.id })"
                                                           target="_blank"
                                                           class="badge bg-opacity-25 p-2 text-decoration-none d-flex align-items-center"
                                                           :class="message.user_id === $page.props.auth.user.id ? 'bg-white text-primary border border-white' : 'bg-primary text-white'">
                                                            <i class="ti ti-paperclip me-1"></i>
                                                            <span class="text-truncate" style="max-width: 150px;">{{ attachment.file_name }}</span>
                                                        </a>
                                                    </div>
                                                </div>
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
                        <div class="p-4 border-top bg-light-subtle">
                            <form @submit.prevent="submitReply">
                                <div class="mb-3">
                                    <label class="form-label text-muted small text-uppercase fw-semibold">Your Reply</label>
                                    <textarea v-model="replyForm.message" class="form-control bg-white" rows="4" style="resize: vertical; min-height: 100px;" placeholder="Type your reply here..." required></textarea>
                                    <div v-if="replyForm.errors.message" class="text-danger small mt-1">{{ replyForm.errors.message }}</div>
                                </div>

                                <div class="row align-items-end mt-3">
                                    <div class="col-md-8">
                                        <label for="reply-attachments" class="form-label small text-muted mb-1"><i class="ti ti-paperclip me-1"></i> Attachments</label>
                                        <input type="file" id="reply-attachments" @change="handleReplyFileUpload" class="form-control" multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.zip">
                                        <div class="form-text mt-1" style="font-size: 0.75rem;">Max 10MB per file.</div>
                                        <div v-for="(error, key) in replyForm.errors" :key="key">
                                            <div v-if="key.startsWith('attachments')" class="text-danger small mt-1">{{ error }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                        <button type="submit" class="btn btn-primary px-4" :disabled="replyForm.processing">
                                            <i class="ti ti-send me-1"></i> Send Reply
                                        </button>
                                    </div>
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
