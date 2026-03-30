<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    projects: {
        type: Array,
        required: true
    }
});

const form = useForm({
    subject: '',
    project_id: null,
    priority: 'medium',
    message: '',
    attachments: [],
});

const handleFileUpload = (e) => {
    form.attachments = e.target.files;
};

const submit = () => {
    form.post(route('tickets.store'));
};
</script>

<template>
    <Head title="Create New Ticket" />

    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <Link :href="route('tickets.index')" class="btn btn-light">
                            Back to Tickets
                        </Link>
                    </div>
                    <h4 class="page-title">Create New Support Ticket</h4>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary-subtle border-bottom border-primary border-opacity-25 pb-3">
                        <h5 class="card-title mb-0 text-primary d-flex align-items-center">
                            <i class="ti ti-ticket me-2 fs-4"></i> New Ticket Details
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form @submit.prevent="submit">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Subject</label>
                                    <input type="text" v-model="form.subject" class="form-control" placeholder="Enter ticket subject" required>
                                    <div v-if="form.errors.subject" class="text-danger small mt-1">{{ form.errors.subject }}</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Relate to Project</label>
                                    <select v-model="form.project_id" class="form-select">
                                        <option :value="null">General Support (No Project)</option>
                                        <option v-for="project in projects" :key="project.id" :value="project.id">
                                            {{ project.name }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.project_id" class="text-danger small mt-1">{{ form.errors.project_id }}</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Priority</label>
                                    <select v-model="form.priority" class="form-select" required>
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                    <div v-if="form.errors.priority" class="text-danger small mt-1">{{ form.errors.priority }}</div>
                                </div>

                                <div class="col-12 mb-3">
                                    <label class="form-label">Description / Message</label>
                                    <textarea v-model="form.message" class="form-control bg-light-subtle" rows="8" style="resize: vertical; min-height: 150px;" placeholder="Describe your issue or request in detail... (You can drag the bottom right corner to resize)" required></textarea>
                                    <div v-if="form.errors.message" class="text-danger small mt-1">{{ form.errors.message }}</div>
                                </div>

                                <div class="col-12 mb-4">
                                    <label class="form-label">Attachments (Optional)</label>
                                    <input type="file" @change="handleFileUpload" class="form-control" multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.zip" />
                                    <div class="form-text mt-1 text-muted">You can select multiple files. Max size per file: 10MB.</div>
                                    <div v-if="form.errors.attachments" class="text-danger small mt-1">{{ form.errors.attachments }}</div>
                                    <div v-for="(error, key) in form.errors" :key="key">
                                        <div v-if="key.startsWith('attachments.')" class="text-danger small mt-1">{{ error }}</div>
                                    </div>
                                </div>

                                <div class="col-12 text-end border-top pt-3 mt-2">
                                    <button type="submit" class="btn btn-primary px-4" :disabled="form.processing">
                                        <i class="ti ti-send me-1"></i> Submit Ticket
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
