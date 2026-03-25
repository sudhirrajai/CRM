<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    client: {
        type: Object,
        required: true
    },
    currencies: {
        type: Array,
        required: true
    }
});

const form = useForm({
    name: props.client.name,
    email: props.client.email,
    phone: props.client.phone || '',
    company: props.client.company || '',
    address: props.client.address || '',
    currency_id: props.client.currency_id,
    status: props.client.status,
});

const submit = () => {
    form.put(route('clients.update', props.client.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Edit Client" />

    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">Edit Client</h4>
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
                                    <label for="name" class="form-label">Contact Name <span class="text-danger">*</span></label>
                                    <input type="text" id="name" v-model="form.name" class="form-control" :class="{ 'is-invalid': form.errors.name }" required>
                                    <div class="invalid-feedback" v-if="form.errors.name">{{ form.errors.name }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="company" class="form-label">Company Name</label>
                                    <input type="text" id="company" v-model="form.company" class="form-control" :class="{ 'is-invalid': form.errors.company }">
                                    <div class="invalid-feedback" v-if="form.errors.company">{{ form.errors.company }}</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" id="email" v-model="form.email" class="form-control" :class="{ 'is-invalid': form.errors.email }" required>
                                    <div class="invalid-feedback" v-if="form.errors.email">{{ form.errors.email }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="text" id="phone" v-model="form.phone" class="form-control" :class="{ 'is-invalid': form.errors.phone }">
                                    <div class="invalid-feedback" v-if="form.errors.phone">{{ form.errors.phone }}</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="address" class="form-label">Billing Address</label>
                                    <textarea id="address" v-model="form.address" class="form-control" :class="{ 'is-invalid': form.errors.address }" rows="3"></textarea>
                                    <div class="invalid-feedback" v-if="form.errors.address">{{ form.errors.address }}</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="currency" class="form-label">Billing Currency <span class="text-danger">*</span></label>
                                    <select id="currency" v-model="form.currency_id" class="form-select" :class="{ 'is-invalid': form.errors.currency_id }" required>
                                        <option value="" disabled>Select Currency</option>
                                        <option v-for="currency in currencies" :key="currency.id" :value="currency.id">
                                            {{ currency.name }} ({{ currency.code }})
                                        </option>
                                    </select>
                                    <div class="invalid-feedback" v-if="form.errors.currency_id">{{ form.errors.currency_id }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="status" class="form-label">Account Status <span class="text-danger">*</span></label>
                                    <select id="status" v-model="form.status" class="form-select" :class="{ 'is-invalid': form.errors.status }" required>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="lead">Lead</option>
                                    </select>
                                    <div class="invalid-feedback" v-if="form.errors.status">{{ form.errors.status }}</div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <Link :href="route('clients.index')" class="btn btn-light me-2">Cancel</Link>
                                <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                    Update Client
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
