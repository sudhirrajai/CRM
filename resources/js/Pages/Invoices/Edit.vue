<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps({
    invoice: {
        type: Object,
        required: true
    },
    clients: { type: Array, required: true },
    projects: { type: Array, required: true },
    currencies: { type: Array, required: true },
});

const form = useForm({
    client_id: props.invoice.client_id,
    project_id: props.invoice.project_id || '',
    currency_id: props.invoice.currency_id,
    invoice_number: props.invoice.invoice_number,
    issue_date: props.invoice.issue_date_formatted,
    due_date: props.invoice.due_date_formatted,
    total_amount: props.invoice.total_amount,
    status: props.invoice.status,
    notes: props.invoice.notes || '',
    payment_mode: props.invoice.payment_mode || '',
    payment_reference: props.invoice.payment_reference || '',
    payment_note: props.invoice.payment_note || '',
    send_email: false,
    items: props.invoice.items && props.invoice.items.length > 0
        ? props.invoice.items.map(i => ({...i}))
        : [{ description: '', quantity: 1, unit_price: 0, total: 0 }],
});

const calculateTotal = () => {
    let tempTotal = 0;
    
    // Calculate from Items
    form.items.forEach(item => {
        item.total = (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0);
        tempTotal += item.total;
    });

    form.total_amount = tempTotal.toFixed(2);
};

watch(() => form.items, () => {
    calculateTotal();
}, { deep: true });

const addItem = () => {
    form.items.push({ description: '', quantity: 1, unit_price: 0, total: 0 });
};

const removeItem = (index) => {
    form.items.splice(index, 1);
};

