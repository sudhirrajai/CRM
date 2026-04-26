<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    secrets: Array,
    categories: Array,
    typeConfig: Object,
    filters: Object,
});

// State
const showAddModal = ref(false);
const showViewModal = ref(false);
const showCategoryModal = ref(false);
const editingSecret = ref(null);
const viewingSecret = ref(null);
const decryptedData = ref(null);
const revealedFields = ref({});
const searchQuery = ref(props.filters?.search || '');
const activeFilter = ref(props.filters?.type || '');
const activeCategory = ref(props.filters?.category || '');
const showFavorites = ref(props.filters?.favorites || false);
const copyFeedback = ref('');

// Forms
const secretForm = useForm({
    name: '',
    type: 'password',
    encrypted_data: {},
    tags: '',
    url: '',
    category_id: '',
    custom_fields: {},
});

const categoryForm = useForm({
    name: '',
    icon: 'ti-folder',
    color: '#6366f1',
});

const customFieldName = ref('');
const customFieldType = ref('text');

// Computed
const currentFields = computed(() => {
    if (secretForm.type === 'custom') return [];
    return props.typeConfig[secretForm.type]?.fields || [];
});

const filteredSecrets = computed(() => props.secrets);

const secretCounts = computed(() => {
    const counts = { all: props.secrets.length };
    Object.keys(props.typeConfig).forEach(t => {
        counts[t] = props.secrets.filter(s => s.type === t).length;
    });
    return counts;
});

// Methods
const applyFilters = () => {
    router.get(route('vault.index'), {
        search: searchQuery.value || undefined,
        type: activeFilter.value || undefined,
        category: activeCategory.value || undefined,
        favorites: showFavorites.value || undefined,
    }, { preserveState: true, preserveScroll: true });
};

let searchTimeout;
watch(searchQuery, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 400);
});

const setTypeFilter = (type) => {
    activeFilter.value = activeFilter.value === type ? '' : type;
    applyFilters();
};

const setCategoryFilter = (id) => {
    activeCategory.value = activeCategory.value === id ? '' : id;
    applyFilters();
};

const toggleFavorites = () => {
    showFavorites.value = !showFavorites.value;
    applyFilters();
};

const openAddModal = (type = 'password') => {
    editingSecret.value = null;
    secretForm.reset();
    secretForm.type = type;
    secretForm.encrypted_data = {};
    secretForm.custom_fields = {};
    showAddModal.value = true;
};

const openEditModal = async (secret) => {
    editingSecret.value = secret;
    secretForm.name = secret.name;
    secretForm.type = secret.type;
    secretForm.tags = secret.tags || '';
    secretForm.url = secret.url || '';
    secretForm.category_id = secret.category_id || '';

    try {
        const res = await fetch(route('vault.decrypt', secret.id));
        const json = await res.json();
        secretForm.encrypted_data = json.data || {};
        if (secret.type === 'custom') {
            secretForm.custom_fields = json.data || {};
        }
    } catch (e) {
        secretForm.encrypted_data = {};
    }
    showAddModal.value = true;
};

const saveSecret = () => {
    if (editingSecret.value) {
        secretForm.put(route('vault.update', editingSecret.value.id), {
            onSuccess: () => { showAddModal.value = false; secretForm.reset(); },
        });
    } else {
        secretForm.post(route('vault.store'), {
            onSuccess: () => { showAddModal.value = false; secretForm.reset(); },
        });
    }
};

const deleteSecret = (secret) => {
    if (confirm(`Delete "${secret.name}"? This cannot be undone.`)) {
        router.delete(route('vault.destroy', secret.id));
    }
};

const toggleFavorite = (secret) => {
    router.post(route('vault.favorite', secret.id), {}, { preserveScroll: true });
};

const viewSecret = async (secret) => {
    viewingSecret.value = secret;
    decryptedData.value = null;
    revealedFields.value = {};
    showViewModal.value = true;
    try {
        const res = await fetch(route('vault.decrypt', secret.id));
        const json = await res.json();
        decryptedData.value = json.data;
    } catch (e) {
        decryptedData.value = { error: 'Failed to decrypt' };
    }
};

const toggleReveal = (key) => {
    revealedFields.value[key] = !revealedFields.value[key];
};

const copyToClipboard = async (text, label) => {
    try {
        await navigator.clipboard.writeText(text);
        copyFeedback.value = label;
        setTimeout(() => { copyFeedback.value = ''; }, 2000);
    } catch (e) { /* fallback */ }
};

const addCustomField = () => {
    if (customFieldName.value.trim()) {
        secretForm.custom_fields[customFieldName.value.trim()] = '';
        customFieldName.value = '';
    }
};

const removeCustomField = (key) => {
    delete secretForm.custom_fields[key];
};

