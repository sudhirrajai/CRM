<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

defineProps({
    clients: { type: Array, required: true },
    servers: { type: Array, required: true },
    currencies: { type: Array, required: true },
});

const form = useForm({
    client_id: '',
    server_id: '',
    domain: '',
    billing_cycle: 'monthly',
    price: 0,
    currency_id: '',
    status: 'active',
});

const submit = () => {
    form.post(route('hostings.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Allocate Hosting" />

    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">Allocate Hosting / VPS</h4>
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
                                    <label for="domain" class="form-label">Domain Name / Hostname <span class="text-danger">*</span></label>
                                    <input type="text" id="domain" v-model="form.domain" class="form-control" :class="{ 'is-invalid': form.errors.domain }" required>
                                    <div class="invalid-feedback" v-if="form.errors.domain">{{ form.errors.domain }}</div>
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
                                <div class="col-md-6">
                                    <label for="server_id" class="form-label">Server Node <span class="text-danger">*</span></label>
                                    <select id="server_id" v-model="form.server_id" class="form-select" :class="{ 'is-invalid': form.errors.server_id }" required>
                                        <option value="" disabled>Select Server</option>
                                        <option v-for="server in servers" :key="server.id" :value="server.id">
                                            {{ server.name }} ({{ server.ip_address }})
                                        </option>
                                    </select>
                                    <div class="invalid-feedback" v-if="form.errors.server_id">{{ form.errors.server_id }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select id="status" v-model="form.status" class="form-select" :class="{ 'is-invalid': form.errors.status }" required>
                                        <option value="active">Active</option>
                                        <option value="suspended">Suspended</option>
                                        <option value="terminated">Terminated</option>
                                    </select>
                                    <div class="invalid-feedback" v-if="form.errors.status">{{ form.errors.status }}</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="billing_cycle" class="form-label">Billing Cycle <span class="text-danger">*</span></label>
                                    <select id="billing_cycle" v-model="form.billing_cycle" class="form-select" :class="{ 'is-invalid': form.errors.billing_cycle }" required>
                                        <option value="monthly">Monthly</option>
                                        <option value="quarterly">Quarterly</option>
                                        <option value="semi_annually">Semi-Annually</option>
                                        <option value="annually">Annually</option>
                                    </select>
                                    <div class="invalid-feedback" v-if="form.errors.billing_cycle">{{ form.errors.billing_cycle }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label for="price" class="form-label">Price <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" id="price" v-model="form.price" class="form-control" :class="{ 'is-invalid': form.errors.price }" required>
                                    <div class="invalid-feedback" v-if="form.errors.price">{{ form.errors.price }}</div>
                                </div>
                                <div class="col-md-4">
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
                                <Link :href="route('hostings.index')" class="btn btn-light me-2">Cancel</Link>
                                <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                    Allocate Hosting
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
