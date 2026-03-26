<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Layout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    categories: Array
});

const form = useForm({
    name: '',
    description: ''
});

const editingCategory = ref(null);

const submit = () => {
    if (editingCategory.value) {
        form.put(route('expense-categories.update', editingCategory.value.id), {
            onSuccess: () => {
                editingCategory.value = null;
                form.reset();
            }
        });
    } else {
        form.post(route('expense-categories.store'), {
            onSuccess: () => form.reset()
        });
    }
};

const editCategory = (category) => {
    editingCategory.value = category;
    form.name = category.name;
    form.description = category.description;
};

const cancelEdit = () => {
    editingCategory.value = null;
    form.reset();
};

const deleteCategory = (id) => {
    if (confirm('Are you sure you want to delete this category?')) {
        form.delete(route('expense-categories.destroy', id));
    }
};
</script>

<template>
    <Head title="Expense Categories" />

    <Layout>
        <div class="row">
            <div class="col-12">
                <h4 class="page-title mb-3">Expense Categories</h4>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ editingCategory ? 'Edit' : 'Add' }} Category</h5>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="submit">
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input v-model="form.name" type="text" class="form-control" placeholder="e.g. Office Supplies" required>
                                <div v-if="form.errors.name" class="text-danger mt-1 small">{{ form.errors.name }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea v-model="form.description" class="form-control" rows="3" placeholder="Optional description"></textarea>
                                <div v-if="form.errors.description" class="text-danger mt-1 small">{{ form.errors.description }}</div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                    {{ editingCategory ? 'Update' : 'Save' }} Category
                                </button>
                                <button v-if="editingCategory" type="button" @click="cancelEdit" class="btn btn-light">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-centered mb-0 text-nowrap">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="category in categories" :key="category.id">
                                        <td><strong>{{ category.name }}</strong></td>
                                        <td>{{ category.description || '-' }}</td>
                                        <td class="text-end">
                                            <button @click="editCategory(category)" class="btn btn-sm btn-light me-1">
                                                <i class="ti ti-edit"></i>
                                            </button>
                                            <button @click="deleteCategory(category.id)" class="btn btn-sm btn-light text-danger">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="categories.length === 0">
                                        <td colspan="3" class="text-center py-4 text-muted">No categories found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>
