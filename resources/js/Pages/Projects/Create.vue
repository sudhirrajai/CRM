<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

defineProps({
    clients: {
        type: Array,
        required: true
    }
});

const form = useForm({
    client_id: '',
    name: '',
    description: '',
    status: 'pending',
    start_date: '',
    end_date: '',
    tech_stack: '',
    budget: '',
    priority: 'medium',
});

const submit = () => {
    form.post(route('projects.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Create Project" />

    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">Create Project</h4>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-9 col-xl-8">
                <div class="card">
                    <div class="card-body">
                        <form @submit.prevent="submit">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Project Name <span class="text-danger">*</span></label>
                                    <input type="text" id="name" v-model="form.name" class="form-control" :class="{ 'is-invalid': form.errors.name }" required>
                                    <div class="invalid-feedback" v-if="form.errors.name">{{ form.errors.name }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="client_id" class="form-label">Client <span class="text-danger">*</span></label>
                                    <select id="client_id" v-model="form.client_id" class="form-select" :class="{ 'is-invalid': form.errors.client_id }" required>
                                        <option value="" disabled>Select Client</option>
                                        <option v-for="client in clients" :key="client.id" :value="client.id">
                                            {{ client.name }} ({{ client.company || 'Individual' }})
                                        </option>
                                    </select>
                                    <div class="invalid-feedback" v-if="form.errors.client_id">{{ form.errors.client_id }}</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="description" class="form-label">Project Description</label>
                                    <textarea id="description" v-model="form.description" class="form-control" :class="{ 'is-invalid': form.errors.description }" rows="4"></textarea>
                                    <div class="invalid-feedback" v-if="form.errors.description">{{ form.errors.description }}</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" id="start_date" v-model="form.start_date" class="form-control" :class="{ 'is-invalid': form.errors.start_date }">
                                    <div class="invalid-feedback" v-if="form.errors.start_date">{{ form.errors.start_date }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" id="end_date" v-model="form.end_date" class="form-control" :class="{ 'is-invalid': form.errors.end_date }">
                                    <div class="invalid-feedback" v-if="form.errors.end_date">{{ form.errors.end_date }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select id="status" v-model="form.status" class="form-select" :class="{ 'is-invalid': form.errors.status }" required>
                                        <option value="pending">Pending</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="on_hold">On Hold</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                    <div class="invalid-feedback" v-if="form.errors.status">{{ form.errors.status }}</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="budget" class="form-label">Budget</label>
                                    <input type="number" step="0.01" id="budget" v-model="form.budget" class="form-control" :class="{ 'is-invalid': form.errors.budget }" placeholder="0.00">
                                    <div class="invalid-feedback" v-if="form.errors.budget">{{ form.errors.budget }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                                    <select id="priority" v-model="form.priority" class="form-select" :class="{ 'is-invalid': form.errors.priority }" required>
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                    </select>
                                    <div class="invalid-feedback" v-if="form.errors.priority">{{ form.errors.priority }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label for="tech_stack" class="form-label">Tech Stack</label>
                                    <input type="text" id="tech_stack" v-model="form.tech_stack" class="form-control" :class="{ 'is-invalid': form.errors.tech_stack }" placeholder="e.g. Laravel, Vue, Tailwind">
                                    <div class="invalid-feedback" v-if="form.errors.tech_stack">{{ form.errors.tech_stack }}</div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <Link :href="route('projects.index')" class="btn btn-light me-2">Cancel</Link>
                                <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                    Create Project
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
