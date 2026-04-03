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

const form = useForm({
    category_id: '',
    name: '',
    amount: '',
    payment_reference: '',
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
        form.date = expense.date;
        form.is_recurring = !!expense.is_recurring;
        form.recurring_frequency = expense.recurring_frequency || '';
        form.next_due_date = expense.next_due_date || '';
        form.notes = expense.notes;
        form.invoice_file = null; // Don't pre-fill file
    } else {
        editingExpense.value = null;
        form.reset();
        form.date = new Date().toISOString().split('T')[0];
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const submit = () => {
    if (editingExpense.value) {
        form.transform((data) => ({
            ...data,
            _method: 'PUT',
        })).post(route('expenses.update', editingExpense.value.id), {
            forceFormData: true,
            onSuccess: () => closeModal()
        });
    } else {
        form.post(route('expenses.store'), {
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
                                            <span v-if="expense.payment_reference" class="text-dark">{{ expense.payment_reference }}</span>
                                            <span v-else class="text-muted small">-</span>
                                        </td>
                                        <td>
                                            <a v-if="expense.invoice_file_path" :href="expense.invoice_file_path" target="_blank" class="btn btn-sm btn-soft-primary px-2 py-1 shadow-none">
                                                <i class="ti ti-file-text me-1"></i> View
                                            </a>
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
                                            <button @click="openModal(expense)" class="btn btn-sm btn-light me-1 shadow-sm">
                                                <i class="ti ti-edit"></i>
                                            </button>
                                            <button @click="deleteExpense(expense.id)" class="btn btn-sm btn-light text-danger shadow-sm">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="expenses.length === 0">
                                        <td colspan="6" class="text-center py-4 text-muted">No expenses found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expense Modal -->
        <div v-if="showModal" class="modal fade show d-block" style="background: rgba(0,0,0,0.5)">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ editingExpense ? 'Edit' : 'Add' }} Expense</h5>
                        <button type="button" class="btn-close" @click="closeModal"></button>
                    </div>
                    <form @submit.prevent="submit">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Date</label>
                                <input v-model="form.date" type="date" class="form-control" required>
                                <div v-if="form.errors.date" class="text-danger mt-1 small">{{ form.errors.date }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Expense Name</label>
                                <input v-model="form.name" type="text" class="form-control" placeholder="e.g. Office Rent" required>
                                <div v-if="form.errors.name" class="text-danger mt-1 small">{{ form.errors.name }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select v-model="form.category_id" class="form-select" required>
                                    <option value="" disabled>Select Category</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                </select>
                                <div v-if="form.errors.category_id" class="text-danger mt-1 small">{{ form.errors.category_id }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input v-model="form.amount" type="number" step="0.01" class="form-control" required>
                                </div>
                                <div v-if="form.errors.amount" class="text-danger mt-1 small">{{ form.errors.amount }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Payment Reference (e.g. Transaction ID)</label>
                                <input v-model="form.payment_reference" type="text" class="form-control" placeholder="e.g. TXN123456789">
                                <div v-if="form.errors.payment_reference" class="text-danger mt-1 small">{{ form.errors.payment_reference }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Attach Invoice (Optional)</label>
                                <input type="file" @input="form.invoice_file = $event.target.files[0]" class="form-control" accept="image/*,application/pdf">
                                <div v-if="form.errors.invoice_file" class="text-danger mt-1 small">{{ form.errors.invoice_file }}</div>
                                <div v-if="editingExpense && editingExpense.invoice_file_path" class="mt-1 small">
                                    <a :href="editingExpense.invoice_file_path" target="_blank" class="text-primary">View current file</a>
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
    </Layout>
</template>
