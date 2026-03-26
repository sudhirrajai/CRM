<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    invoices: {
        type: Array,
        required: true
    }
});

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};

const deleteRecord = (id, invoiceNumber) => {
    if (confirm(`Are you sure you want to delete Invoice #${invoiceNumber}? This action cannot be undone.`)) {
        router.delete(route('invoices.destroy', id));
    }
};

const sendSuspension = (id) => {
    if (confirm('Are you sure you want to send a suspension notification for this invoice?')) {
        router.post(route('invoices.send-suspension', id));
    }
};

const isOverdue = (dueDate) => {
    if (!dueDate) return false;
    return new Date(dueDate) < new Date();
};
</script>

<template>
    <Head title="Invoices" />

    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">Billing & Invoices</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="header-title">All Invoices</h4>
                            <Link :href="route('invoices.create')" class="btn btn-primary" v-if="$page.props.auth.roles.includes('admin') || $page.props.auth.roles.includes('staff')">
                                Generate Invoice
                            </Link>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-centered mb-0 align-middle table-hover table-nowrap">
                                <thead class="table-light">
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Client</th>
                                        <th>Amount</th>
                                        <th>Issue Date</th>
                                        <th>Status</th>
                                        <th style="width: 150px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="invoice in invoices" :key="invoice.id">
                                        <td>#{{ invoice.invoice_number }}</td>
                                        <td>{{ invoice.client?.name || '-' }}</td>
                                        <td>
                                            {{ invoice.currency?.symbol_position === 'prefix' ? invoice.currency?.symbol : '' }}
                                            {{ invoice.total_amount }}
                                            {{ invoice.currency?.symbol_position === 'suffix' ? invoice.currency?.symbol : '' }}
                                        </td>
                                        <td>{{ formatDate(invoice.issue_date) }}</td>
                                        <td>
                                            <span class="badge" :class="invoice.status === 'paid' ? 'bg-success' : (invoice.status === 'overdue' ? 'bg-danger' : 'bg-warning')">
                                                {{ invoice.status }}
                                            </span>
                                        </td>
                                        <td>
                                            <a :href="route('invoices.view-pdf', invoice.id)" class="action-icon text-success" target="_blank" title="View PDF"> <i class="ti ti-file-text"></i></a>
                                            <a :href="route('invoices.pdf', invoice.id)" class="action-icon text-info" title="Download PDF"> <i class="ti ti-download"></i></a>
                                            <Link :href="route('invoices.edit', invoice.id)" class="action-icon" title="Edit" v-if="!$page.props.auth.roles.includes('client')"> <i class="ti ti-edit"></i></Link>
                                            
                                            <!-- Suspension Notification Button -->
                                            <button @click="sendSuspension(invoice.id)" 
                                                    class="action-icon text-warning" 
                                                    title="Send Suspension Notification"
                                                    v-if="($page.props.auth.roles.includes('admin') || $page.props.auth.roles.includes('staff')) && isOverdue(invoice.due_date) && invoice.status !== 'paid'">
                                                <i class="ti ti-alert-triangle"></i>
                                            </button>

                                            <button @click="deleteRecord(invoice.id, invoice.invoice_number)" class="action-icon text-danger" title="Delete" v-if="$page.props.auth.roles.includes('admin')"> <i class="ti ti-trash"></i></button>
                                        </td>
                                    </tr>
                                    <tr v-if="invoices.length === 0">
                                        <td colspan="6" class="text-center py-4">No invoices found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