const saveCategory = () => {
    categoryForm.post(route('vault.categories.store'), {
        onSuccess: () => { showCategoryModal.value = false; categoryForm.reset(); },
    });
};

const deleteCategory = (cat) => {
    if (confirm(`Delete category "${cat.name}"?`)) {
        router.delete(route('vault.categories.destroy', cat.id));
    }
};

const isPasswordField = (field) => {
    if (typeof field === 'object') return field.type === 'password';
    const key = String(field).toLowerCase();
    return key.includes('password') || key.includes('secret') || key.includes('key') || key.includes('private') || key.includes('pem');
};

const getTypeIcon = (type) => props.typeConfig[type]?.icon || 'ti-lock';
const getTypeColor = (type) => props.typeConfig[type]?.color || '#64748b';
const getTypeLabel = (type) => props.typeConfig[type]?.label || type;
</script>

<template>
    <Head title="Secrets Vault" />
    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">
                        <i class="ti ti-shield-lock me-2" style="color: #6366f1;"></i>Secrets Vault
                    </h4>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-3">
            <div class="col-6 col-md-3 col-lg" v-for="(conf, type) in typeConfig" :key="type">
                <div class="card mb-2" style="cursor:pointer; transition: all 0.2s;" :style="activeFilter === type ? { borderColor: conf.color, borderWidth: '2px' } : {}" @click="setTypeFilter(type)">
                    <div class="card-body py-2 px-3 d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" :style="{ background: conf.color + '20', color: conf.color, width: '32px', height: '32px', minWidth: '32px' }">
                            <i :class="conf.icon" style="font-size: 16px;"></i>
                        </div>
                        <div>
                            <div class="fw-semibold text-truncate" style="font-size: 12px;">{{ conf.label }}</div>
                            <div class="fw-bold" style="font-size: 16px;">{{ secretCounts[type] || 0 }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <button @click="openAddModal()" class="btn btn-primary w-100 mb-3">
                            <i class="ti ti-plus me-1"></i> Add Secret
                        </button>

                        <div class="mb-3">
                            <input v-model="searchQuery" type="text" class="form-control form-control-sm" placeholder="Search secrets..." />
                        </div>

                        <ul class="list-unstyled mb-3">
                            <li class="py-1 px-2 rounded mb-1" style="cursor:pointer;" :class="{ 'bg-primary bg-opacity-10': !activeFilter && !showFavorites && !activeCategory }" @click="activeFilter=''; activeCategory=''; showFavorites=false; applyFilters();">
                                <i class="ti ti-list me-2"></i> All Secrets <span class="badge bg-secondary float-end">{{ secretCounts.all }}</span>
                            </li>
                            <li class="py-1 px-2 rounded mb-1" style="cursor:pointer;" :class="{ 'bg-warning bg-opacity-10': showFavorites }" @click="toggleFavorites">
                                <i class="ti ti-star me-2 text-warning"></i> Favorites
                            </li>
                        </ul>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-uppercase fw-bold text-muted">Categories</small>
                            <button @click="showCategoryModal=true; categoryForm.reset();" class="btn btn-sm btn-outline-primary py-0 px-1" style="font-size: 11px;"><i class="ti ti-plus"></i></button>
                        </div>
                        <ul class="list-unstyled">
                            <li v-for="cat in categories" :key="cat.id" class="py-1 px-2 rounded mb-1 d-flex align-items-center justify-content-between" style="cursor:pointer;" :class="{ 'bg-primary bg-opacity-10': activeCategory === cat.id }" @click="setCategoryFilter(cat.id)">
                                <span><i :class="cat.icon" class="me-2" :style="{ color: cat.color }"></i>{{ cat.name }}</span>
                                <div>
                                    <span class="badge bg-secondary me-1">{{ cat.secrets_count }}</span>
                                    <button @click.stop="deleteCategory(cat)" class="btn btn-sm p-0 text-danger"><i class="ti ti-x" style="font-size:12px;"></i></button>
                                </div>
                            </li>
                            <li v-if="categories.length === 0" class="text-muted py-1" style="font-size: 12px;">No categories yet</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">
                                <template v-if="showFavorites"><i class="ti ti-star text-warning me-1"></i> Favorites</template>
                                <template v-else-if="activeFilter">{{ typeConfig[activeFilter]?.label }}</template>
                                <template v-else>All Secrets</template>
                                <span class="badge bg-secondary ms-2">{{ filteredSecrets.length }}</span>
                            </h5>
                        </div>

                        <!-- Secrets List -->
                        <div v-if="filteredSecrets.length === 0" class="text-center py-5">
                            <i class="ti ti-shield-lock" style="font-size: 48px; color: #ccc;"></i>
                            <p class="text-muted mt-2">No secrets found. Add your first secret to get started.</p>
                            <button @click="openAddModal()" class="btn btn-primary"><i class="ti ti-plus me-1"></i> Add Secret</button>
                        </div>

                        <div v-else class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:40px;"></th>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Category</th>
                                        <th>Tags</th>
                                        <th>Updated</th>
                                        <th style="width:140px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="secret in filteredSecrets" :key="secret.id">
                                        <td>
                                            <button @click="toggleFavorite(secret)" class="btn btn-sm p-0" :title="secret.is_favorite ? 'Unfavorite' : 'Favorite'">
                                                <i :class="secret.is_favorite ? 'ti ti-star-filled text-warning' : 'ti ti-star text-muted'"></i>
                                            </button>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center" :style="{ background: getTypeColor(secret.type) + '20', color: getTypeColor(secret.type), width: '30px', height: '30px', minWidth: '30px' }">
                                                    <i :class="getTypeIcon(secret.type)" style="font-size:14px;"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">{{ secret.name }}</div>
                                                    <small v-if="secret.url" class="text-muted">{{ secret.url }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="badge" :style="{ background: getTypeColor(secret.type) + '20', color: getTypeColor(secret.type) }">{{ getTypeLabel(secret.type) }}</span></td>
                                        <td><span v-if="secret.category" class="badge bg-light text-dark"><i :class="secret.category.icon" class="me-1" :style="{ color: secret.category.color }"></i>{{ secret.category.name }}</span><span v-else class="text-muted">—</span></td>
                                        <td><small class="text-muted">{{ secret.tags || '—' }}</small></td>
                                        <td><small class="text-muted">{{ secret.updated_at }}</small></td>
                                        <td>
                                            <button @click="viewSecret(secret)" class="btn btn-sm btn-outline-primary me-1" title="View"><i class="ti ti-eye"></i></button>
                                            <button @click="openEditModal(secret)" class="btn btn-sm btn-outline-warning me-1" title="Edit"><i class="ti ti-edit"></i></button>
                                            <button @click="deleteSecret(secret)" class="btn btn-sm btn-outline-danger" title="Delete"><i class="ti ti-trash"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <div v-if="showAddModal" class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="ti ti-shield-lock me-2"></i>{{ editingSecret ? 'Edit' : 'Add' }} Secret</h5>
                        <button @click="showAddModal=false" class="btn-close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label class="form-label">Name *</label>
                                <input v-model="secretForm.name" type="text" class="form-control" placeholder="e.g. Production Database" />
                                <div v-if="secretForm.errors.name" class="text-danger small">{{ secretForm.errors.name }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Type *</label>
                                <select v-model="secretForm.type" class="form-select" :disabled="!!editingSecret">
                                    <option v-for="(conf, type) in typeConfig" :key="type" :value="type">{{ conf.label }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Type-specific fields -->
                        <div v-if="secretForm.type !== 'custom'" class="row">
                            <div v-for="field in currentFields" :key="field.key" :class="field.type === 'textarea' ? 'col-12' : 'col-md-6'" class="mb-3">
                                <label class="form-label">{{ field.label }}</label>
                                <div v-if="field.type === 'password'" class="input-group">
                                    <input :type="revealedFields['form_'+field.key] ? 'text' : 'password'" v-model="secretForm.encrypted_data[field.key]" class="form-control" />
                                    <button @click="toggleReveal('form_'+field.key)" class="btn btn-outline-secondary" type="button">
                                        <i :class="revealedFields['form_'+field.key] ? 'ti ti-eye-off' : 'ti ti-eye'"></i>
                                    </button>
                                </div>
                                <select v-else-if="field.type === 'select'" v-model="secretForm.encrypted_data[field.key]" class="form-select">
                                    <option value="">Select...</option>
                                    <option v-for="opt in field.options" :key="opt" :value="opt">{{ opt }}</option>
                                </select>
                                <textarea v-else-if="field.type === 'textarea'" v-model="secretForm.encrypted_data[field.key]" class="form-control" rows="3"></textarea>
                                <input v-else type="text" v-model="secretForm.encrypted_data[field.key]" class="form-control" />
                            </div>
                        </div>

                        <!-- Custom fields -->
                        <div v-if="secretForm.type === 'custom'">
                            <div class="d-flex gap-2 mb-3">
                                <input v-model="customFieldName" class="form-control form-control-sm" placeholder="Field name" @keyup.enter="addCustomField" />
                                <select v-model="customFieldType" class="form-select form-select-sm" style="width:120px;">
                                    <option value="text">Text</option>
                                    <option value="password">Secret</option>
                                </select>
                                <button @click="addCustomField" class="btn btn-sm btn-outline-primary"><i class="ti ti-plus"></i></button>
                            </div>
                            <div v-for="(val, key) in secretForm.custom_fields" :key="key" class="mb-2">
                                <label class="form-label d-flex justify-content-between">
                                    {{ key }}
                                    <button @click="removeCustomField(key)" class="btn btn-sm p-0 text-danger"><i class="ti ti-x"></i></button>
                                </label>
                                <input type="text" v-model="secretForm.custom_fields[key]" class="form-control" />
                            </div>
                        </div>

                        <hr />
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Category</label>
                                <select v-model="secretForm.category_id" class="form-select">
                                    <option value="">None</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tags</label>
                                <input v-model="secretForm.tags" type="text" class="form-control" placeholder="prod, aws, mysql" />
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">URL</label>
                                <input v-model="secretForm.url" type="text" class="form-control" placeholder="https://..." />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button @click="showAddModal=false" class="btn btn-secondary">Cancel</button>
                        <button @click="saveSecret" class="btn btn-primary" :disabled="secretForm.processing">
                            <i class="ti ti-device-floppy me-1"></i> {{ editingSecret ? 'Update' : 'Save' }} Secret
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- View Modal -->
        <div v-if="showViewModal" class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i :class="getTypeIcon(viewingSecret.type)" class="me-2" :style="{ color: getTypeColor(viewingSecret.type) }"></i>
                            {{ viewingSecret.name }}
                        </h5>
                        <button @click="showViewModal=false" class="btn-close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Copy feedback toast -->
                        <div v-if="copyFeedback" class="alert alert-success py-1 px-3 d-inline-block mb-3" style="font-size:12px;">
                            <i class="ti ti-check me-1"></i> Copied "{{ copyFeedback }}" to clipboard
                        </div>

                        <div v-if="!decryptedData" class="text-center py-4">
                            <div class="spinner-border text-primary"></div>
                            <p class="mt-2 text-muted">Decrypting...</p>
                        </div>

                        <div v-else>
                            <div class="mb-2 d-flex gap-2 flex-wrap">
                                <span class="badge" :style="{ background: getTypeColor(viewingSecret.type) + '20', color: getTypeColor(viewingSecret.type) }">{{ getTypeLabel(viewingSecret.type) }}</span>
                                <span v-if="viewingSecret.category" class="badge bg-light text-dark"><i :class="viewingSecret.category.icon" class="me-1"></i>{{ viewingSecret.category.name }}</span>
                                <span v-if="viewingSecret.tags" class="badge bg-light text-dark"><i class="ti ti-tag me-1"></i>{{ viewingSecret.tags }}</span>
                            </div>

                            <div class="table-responsive mt-3">
                                <table class="table table-bordered mb-0">
                                    <tbody>
                                        <tr v-for="(value, key) in decryptedData" :key="key">
                                            <td class="fw-semibold text-capitalize bg-light" style="width:180px;">{{ key.replace(/_/g, ' ') }}</td>
                                            <td>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div style="flex:1; word-break:break-all; white-space:pre-wrap;">
                                                        <template v-if="isPasswordField(key) && !revealedFields['view_'+key]">
                                                            <span style="letter-spacing:3px;">••••••••••</span>
                                                        </template>
                                                        <template v-else>{{ value || '—' }}</template>
                                                    </div>
                                                    <div class="d-flex gap-1 ms-2" v-if="value">
                                                        <button v-if="isPasswordField(key)" @click="toggleReveal('view_'+key)" class="btn btn-sm btn-outline-secondary py-0 px-1" :title="revealedFields['view_'+key] ? 'Hide' : 'Show'">
                                                            <i :class="revealedFields['view_'+key] ? 'ti ti-eye-off' : 'ti ti-eye'" style="font-size:14px;"></i>
                                                        </button>
                                                        <button @click="copyToClipboard(value, key.replace(/_/g, ' '))" class="btn btn-sm btn-outline-primary py-0 px-1" title="Copy">
                                                            <i class="ti ti-copy" style="font-size:14px;"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3 text-muted" style="font-size:12px;">
                                <i class="ti ti-clock me-1"></i> Last accessed: {{ viewingSecret.last_accessed_at || 'Just now' }}
                                &nbsp;|&nbsp; Updated: {{ viewingSecret.updated_at }}
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button @click="openEditModal(viewingSecret); showViewModal=false;" class="btn btn-warning"><i class="ti ti-edit me-1"></i> Edit</button>
                        <button @click="showViewModal=false" class="btn btn-secondary">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Modal -->
        <div v-if="showCategoryModal" class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">New Category</h5>
                        <button @click="showCategoryModal=false" class="btn-close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input v-model="categoryForm.name" type="text" class="form-control" placeholder="e.g. Production" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Color</label>
                            <input v-model="categoryForm.color" type="color" class="form-control form-control-color" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button @click="showCategoryModal=false" class="btn btn-secondary btn-sm">Cancel</button>
                        <button @click="saveCategory" class="btn btn-primary btn-sm" :disabled="categoryForm.processing">Create</button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
