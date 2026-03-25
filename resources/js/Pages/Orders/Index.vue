<script setup>
import CRMAppLayout from '@/Layouts/CRMAppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    orders: Array
});
</script>

<template>
    <Head title="Orders" />

    <CRMAppLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between p-3">
                    <h4 class="mb-0">Order Management</h4>
                    <Link :href="route('orders.create')" class="btn btn-primary">
                        <i class="ti ti-plus me-1"></i> New Order
                    </Link>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between border-bottom border-dashed">
                        <h4 class="header-title">All Orders</h4>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-custom table-centered table-nowrap table-hover mb-0">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th>Order #</th>
                                        <th>Client</th>
                                        <th>Total Amount</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th class="text-end pe-3">Action</th>
                                    </tr>
                                </thead>
                            <tbody>
                                <tr v-for="order in orders" :key="order.id">
                                    <td><span class="text-dark fw-medium">{{ order.order_number }}</span></td>
                                    <td>{{ order.client.name }}</td>
                                    <td>${{ order.total_amount.toLocaleString() }}</td>
                                    <td>
                                        <span class="badge py-1 px-2" :class="{
                                            'badge-soft-success': order.status === 'delivered',
                                            'badge-soft-primary': order.status === 'processing',
                                            'badge-soft-warning': order.status === 'pending',
                                            'badge-soft-danger': order.status === 'cancelled'
                                        }">
                                            {{ order.status }}
                                        </span>
                                    </td>
                                    <td>{{ new Date(order.created_at).toLocaleDateString() }}</td>
                                    <td class="text-end pe-3">
                                        <div class="hstack gap-2 justify-content-end">
                                            <Link :href="route('orders.edit', order.id)" class="btn btn-soft-primary btn-sm btn-icon rounded-circle">
                                                <i class="ti ti-edit"></i>
                                            </Link>
                                            <button class="btn btn-soft-danger btn-sm btn-icon rounded-circle">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="orders.length === 0">
                                    <td colspan="6" class="text-center py-4 text-muted">No orders found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div> <!-- Card body end -->
                </div>
            </div>
        </div>
    </CRMAppLayout>
</template>
