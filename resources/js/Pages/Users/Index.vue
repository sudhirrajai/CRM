<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    users: {
        type: Array,
        required: true
    },
    roles: {
        type: Array,
        required: true
    }
});

const showingUserModal = ref(false);
const editingUser = ref(null);

const form = useForm({
    name: '',
    email: '',
    password: '',
    roles: [],
});

const openCreateModal = () => {
    editingUser.value = null;
    form.reset();
    showingUserModal.value = true;
};

const openEditModal = (user) => {
    editingUser.value = user;
    form.name = user.name;
    form.email = user.email;
    form.password = '';
    form.roles = user.roles.map(r => r.name);
    showingUserModal.value = true;
};

const submit = () => {
    if (editingUser.value) {
        form.put(route('users.update', editingUser.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('users.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const closeModal = () => {
    showingUserModal.value = false;
    form.reset();
};

const deleteUser = (id) => {
    if (confirm('Are you sure you want to delete this user?')) {
        useForm({}).delete(route('users.destroy', id));
    }
};
</script>

<template>
    <Head title="Users Management" />

    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <button @click="openCreateModal" class="btn btn-primary btn-sm">
                            <i class="ti ti-plus me-1"></i> Add New User
                        </button>
                    </div>
                    <h4 class="page-title">Users Management</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div v-if="$page.props.flash.success" class="alert alert-success mt-2">
                            {{ $page.props.flash.success }}
                        </div>

                        <div class="table-responsive">
                            <table class="table table-centered table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Roles</th>
                                        <th>Created At</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="user in users" :key="user.id">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-2">
                                                    <span class="avatar-title bg-primary-subtle text-primary rounded-circle">
                                                        {{ user.name.charAt(0) }}
                                                    </span>
                                                </div>
                                                <span class="fw-semibold">{{ user.name }}</span>
                                            </div>
                                        </td>
                                        <td>{{ user.email }}</td>
                                        <td>
                                            <span v-for="role in user.roles" :key="role.id" class="badge bg-info-subtle text-info me-1 text-capitalize">
                                                {{ role.name }}
                                            </span>
                                        </td>
                                        <td>{{ new Date(user.created_at).toLocaleDateString() }}</td>
                                        <td class="text-end">
                                            <button @click="openEditModal(user)" class="btn btn-sm btn-soft-primary me-1">
                                                <i class="ti ti-edit"></i>
                                            </button>
                                            <button v-if="user.id !== $page.props.auth.user.id" @click="deleteUser(user.id)" class="btn btn-sm btn-soft-danger">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Modal (Built-in simple modal using Bootstrap classes) -->
        <div v-if="showingUserModal" class="modal fade show d-block" style="background: rgba(0,0,0,0.5)">
            <div class="modal-dialog">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">{{ editingUser ? 'Edit User' : 'Add New User' }}</h5>
                        <button type="button" class="btn-close btn-close-white" @click="closeModal"></button>
                    </div>
                    <form @submit.prevent="submit">
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" v-model="form.name" class="form-control" required>
                                <div v-if="form.errors.name" class="text-danger small mt-1">{{ form.errors.name }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" v-model="form.email" class="form-control" required>
                                <div v-if="form.errors.email" class="text-danger small mt-1">{{ form.errors.email }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password {{ editingUser ? '(Leave blank to keep current)' : '' }}</label>
                                <input type="password" v-model="form.password" class="form-control" :required="!editingUser">
                                <div v-if="form.errors.password" class="text-danger small mt-1">{{ form.errors.password }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Assign Roles</label>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    <div v-for="role in roles" :key="role.id" class="form-check">
                                        <input class="form-check-input" type="checkbox" :id="'role'+role.id" :value="role.name" v-model="form.roles">
                                        <label class="form-check-label text-capitalize" :for="'role'+role.id">{{ role.name }}</label>
                                    </div>
                                </div>
                                <div v-if="form.errors.roles" class="text-danger small mt-1">{{ form.errors.roles }}</div>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0">
                            <button type="button" class="btn btn-light" @click="closeModal">Cancel</button>
                            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                {{ editingUser ? 'Update User' : 'Create User' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.modal { transition: all 0.3s ease; }
</style>
