<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Icon } from '@iconify/vue';

const props = defineProps({
    templates: Array,
    categories: Array,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const selectedCategory = ref(props.filters?.category || '');

const filteredTemplates = computed(() => {
    let items = props.templates || [];
    if (search.value) {
        const q = search.value.toLowerCase();
        items = items.filter(t => t.name.toLowerCase().includes(q) || t.subject.toLowerCase().includes(q));
    }
    if (selectedCategory.value) {
        items = items.filter(t => t.category === selectedCategory.value);
    }
    return items;
});

function deleteTemplate(id) {
    if (confirm('Are you sure you want to delete this template?')) {
        router.delete(route('marketing.templates.destroy', id));
    }
}

function duplicateTemplate(id) {
    router.post(route('marketing.templates.duplicate', id));
}

function getEditorBadge(type) {
    return type === 'dragdrop'
        ? { label: 'Drag & Drop', class: 'text-bg-info border border-info-subtle' }
        : { label: 'Rich Text', class: 'text-bg-primary border border-primary-subtle' };
}
</script>

<template>
    <Head title="Email Templates" />
    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column mb-4 mt-2">
                    <div class="flex-grow-1">
                        <h4 class="fs-18 text-uppercase fw-bold m-0">Email Templates</h4>
                    </div>
                    <div class="d-flex gap-2">
                        <Link :href="route('marketing.templates.create')" class="btn btn-primary btn-sm">
                            <i class="ti ti-plus me-1"></i> New Template
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card border shadow-none mb-3">
            <div class="card-body py-3 px-3">
                <div class="row g-2 align-items-center">
                    <div class="col-md-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-transparent border-end-0 border-dashed"><i class="ti ti-search text-muted"></i></span>
                            <input v-model="search" type="text" class="form-control border-start-0 border-dashed ps-0" placeholder="Search templates..." />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select v-model="selectedCategory" class="form-select form-select-sm border-dashed">
                            <option value="">All Categories</option>
                            <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Templates Grid -->
        <div class="row g-3">
            <div v-for="template in filteredTemplates" :key="template.id" class="col-xl-4 col-md-6">
                <div class="card border shadow-none h-100 mb-0 template-card">
                    <!-- Preview Area -->
                    <div class="position-relative" style="height: 160px; background: #f0f3fb; overflow: hidden; border-bottom: 1px dashed #e1e7f6;">
                        <div v-if="template.html_content" class="w-100 h-100" style="transform: scale(0.3); transform-origin: top left; width: 333%; pointer-events: none;" v-html="template.html_content"></div>
                        <div v-else class="d-flex align-items-center justify-content-center h-100 text-muted">
                            <Icon icon="solar:plate-bold-duotone" class="fs-48 opacity-25" />
                        </div>
                        <div class="position-absolute top-0 end-0 m-2">
                            <span class="badge fs-10" :class="getEditorBadge(template.editor_type).class">
                                {{ getEditorBadge(template.editor_type).label }}
                            </span>
                        </div>
                    </div>

                    <div class="card-body p-3">
                        <h6 class="card-title fs-14 mb-1 text-truncate">{{ template.name }}</h6>
                        <p class="text-muted fs-11 mb-2 text-truncate">{{ template.subject || 'No Subject' }}</p>
                        <div class="d-flex gap-1">
                            <span v-if="template.category" class="badge text-bg-light border fs-10">{{ template.category }}</span>
                        </div>
                    </div>

                    <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-3">
                        <div class="d-flex gap-1 justify-content-end">
                            <Link :href="route('marketing.templates.edit', template.id)" class="btn btn-sm btn-icon btn-light" title="Edit Template">
                                <Icon icon="solar:pen-new-square-bold-duotone" />
                            </Link>
                            <button @click="duplicateTemplate(template.id)" class="btn btn-sm btn-icon btn-light" title="Duplicate">
                                <Icon icon="solar:copy-bold-duotone" />
                            </button>
                            <button @click="deleteTemplate(template.id)" class="btn btn-sm btn-icon btn-light text-danger" title="Delete">
                                <Icon icon="solar:trash-bin-trash-bold-duotone" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="filteredTemplates.length === 0" class="col-12 text-center py-5 text-muted">
                <Icon icon="solar:palette-bold-duotone" class="fs-48 opacity-25 mb-2" />
                <p class="mb-0">No email templates found. Create one to start designing your emails!</p>
                <Link :href="route('marketing.templates.create')" class="btn btn-sm btn-primary mt-2">Design Template</Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.template-card:hover { border-color: #3e60d5 !important; }
</style>