const submit = () => {
    form.put(route('invoices.update', props.invoice.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Edit Invoice" />

    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">Edit Invoice</h4>
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
                                    <label for="invoice_number" class="form-label">Invoice Number <span class="text-danger">*</span></label>
                                    <input type="text" id="invoice_number" v-model="form.invoice_number" class="form-control" :class="{ 'is-invalid': form.errors.invoice_number }" required>
                                    <div class="invalid-feedback" v-if="form.errors.invoice_number">{{ form.errors.invoice_number }}</div>
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
                                    <label for="project_id" class="form-label">Related Project</label>
                                    <select id="project_id" v-model="form.project_id" class="form-select" :class="{ 'is-invalid': form.errors.project_id }">
                                        <option value="">None (General Billing)</option>
                                        <option v-for="project in projects" :key="project.id" :value="project.id">
                                            {{ project.name }}
                                        </option>
                                    </select>
                                    <div class="invalid-feedback" v-if="form.errors.project_id">{{ form.errors.project_id }}</div>
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

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="issue_date" class="form-label">Issue Date <span class="text-danger">*</span></label>
                                    <input type="date" id="issue_date" v-model="form.issue_date" class="form-control" :class="{ 'is-invalid': form.errors.issue_date }" required>
                                    <div class="invalid-feedback" v-if="form.errors.issue_date">{{ form.errors.issue_date }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="due_date" class="form-label">Due Date <span class="text-danger">*</span></label>
                                    <input type="date" id="due_date" v-model="form.due_date" class="form-control" :class="{ 'is-invalid': form.errors.due_date }" required>
                                    <div class="invalid-feedback" v-if="form.errors.due_date">{{ form.errors.due_date }}</div>
                                </div>
                            </div>

                            <div class="card border border-light mt-4 mb-4">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Invoice Items</h5>
                                    <button type="button" class="btn btn-sm btn-outline-primary" @click="addItem">
                                        <i class="ti ti-plus me-1"></i> Add Service
                                    </button>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-borderless mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Description</th>
                                                    <th style="width: 15%">Quantity</th>
                                                    <th style="width: 20%">Unit Price</th>
                                                    <th style="width: 20%">Amount</th>
                                                    <th style="width: 5%"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(item, index) in form.items" :key="index">
                                                    <td>
                                                        <input type="text" v-model="item.description" class="form-control" placeholder="e.g. Hosting Services" list="service-options" required>
                                                        <div class="invalid-feedback" v-if="form.errors[`items.${index}.description`]">{{ form.errors[`items.${index}.description`] }}</div>
                                                    </td>
                                                    <td>
                                                        <input type="number" v-model="item.quantity" class="form-control" min="1" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" v-model="item.unit_price" class="form-control" required>
                                                    </td>
                                                    <td>
                                                        <input type="text" :value="Number(item.total || 0).toFixed(2)" class="form-control bg-light" readonly>
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <button type="button" class="btn btn-sm btn-link text-danger p-0" @click="removeItem(index)" v-if="form.items.length > 1">
                                                            <i class="ti ti-trash fs-5"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <datalist id="service-options">
                                            <option value="Project Creation"></option>
                                            <option value="Hosting Services"></option>
                                            <option value="Domain Registration"></option>
                                            <option value="Maintenance & Support"></option>
                                            <option value="Consulting"></option>
                                        </datalist>
                                    </div>
                                </div>
                            </div>

                            <div class="row align-items-center mb-3">
                                <div class="col-md-6 offset-md-6">
                                    <div class="d-flex justify-content-between align-items-center p-2 mb-3 bg-light rounded">
                                        <h5 class="m-0">Total Amount</h5>
                                        <h4 class="m-0 text-primary">
                                            {{ currencies.find(c => c.id === form.currency_id)?.symbol || '$' }}{{ form.total_amount }}
                                        </h4>
                                    </div>
                                    <div class="d-none">
                                        <input type="number" step="0.01" id="total_amount" v-model="form.total_amount" class="form-control" :class="{ 'is-invalid': form.errors.total_amount }" required readonly>
                                    </div>
                                    <div class="invalid-feedback d-block" v-if="form.errors.total_amount">{{ form.errors.total_amount }}</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select id="status" v-model="form.status" class="form-select" :class="{ 'is-invalid': form.errors.status }" required>
                                        <option value="draft">Draft</option>
                                        <option value="sent">Sent</option>
                                        <option value="paid">Paid</option>
                                        <option value="overdue">Overdue</option>
                                    </select>
                                    <div class="invalid-feedback" v-if="form.errors.status">{{ form.errors.status }}</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea id="notes" v-model="form.notes" class="form-control" :class="{ 'is-invalid': form.errors.notes }" rows="3"></textarea>
                                    <div class="invalid-feedback" v-if="form.errors.notes">{{ form.errors.notes }}</div>
                                </div>
                            </div>

                            <div v-if="form.status === 'paid'" class="card border border-light mt-4 mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Payment Details</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="payment_mode" class="form-label">Payment Mode</label>
                                            <select id="payment_mode" v-model="form.payment_mode" class="form-select" :class="{ 'is-invalid': form.errors.payment_mode }">
                                                <option value="">Select Mode</option>
                                                <option value="bank_transfer">Bank Transfer</option>
                                                <option value="credit_card">Credit Card</option>
                                                <option value="paypal">PayPal</option>
                                                <option value="neft_rtgs">NEFT / RTGS</option>
                                                <option value="upi">UPI</option>
                                                <option value="cash">Cash</option>
                                                <option value="other">Other</option>
                                            </select>
                                            <div class="invalid-feedback" v-if="form.errors.payment_mode">{{ form.errors.payment_mode }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="payment_reference" class="form-label">Payment Reference</label>
                                            <input type="text" id="payment_reference" v-model="form.payment_reference" class="form-control" :class="{ 'is-invalid': form.errors.payment_reference }" placeholder="e.g. Transaction ID">
                                            <div class="invalid-feedback" v-if="form.errors.payment_reference">{{ form.errors.payment_reference }}</div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <label for="payment_note" class="form-label">Payment Note</label>
                                            <textarea id="payment_note" v-model="form.payment_note" class="form-control" :class="{ 'is-invalid': form.errors.payment_note }" rows="2" placeholder="Optional details..."></textarea>
                                            <div class="invalid-feedback" v-if="form.errors.payment_note">{{ form.errors.payment_note }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="form-check">
                                        <input type="checkbox" id="send_email" v-model="form.send_email" class="form-check-input">
                                        <label for="send_email" class="form-check-label user-select-none">Send copy to client via email</label>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <Link :href="route('invoices.index')" class="btn btn-light me-2">Cancel</Link>
                                <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                    Update Invoice
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
