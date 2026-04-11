<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    invoice: {
        type: Object,
        required: true
    }
});

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};

const formatCurrency = (amount, currency) => {
    if (amount === null || amount === undefined) return '-';
    const symbol = currency?.symbol || '$';
    const pos = currency?.symbol_position || 'prefix';
    return pos === 'prefix' ? `${symbol}${amount}` : `${amount}${symbol}`;
};

const sendEmail = () => {
    if (confirm('Are you sure you want to send this invoice to all recipients?')) {
        router.post(route('invoices.send-email', props.invoice.id));
    }
};

const sendSuspension = () => {
    if (confirm('Are you sure you want to send a suspension notification?')) {
        router.post(route('invoices.send-suspension', props.invoice.id));
    }
};

const isOverdue = (dueDate) => {
    if (!dueDate) return false;
    return new Date(dueDate) < new Date() && props.invoice.status !== 'paid';
};
</script>

<template>
    <Head :title="'Invoice #' + invoice.invoice_number" />

    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex justify-content-between align-items-center py-3">
                    <h4 class="page-title mb-0">Invoice: #{{ invoice.invoice_number }}</h4>
                    <div class="page-title-right d-flex gap-2">
                        <button @click="sendEmail" class="btn btn-primary">
                            <i class="ti ti-mail me-1"></i> Send Email
                        </button>
                        <a :href="route('invoices.pdf', invoice.id)" class="btn btn-info">
                            <i class="ti ti-download me-1"></i> Download PDF
                        </a>
                        <Link :href="route('invoices.edit', invoice.id)" class="btn btn-secondary" v-if="!$page.props.auth.roles.includes('client')">
                            <i class="ti ti-edit me-1"></i> Edit
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-4">
                            <div>
                                <h4 class="mb-1">Invoice Info</h4>
                                <p class="text-muted mb-0">Issued on: {{ formatDate(invoice.issue_date) }}</p>
                                <p class="text-muted mb-0">Due on: {{ formatDate(invoice.due_date) }}</p>
                            </div>
                            <div class="text-end">
                                <span class="badge px-3 py-2 fs-14" :class="invoice.status === 'paid' ? 'bg-success' : (isOverdue(invoice.due_date) ? 'bg-danger' : 'bg-warning')">
                                    {{ invoice.status.toUpperCase() }}
                                </span>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-centered table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Description</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Rate</th>
                                        <th class="text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in invoice.items" :key="item.id">
                                        <td>
                                            <h5 class="font-14 my-1 fw-bold">{{ item.description }}</h5>
                                        </td>
                                        <td class="text-center">{{ item.quantity }}</td>
                                        <td class="text-end">{{ formatCurrency(item.unit_price, invoice.currency) }}</td>
                                        <td class="text-end fw-bold">{{ formatCurrency(item.total_price, invoice.currency) }}</td>
                                    </tr>
                                    <tr v-if="!invoice.items || invoice.items.length === 0">
                                        <td colspan="4" class="text-center py-3 text-muted">No items found for this invoice.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="row justify-content-end mt-4">
                            <div class="col-md-4">
                                <div class="table-responsive">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tbody>
                                            <tr>
                                                <th class="text-end">Subtotal :</th>
                                                <td class="text-end">{{ formatCurrency(invoice.sub_total, invoice.currency) }}</td>
                                            </tr>
                                            <tr v-if="invoice.tax > 0">
                                                <th class="text-end">Tax :</th>
                                                <td class="text-end">{{ formatCurrency(invoice.tax, invoice.currency) }}</td>
                                            </tr>
                                            <tr class="border-top">
                                                <th class="text-end pb-0 fs-16">Total :</th>
                                                <td class="text-end pb-0 fs-16 fw-bold">{{ formatCurrency(invoice.total_amount, invoice.currency) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 border-top pt-3" v-if="invoice.notes">
                            <h5 class="font-14">Notes:</h5>
                            <p class="text-muted">{{ invoice.notes }}</p>
                        </div>

                        <div class="mt-4 border-top pt-3 bg-light p-3 rounded" v-if="invoice.status === 'paid' && (invoice.payment_mode || invoice.payment_reference || invoice.payment_note)">
                            <h5 class="font-14 mb-2"><i class="ti ti-receipt-2 me-1"></i> Payment Details</h5>
                            <ul class="list-unstyled text-muted mb-0 small">
                                <li v-if="invoice.payment_mode" class="mb-1"><strong>Mode:</strong> <span class="text-capitalize">{{ invoice.payment_mode.replace('_', ' ') }}</span></li>
                                <li v-if="invoice.payment_reference" class="mb-1"><strong>Reference:</strong> {{ invoice.payment_reference }}</li>
                                <li v-if="invoice.payment_note" class="mt-1"><strong>Note:</strong> {{ invoice.payment_note }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Client Info -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title mb-3">Client Details</h4>
                        <div class="d-flex align-items-center mb-3" v-if="invoice.client">
                            <div class="avatar-md me-3">
                                <span class="avatar-title rounded-circle bg-primary-subtle text-primary fw-bold fs-20">
                                    {{ invoice.client.name.charAt(0) }}
                                </span>
                            </div>
                            <div>
                                <h5 class="mb-1">{{ invoice.client.name }}</h5>
                                <p class="text-muted mb-0">{{ invoice.client.company || 'Individual' }}</p>
                            </div>
                        </div>
                        <ul class="list-unstyled mb-0 vstack gap-2 text-muted">
                            <li><i class="ti ti-mail me-2"></i> {{ invoice.client?.email }}</li>
                            <li><i class="ti ti-phone me-2"></i> {{ invoice.client?.phone || 'N/A' }}</li>
                            <li><i class="ti ti-map-pin me-2"></i> {{ invoice.client?.address || 'N/A' }}</li>
                        </ul>
                    </div>
                </div>

                <!-- Project Info -->
                <div class="card" v-if="invoice.project">
                    <div class="card-body">
                        <h4 class="header-title mb-3">Project Reference</h4>
                        <Link :href="route('projects.show', invoice.project.id)" class="h5 text-primary mb-2 d-block">
                            {{ invoice.project.name }}
                        </Link>
                        <p class="text-muted small mb-0">{{ invoice.project.description?.substring(0, 100) }}...</p>
                        
                        <!-- Recipients Info -->
                        <div class="mt-3 pt-3 border-top">
                            <h6 class="mb-2">Additional Recipients:</h6>
                            <ul class="list-unstyled small mb-0 hstack gap-1 flex-wrap">
                                <li v-for="recipient in (invoice.project.invoice_recipients || [])" :key="recipient.id" class="badge bg-light text-dark border">
                                    {{ recipient.name }}
                                </li>
                                <li v-if="!invoice.project.invoice_recipients || invoice.project.invoice_recipients.length === 0" class="text-muted italic">
                                    None
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="card border-danger border" v-if="isOverdue(invoice.due_date)">
                    <div class="card-body">
                        <h4 class="header-title text-danger mb-3">Overdue Actions</h4>
                        <p class="small text-muted">This invoice is past its due date. You can send a final warning or suspension notice.</p>
                        <button @click="sendSuspension" class="btn btn-outline-danger btn-sm w-100">
                            <i class="ti ti-alert-triangle me-1"></i> Send Suspension Notice
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
