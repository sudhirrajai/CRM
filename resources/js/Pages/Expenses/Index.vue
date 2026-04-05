<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import Layout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    expenses: Array,
    categories: Array
});

const showModal = ref(false);
const editingExpense = ref(null);
const showInvoicePreview = ref(false);
const previewInvoice = ref({ url: '', type: '', name: '' });
const showDetailsModal = ref(false);
const detailsExpense = ref(null);

const form = useForm({
    category_id: '',
    name: '',
    amount: '',
    payment_reference: '',
    payment_mode: '',
    payment_mode_details: {
        upi_id: '',
        account_number: '',
        ifsc_code: '',
        bank_name: '',
        account_holder: '',
    },
    date: new Date().toISOString().split('T')[0],
    is_recurring: false,
    recurring_frequency: '',
    next_due_date: '',
    notes: '',
    invoice_file: null,
});

const openModal = (expense = null) => {
    if (expense) {
        editingExpense.value = expense;
        form.category_id = expense.category_id;
        form.name = expense.name;
        form.amount = expense.amount;
        form.payment_reference = expense.payment_reference || '';
        form.payment_mode = expense.payment_mode || '';
        form.payment_mode_details = {
            upi_id: expense.payment_mode_details?.upi_id || '',
            account_number: expense.payment_mode_details?.account_number || '',
            ifsc_code: expense.payment_mode_details?.ifsc_code || '',
            bank_name: expense.payment_mode_details?.bank_name || '',
            account_holder: expense.payment_mode_details?.account_holder || '',
        };
        form.date = expense.date;
        form.is_recurring = !!expense.is_recurring;
        form.recurring_frequency = expense.recurring_frequency || '';
        form.next_due_date = expense.next_due_date || '';
        form.notes = expense.notes;
        form.invoice_file = null;
    } else {
        editingExpense.value = null;
        form.reset();
        form.date = new Date().toISOString().split('T')[0];
        form.payment_mode_details = {
            upi_id: '',
            account_number: '',
            ifsc_code: '',
            bank_name: '',
            account_holder: '',
        };
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const submit = () => {
    // Clean payment_mode_details if no payment mode selected
    const formData = { ...form.data() };
    if (!formData.payment_mode) {
        formData.payment_mode_details = null;
    }

    if (editingExpense.value) {
        form.transform((data) => ({
            ...data,
            _method: 'PUT',
            payment_mode_details: formData.payment_mode ? data.payment_mode_details : null,
        })).post(route('expenses.update', editingExpense.value.id), {
            forceFormData: true,
            onSuccess: () => closeModal()
        });
    } else {
        form.transform((data) => ({
            ...data,
            payment_mode_details: formData.payment_mode ? data.payment_mode_details : null,
        })).post(route('expenses.store'), {
            onSuccess: () => closeModal()
        });
    }
};

const deleteExpense = (id) => {
    if (confirm('Are you sure you want to delete this expense?')) {
        form.delete(route('expenses.destroy', id));
    }
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(amount);
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('en-IN', {
        year: 'numeric',
        month: 'short',
        day: '2-digit'
    }).format(date);
};

const openInvoicePreview = (expense) => {
    if (!expense.invoice_file_path) return;
    const url = expense.invoice_file_path;
    const ext = url.split('.').pop().toLowerCase();
    previewInvoice.value = {
        url: url,
        type: ext === 'pdf' ? 'pdf' : 'image',
        name: expense.name
    };
    showInvoicePreview.value = true;
};

const closeInvoicePreview = () => {
    showInvoicePreview.value = false;
};

const openDetailsModal = (expense) => {
    detailsExpense.value = expense;
    showDetailsModal.value = true;
};

const closeDetailsModal = () => {
    showDetailsModal.value = false;
    detailsExpense.value = null;
};

const paymentModeLabel = (mode) => {
    const labels = {
        'upi': 'UPI',
        'bank_transfer': 'Bank Transfer',
        'neft_rtgs': 'NEFT / RTGS',
    };
    return labels[mode] || mode || '-';
};

const paymentModeBadgeClass = (mode) => {
    const classes = {
        'upi': 'bg-soft-success text-success',
        'bank_transfer': 'bg-soft-primary text-primary',
        'neft_rtgs': 'bg-soft-warning text-warning',
    };
    return classes[mode] || 'bg-soft-secondary text-secondary';
};
</script>

<template>
    <Head title="Expenses" />

    <Layout>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="header-title">Expenses</h4>
                            <button @click="openModal()" class="btn btn-primary">
                                <i class="ti ti-plus me-1"></i> Add Expense
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-centered mb-0 text-nowrap">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Expense Name</th>
                                        <th>Category</th>
                                        <th>Amount</th>
                                        <th>Payment Mode</th>
                                        <th>Payment Ref</th>
                                        <th>Invoice</th>
                                        <th>Recurring</th>
                                        <th>Next Due Date</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="expense in expenses" :key="expense.id">
                                        <td>{{ formatDate(expense.date) }}</td>
                                        <td><strong>{{ expense.name }}</strong></td>
                                        <td>
                                            <span class="badge bg-soft-info text-info">{{ expense.category.name }}</span>
                                        </td>
                                        <td class="fw-bold">{{ formatCurrency(expense.amount) }}</td>
                                        <td>
                                            <span v-if="expense.payment_mode" class="badge" :class="paymentModeBadgeClass(expense.payment_mode)">
                                                {{ paymentModeLabel(expense.payment_mode) }}
                                            </span>
                                            <span v-else class="text-muted small">-</span>
                                        </td>
                                        <td>
                                            <span v-if="expense.payment_reference" class="text-dark">{{ expense.payment_reference }}</span>
                                            <span v-else class="text-muted small">-</span>
                                        </td>
                                        <td>
                                            <button v-if="expense.invoice_file_path" @click="openInvoicePreview(expense)" class="btn btn-sm btn-soft-primary px-2 py-1 shadow-none">
                                                <i class="ti ti-file-text me-1"></i> View
                                            </button>
                                            <span v-else class="text-muted small">None</span>
                                        </td>
                                        <td>
                                            <div v-if="expense.is_recurring">
                                                <span class="badge bg-soft-success text-success">Yes</span>
                                                <div class="text-muted small text-capitalize">{{ expense.recurring_frequency }}</div>
                                            </div>
                                            <span v-else class="badge bg-soft-secondary text-secondary">No</span>
                                        </td>
                                        <td>
                                            <span v-if="expense.is_recurring && expense.next_due_date" class="fw-medium text-primary">
                                                {{ formatDate(expense.next_due_date) }}
                                            </span>
                                            <span v-else class="text-muted opacity-50">-</span>
                                        </td>
                                        <td class="text-end">
                                            <button @click="openDetailsModal(expense)" class="btn btn-sm btn-light me-1 shadow-sm" title="View Details">
                                                <i class="ti ti-eye"></i>
                                            </button>
                                            <button @click="openModal(expense)" class="btn btn-sm btn-light me-1 shadow-sm" title="Edit">
                                                <i class="ti ti-edit"></i>
                                            </button>
                                            <button @click="deleteExpense(expense.id)" class="btn btn-sm btn-light text-danger shadow-sm" title="Delete">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="expenses.length === 0">
                                        <td colspan="10" class="text-center py-4 text-muted">No expenses found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expense Create/Edit Modal -->
        <div v-if="showModal" class="modal fade show d-block" style="background: rgba(0,0,0,0.5)">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ editingExpense ? 'Edit' : 'Add' }} Expense</h5>
                        <button type="button" class="btn-close" @click="closeModal"></button>
                    </div>
                    <form @submit.prevent="submit">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date</label>
                                    <input v-model="form.date" type="date" class="form-control" required>
                                    <div v-if="form.errors.date" class="text-danger mt-1 small">{{ form.errors.date }}</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Expense Name</label>
                                    <input v-model="form.name" type="text" class="form-control" placeholder="e.g. Office Rent" required>
                                    <div v-if="form.errors.name" class="text-danger mt-1 small">{{ form.errors.name }}</div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Category</label>
                                    <select v-model="form.category_id" class="form-select" required>
                                        <option value="" disabled>Select Category</option>
                                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                    </select>
                                    <div v-if="form.errors.category_id" class="text-danger mt-1 small">{{ form.errors.category_id }}</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>
                                        <input v-model="form.amount" type="number" step="0.01" class="form-control" required>
                                    </div>
                                    <div v-if="form.errors.amount" class="text-danger mt-1 small">{{ form.errors.amount }}</div>
                                </div>
                            </div>

                            <!-- Payment Mode Section -->
                            <div class="card bg-light border-0 mb-3">
                                <div class="card-body py-3">
                                    <h6 class="card-title mb-3"><i class="ti ti-credit-card me-1"></i> Payment Information</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Payment Mode</label>
                                            <select v-model="form.payment_mode" class="form-select">
                                                <option value="">Select Payment Mode</option>
                                                <option value="upi">UPI</option>
                                                <option value="bank_transfer">Bank Transfer</option>
                                                <option value="neft_rtgs">NEFT / RTGS</option>
                                            </select>
                                            <div v-if="form.errors.payment_mode" class="text-danger mt-1 small">{{ form.errors.payment_mode }}</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Payment Reference / Transaction ID</label>
                                            <input v-model="form.payment_reference" type="text" class="form-control" placeholder="e.g. TXN123456789">
                                            <div v-if="form.errors.payment_reference" class="text-danger mt-1 small">{{ form.errors.payment_reference }}</div>
                                        </div>
                                    </div>

                                    <!-- UPI Details -->
                                    <div v-if="form.payment_mode === 'upi'" class="row animate-slide-in">
                                        <div class="col-md-12 mb-2">
                                            <label class="form-label small fw-bold">UPI ID (Paid To)</label>
                                            <input v-model="form.payment_mode_details.upi_id" type="text" class="form-control form-control-sm" placeholder="e.g. merchant@upi or 9876543210@paytm">
                                        </div>
                                    </div>

                                    <!-- Bank Transfer / NEFT-RTGS Details -->
                                    <div v-if="form.payment_mode === 'bank_transfer' || form.payment_mode === 'neft_rtgs'" class="row animate-slide-in">
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label small fw-bold">Account Holder Name</label>
                                            <input v-model="form.payment_mode_details.account_holder" type="text" class="form-control form-control-sm" placeholder="e.g. John Doe">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label small fw-bold">Bank Name</label>
                                            <input v-model="form.payment_mode_details.bank_name" type="text" class="form-control form-control-sm" placeholder="e.g. HDFC Bank">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label small fw-bold">Account Number</label>
                                            <input v-model="form.payment_mode_details.account_number" type="text" class="form-control form-control-sm" placeholder="e.g. 1234567890123">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label small fw-bold">IFSC Code</label>
                                            <input v-model="form.payment_mode_details.ifsc_code" type="text" class="form-control form-control-sm" placeholder="e.g. HDFC0001234">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Attach Invoice (Optional)</label>
                                <input type="file" @input="form.invoice_file = $event.target.files[0]" class="form-control" accept="image/*,application/pdf">
                                <div v-if="form.errors.invoice_file" class="text-danger mt-1 small">{{ form.errors.invoice_file }}</div>
                                <div v-if="editingExpense && editingExpense.invoice_file_path" class="mt-2">
                                    <button type="button" @click="openInvoicePreview(editingExpense)" class="btn btn-sm btn-soft-primary">
                                        <i class="ti ti-file-text me-1"></i> View Current Invoice
                                    </button>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch mb-2">
                                    <input v-model="form.is_recurring" class="form-check-input" type="checkbox" id="recurringSwitch">
                                    <label class="form-check-label" for="recurringSwitch">Recurring Expense?</label>
                                </div>
                                <div v-if="form.is_recurring" class="row g-2 ps-3 animate-slide-in">
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label small fw-bold">Frequency</label>
                                        <select v-model="form.recurring_frequency" class="form-select form-select-sm" required>
                                            <option value="" disabled>Select Frequency</option>
                                            <option value="weekly">Weekly</option>
                                            <option value="monthly">Monthly</option>
                                            <option value="yearly">Yearly</option>
                                        </select>
                                        <div v-if="form.errors.recurring_frequency" class="text-danger mt-1 small">{{ form.errors.recurring_frequency }}</div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label small fw-bold">Next Due Date</label>
                                        <input v-model="form.next_due_date" type="date" class="form-control form-control-sm" required>
                                        <div v-if="form.errors.next_due_date" class="text-danger mt-1 small">{{ form.errors.next_due_date }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-0">
                                <label class="form-label">Notes</label>
                                <textarea v-model="form.notes" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" @click="closeModal">Cancel</button>
                            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                {{ editingExpense ? 'Update' : 'Save' }} Expense
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Invoice Preview Modal -->
        <div v-if="showInvoicePreview" class="modal fade show d-block" style="background: rgba(0,0,0,0.7); z-index: 1060" @click.self="closeInvoicePreview">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content" style="min-height: 500px;">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-file-text me-2"></i> Invoice — {{ previewInvoice.name }}
                        </h5>
                        <div class="d-flex align-items-center gap-2">
                            <a :href="previewInvoice.url" target="_blank" class="btn btn-sm btn-soft-primary" title="Open in New Tab">
                                <i class="ti ti-external-link"></i>
                            </a>
                            <a :href="previewInvoice.url" download class="btn btn-sm btn-soft-success" title="Download">
                                <i class="ti ti-download"></i>
                            </a>
                            <button type="button" class="btn-close" @click="closeInvoicePreview"></button>
                        </div>
                    </div>
                    <div class="modal-body p-0 d-flex align-items-center justify-content-center" style="min-height: 450px; background: #f8f9fa;">
                        <!-- PDF Preview -->
                        <iframe v-if="previewInvoice.type === 'pdf'" :src="previewInvoice.url" style="width: 100%; height: 500px; border: none;"></iframe>
                        <!-- Image Preview -->
                        <img v-else :src="previewInvoice.url" :alt="previewInvoice.name" class="img-fluid" style="max-height: 500px; object-fit: contain;">
                    </div>
                </div>
            </div>
        </div>

        <!-- Expense Details Modal -->
        <div v-if="showDetailsModal && detailsExpense" class="modal fade show d-block" style="background: rgba(0,0,0,0.5); z-index: 1055" @click.self="closeDetailsModal">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-receipt me-2"></i> Expense Details
                        </h5>
                        <button type="button" class="btn-close" @click="closeDetailsModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-5 text-muted small">Expense Name</div>
                            <div class="col-7 fw-semibold">{{ detailsExpense.name }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-5 text-muted small">Date</div>
                            <div class="col-7">{{ formatDate(detailsExpense.date) }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-5 text-muted small">Category</div>
                            <div class="col-7">
                                <span class="badge bg-soft-info text-info">{{ detailsExpense.category.name }}</span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-5 text-muted small">Amount</div>
                            <div class="col-7 fw-bold text-dark">{{ formatCurrency(detailsExpense.amount) }}</div>
                        </div>

                        <hr class="my-3">
                        <h6 class="mb-3"><i class="ti ti-credit-card me-1"></i> Payment Details</h6>

                        <div class="row mb-2">
                            <div class="col-5 text-muted small">Payment Mode</div>
                            <div class="col-7">
                                <span v-if="detailsExpense.payment_mode" class="badge" :class="paymentModeBadgeClass(detailsExpense.payment_mode)">
                                    {{ paymentModeLabel(detailsExpense.payment_mode) }}
                                </span>
                                <span v-else class="text-muted">-</span>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5 text-muted small">Reference / Txn ID</div>
                            <div class="col-7">
                                <code v-if="detailsExpense.payment_reference" class="text-dark">{{ detailsExpense.payment_reference }}</code>
                                <span v-else class="text-muted">-</span>
                            </div>
                        </div>

                        <!-- UPI details -->
                        <div v-if="detailsExpense.payment_mode === 'upi' && detailsExpense.payment_mode_details" class="mt-2">
                            <div class="row mb-2">
                                <div class="col-5 text-muted small">UPI ID (Paid To)</div>
                                <div class="col-7"><code class="text-dark">{{ detailsExpense.payment_mode_details.upi_id || '-' }}</code></div>
                            </div>
                        </div>

                        <!-- Bank Transfer / NEFT-RTGS details -->
                        <div v-if="(detailsExpense.payment_mode === 'bank_transfer' || detailsExpense.payment_mode === 'neft_rtgs') && detailsExpense.payment_mode_details" class="mt-2">
                            <div class="row mb-2">
                                <div class="col-5 text-muted small">Account Holder</div>
                                <div class="col-7">{{ detailsExpense.payment_mode_details.account_holder || '-' }}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-5 text-muted small">Bank Name</div>
                                <div class="col-7">{{ detailsExpense.payment_mode_details.bank_name || '-' }}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-5 text-muted small">Account Number</div>
                                <div class="col-7"><code class="text-dark">{{ detailsExpense.payment_mode_details.account_number || '-' }}</code></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-5 text-muted small">IFSC Code</div>
                                <div class="col-7"><code class="text-dark">{{ detailsExpense.payment_mode_details.ifsc_code || '-' }}</code></div>
                            </div>
                        </div>

                        <hr class="my-3" v-if="detailsExpense.invoice_file_path || detailsExpense.is_recurring || detailsExpense.notes">

                        <!-- Invoice -->
                        <div v-if="detailsExpense.invoice_file_path" class="row mb-3">
                            <div class="col-5 text-muted small">Invoice</div>
                            <div class="col-7">
                                <button @click="openInvoicePreview(detailsExpense)" class="btn btn-sm btn-soft-primary">
                                    <i class="ti ti-file-text me-1"></i> View Invoice
                                </button>
                            </div>
                        </div>

                        <!-- Recurring -->
                        <div v-if="detailsExpense.is_recurring" class="row mb-3">
                            <div class="col-5 text-muted small">Recurring</div>
                            <div class="col-7">
                                <span class="badge bg-soft-success text-success me-1">Yes</span>
                                <span class="text-capitalize">{{ detailsExpense.recurring_frequency }}</span>
                                <div v-if="detailsExpense.next_due_date" class="text-muted small mt-1">
                                    Next due: <strong class="text-primary">{{ formatDate(detailsExpense.next_due_date) }}</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div v-if="detailsExpense.notes" class="row mb-2">
                            <div class="col-5 text-muted small">Notes</div>
                            <div class="col-7 text-dark" style="white-space: pre-line;">{{ detailsExpense.notes }}</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" @click="closeDetailsModal">Close</button>
                        <button type="button" class="btn btn-primary" @click="closeDetailsModal(); openModal(detailsExpense);">
                            <i class="ti ti-edit me-1"></i> Edit
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>
