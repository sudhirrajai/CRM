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
    date: new Date().toISOString().split('T')[0],
    is_recurring: false,
    notes: ''
});

const openModal = (expense = null) => {
    if (expense) {
        editingExpense.value = expense;
        form.category_id = expense.category_id;
        form.name = expense.name;
        form.amount = expense.amount;
        form.date = expense.date;
        form.is_recurring = expense.is_recurring;
        form.notes = expense.notes;
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
        form.put(route('expenses.update', editingExpense.value.id), {
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
                                        <th>Recurring</th>
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
                                            <span v-if="expense.is_recurring" class="badge bg-soft-success text-success">Yes</span>
                                            <span v-else class="badge bg-soft-secondary text-secondary">No</span>
                                        </td>
                                        <td class="text-end">
                                            <button @click="openModal(expense)" class="btn btn-sm btn-light me-1">
                                                <i class="ti ti-edit"></i>
                                            </button>
                                            <button @click="deleteExpense(expense.id)" class="btn btn-sm btn-light text-danger">
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
                                <div class="form-check form-switch">
                                    <input v-model="form.is_recurring" class="form-check-input" type="checkbox" id="recurringSwitch">
                                    <label class="form-check-label" for="recurringSwitch">Recurring Expense?</label>
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
