<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    roles: {
        type: Array,
        required: true
    },
    groupedPermissions: {
        type: Object,
        required: true
    }
});

const activeRoleIndex = ref(0);
const activeRole = computed(() => props.roles[activeRoleIndex.value]);

const roleForms = props.roles.map(role => {
    return useForm({
        permissions: role.permissions.map(p => p.name)
    });
});

const updatePermissions = (index) => {
    const role = props.roles[index];
    roleForms[index].put(route('roles.update', role.id), {
        preserveScroll: true,
    });
};

const togglePermission = (roleIndex, permissionName) => {
    if (props.roles[roleIndex].name === 'admin') return;

    const index = roleForms[roleIndex].permissions.indexOf(permissionName);
    if (index > -1) {
        roleForms[roleIndex].permissions.splice(index, 1);
    } else {
        roleForms[roleIndex].permissions.push(permissionName);
    }
};

const isPermissionSelected = (roleIndex, permissionName) => {
    return roleForms[roleIndex].permissions.includes(permissionName);
};

const formatLabel = (name) => {
    return name.split('.').pop().charAt(0).toUpperCase() + name.split('.').pop().slice(1);
};

const moduleOrder = [
    'clients', 'projects', 'invoices', 'hostings', 'servers', 'orders', 
    'expenses', 'expense_categories', 'reports', 'users', 'roles', 'settings'
];

const sortedModules = computed(() => {
    const modules = Object.keys(props.groupedPermissions);
    return modules.sort((a, b) => {
        const indexA = moduleOrder.indexOf(a);
        const indexB = moduleOrder.indexOf(b);
        if (indexA === -1) return 1;
        if (indexB === -1) return -1;
        return indexA - indexB;
    });
});
</script>

<template>
    <Head title="Role Permissions" />

    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">Role & Permission Management</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body p-0">
                        <div class="nav flex-column nav-pills" role="tablist">
                            <button 
                                v-for="(role, index) in roles" 
                                :key="role.id"
                                @click="activeRoleIndex = index" 
                                class="nav-link text-start rounded-0 py-3 border-bottom text-capitalize" 
                                :class="{ 'active': activeRoleIndex === index }"
                            >
                                <i class="ti ti-shield me-2"></i> {{ role.name }} Role
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info mt-3 shadow-sm border-0">
                    <p class="mb-0 small">
                        <i class="ti ti-info-circle me-1"></i>
                        <strong>Admin Role:</strong> Permissions are automatically assigned and cannot be modified.
                    </p>
                </div>
            </div>

            <div class="col-md-9">
                <div class="card">
                    <div class="card-body">
                        <!-- Success/Error Alerts -->
                        <div v-if="$page.props.flash.success" class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ $page.props.flash.success }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <div v-if="$page.props.flash.error" class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ $page.props.flash.error }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="header-title mb-0 text-capitalize">{{ activeRole.name }} Permissions</h4>
                            <button 
                                v-if="activeRole.name !== 'admin'"
                                @click="updatePermissions(activeRoleIndex)" 
                                class="btn btn-primary btn-sm"
                                :disabled="roleForms[activeRoleIndex].processing"
                            >
                                <i class="ti ti-device-floppy me-1"></i> Save Changes
                            </button>
                        </div>

                        <div class="permission-matrix">
                            <div v-for="moduleName in sortedModules" :key="moduleName" class="module-section mb-4">
                                <h5 class="module-title text-capitalize border-bottom pb-2 mb-3">
                                    <i class="ti ti-folder me-2 text-primary"></i> {{ moduleName.replace('_', ' ') }}
                                </h5>
                                <div class="row">
                                    <div 
                                        v-for="permission in groupedPermissions[moduleName]" 
                                        :key="permission.id"
                                        class="col-md-3 mb-3"
                                    >
                                        <div class="form-check form-switch custom-switch">
                                            <input 
                                                class="form-check-input" 
                                                type="checkbox" 
                                                :id="permission.id"
                                                :checked="isPermissionSelected(activeRoleIndex, permission.name)"
                                                @change="togglePermission(activeRoleIndex, permission.name)"
                                                :disabled="activeRole.name === 'admin' || roleForms[activeRoleIndex].processing"
                                            >
                                            <label class="form-check-label" :for="permission.id">
                                                {{ formatLabel(permission.name) }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top" v-if="activeRole.name !== 'admin'">
                            <button 
                                @click="updatePermissions(activeRoleIndex)" 
                                class="btn btn-primary"
                                :disabled="roleForms[activeRoleIndex].processing"
                            >
                                <i class="ti ti-device-floppy me-1"></i> Save Permissions for {{ activeRole.name }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.nav-pills .nav-link.active {
    background-color: var(--bs-primary);
    color: #fff;
}
.nav-link {
    color: var(--bs-body-color);
    transition: all 0.3s ease;
}
.nav-link:hover:not(.active) {
    background-color: rgba(var(--bs-primary-rgb), 0.1);
}
.module-title {
    font-size: 1.1rem;
    font-weight: 600;
}
.custom-switch .form-check-input {
    width: 2.5em;
    height: 1.25em;
    cursor: pointer;
}
.custom-switch .form-check-label {
    padding-left: 0.5rem;
    cursor: pointer;
    font-weight: 500;
}
</style>
