<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    server: {
        type: Object,
        required: true
    },
    currencies: {
        type: Array,
        required: true
    }
});

const form = useForm({
    name: props.server.name,
    provider: props.server.provider || '',
    ip_address: props.server.ip_address,
    monthly_cost: props.server.monthly_cost || '',
    currency_id: props.server.currency_id,
    status: props.server.status,
});

const submit = () => {
    form.put(route('servers.update', props.server.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Edit Server" />

    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">Edit Server</h4>
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
                                    <label for="name" class="form-label">Server Name <span class="text-danger">*</span></label>
                                    <input type="text" id="name" v-model="form.name" class="form-control" :class="{ 'is-invalid': form.errors.name }" required>
                                    <div class="invalid-feedback" v-if="form.errors.name">{{ form.errors.name }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="provider" class="form-label">Provider / Datacenter</label>
                                    <input type="text" id="provider" v-model="form.provider" class="form-control" :class="{ 'is-invalid': form.errors.provider }">
                                    <div class="invalid-feedback" v-if="form.errors.provider">{{ form.errors.provider }}</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="ip_address" class="form-label">IP Address <span class="text-danger">*</span></label>
                                    <input type="text" id="ip_address" v-model="form.ip_address" class="form-control" :class="{ 'is-invalid': form.errors.ip_address }" required>
                                    <div class="invalid-feedback" v-if="form.errors.ip_address">{{ form.errors.ip_address }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select id="status" v-model="form.status" class="form-select" :class="{ 'is-invalid': form.errors.status }" required>
                                        <option value="active">Active</option>
                                        <option value="offline">Offline</option>
                                        <option value="maintenance">Maintenance</option>
                                    </select>
                                    <div class="invalid-feedback" v-if="form.errors.status">{{ form.errors.status }}</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="monthly_cost" class="form-label">Monthly Cost</label>
                                    <input type="number" step="0.01" id="monthly_cost" v-model="form.monthly_cost" class="form-control" :class="{ 'is-invalid': form.errors.monthly_cost }">
                                    <div class="invalid-feedback" v-if="form.errors.monthly_cost">{{ form.errors.monthly_cost }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="currency_id" class="form-label">Currency <span class="text-danger">*</span></label>
                                    <select id="currency_id" v-model="form.currency_id" class="form-select" :class="{ 'is-invalid': form.errors.currency_id }" required>
                                        <option value="" disabled>Select Currency</option>
                                        <option v-for="currency in currencies" :key="currency.id" :value="currency.id">
                                            {{ currency.name }} ({{ currency.code }})
                                        </option>
                                    </select>
                                    <div class="invalid-feedback" v-if="form.errors.currency_id">{{ form.errors.currency_id }}</div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <Link :href="route('servers.index')" class="btn btn-light me-2">Cancel</Link>
                                <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                    Update Server
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
