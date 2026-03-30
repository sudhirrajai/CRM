<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    project: {
        type: Object,
        required: true
    },
    clients: {
        type: Array,
        required: true
    }
});

const form = useForm({
    client_id: props.project.client_id,
    name: props.project.name,
    description: props.project.description || '',
    status: props.project.status,
    start_date: props.project.start_date || '',
    end_date: props.project.end_date || '',
    tech_stack: props.project.tech_stack || '',
    budget: props.project.budget || '',
    priority: props.project.priority || 'medium',
    milestones: props.project.milestones || [],
});

const addMilestone = () => {
    form.milestones.push({
        name: '',
        start_date: '',
        end_date: '',
        hours: '',
        description: '',
    });
};

const removeMilestone = (index) => {
    form.milestones.splice(index, 1);
};

const submit = () => {
    form.put(route('projects.update', props.project.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Edit Project" />

    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">Edit Project</h4>
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

                            <div class="row mb-3 border-bottom pb-4">
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

                            <!-- Milestones Section -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="mb-0">Project Milestones</h5>
                                        <button type="button" @click="addMilestone" class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-plus me-1"></i> Add Milestone
                                        </button>
                                    </div>
                                    
                                    <div v-if="form.milestones.length === 0" class="text-center py-4 border rounded bg-light mb-3">
                                        <p class="text-muted mb-0">No milestones added yet. Click "Add Milestone" to start.</p>
                                    </div>

                                    <div v-for="(milestone, index) in form.milestones" :key="index" class="card border mb-3 shadow-none">
                                        <div class="card-body bg-light-subtle">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="card-title mb-0 text-primary">Milestone #{{ index + 1 }}</h6>
                                                <button type="button" @click="removeMilestone(index)" class="btn btn-sm text-danger border-0">
                                                    <i class="ti ti-trash fs-4"></i>
                                                </button>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-md-8">
                                                    <label class="form-label small fw-bold">Milestone Name *</label>
                                                    <input type="text" v-model="milestone.name" class="form-control form-control-sm" :class="{ 'is-invalid': form.errors[`milestones.${index}.name`] }" required>
                                                    <div class="invalid-feedback" v-if="form.errors[`milestones.${index}.name`]">{{ form.errors[`milestones.${index}.name`] }}</div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-bold">Hours (Estimated)</label>
                                                    <input type="number" step="0.5" v-model="milestone.hours" class="form-control form-control-sm" :class="{ 'is-invalid': form.errors[`milestones.${index}.hours`] }">
                                                    <div class="invalid-feedback" v-if="form.errors[`milestones.${index}.hours`]">{{ form.errors[`milestones.${index}.hours`] }}</div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold">Start Date *</label>
                                                    <input type="date" v-model="milestone.start_date" class="form-control form-control-sm" :class="{ 'is-invalid': form.errors[`milestones.${index}.start_date`] }" required>
                                                    <div class="invalid-feedback" v-if="form.errors[`milestones.${index}.start_date`]">{{ form.errors[`milestones.${index}.start_date`] }}</div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold">End Date *</label>
                                                    <input type="date" v-model="milestone.end_date" class="form-control form-control-sm" :class="{ 'is-invalid': form.errors[`milestones.${index}.end_date`] }" required>
                                                    <div class="invalid-feedback" v-if="form.errors[`milestones.${index}.end_date`]">{{ form.errors[`milestones.${index}.end_date`] }}</div>
                                                </div>
                                                <div class="col-md-12">
                                                    <label class="form-label small fw-bold">Description</label>
                                                    <textarea v-model="milestone.description" class="form-control form-control-sm" rows="2" :class="{ 'is-invalid': form.errors[`milestones.${index}.description`] }"></textarea>
                                                    <div class="invalid-feedback" v-if="form.errors[`milestones.${index}.description`]">{{ form.errors[`milestones.${index}.description`] }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                                <Link :href="route('projects.index')" class="btn btn-light me-2">Cancel</Link>
                                <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                    Update Project & Milestones
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
