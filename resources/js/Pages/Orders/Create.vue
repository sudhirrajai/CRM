<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    clients: Array
});

const form = useForm({
    client_id: '',
    order_number: 'ORD-' + Math.floor(Math.random() * 1000000),
    total_amount: '',
    status: 'pending'
});

const submit = () => {
    form.post(route('orders.store'));
};
</script>

<template>
    <Head title="Create Order" />

    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between p-3">
                    <h4 class="mb-0">Placement New Order</h4>
                    <Link :href="route('orders.index')" class="btn btn-outline-secondary btn-sm">
                        <i class="ti ti-arrow-left me-1"></i> Back
                    </Link>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mx-auto">
                <div class="card">
                    <div class="card-body">
                        <form @submit.prevent="submit">
                            <div class="mb-3">
                                <label class="form-label">Order Reference</label>
                                <input type="text" class="form-control bg-light" v-model="form.order_number" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Select Client</label>
                                <select class="form-select" v-model="form.client_id" required>
                                    <option value="" disabled>Choose Client</option>
                                    <option v-for="client in clients" :key="client.id" :value="client.id">
                                        {{ client.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Total Amount ($)</label>
                                <input type="number" class="form-control" v-model="form.total_amount" required step="0.01" placeholder="0.00">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Order Status</label>
                                <select class="form-select" v-model="form.status">
                                    <option value="pending">Pending</option>
                                    <option value="processing">Processing</option>
                                    <option value="shipped">Shipped</option>
                                    <option value="delivered">Delivered</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-primary w-100" :disabled="form.processing">Place Order</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
