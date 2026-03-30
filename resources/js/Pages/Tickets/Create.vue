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
});

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
                <div class="card">
                    <div class="card-body">
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
                                    <textarea v-model="form.message" class="form-control" rows="8" placeholder="Describe your issue or request in detail..." required></textarea>
                                    <div v-if="form.errors.message" class="text-danger small mt-1">{{ form.errors.message }}</div>
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                        Create Ticket
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
