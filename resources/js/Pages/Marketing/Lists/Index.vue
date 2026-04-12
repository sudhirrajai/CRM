<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, Head, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';

const props = defineProps({
    lists: Array,
});

function deleteList(id) {
    if (confirm('Delete this mailing list and all its subscribers?')) {
        router.delete(route('marketing.lists.destroy', id));
    }
}
</script>

<template>
    <Head title="Mailing Lists" />
    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column mb-4 mt-2">
                    <div class="flex-grow-1">
                        <h4 class="fs-18 text-uppercase fw-bold m-0">Mailing Lists</h4>
                    </div>
                    <div class="d-flex gap-2">
                        <Link :href="route('marketing.lists.create')" class="btn btn-primary btn-sm">
                            <i class="ti ti-plus me-1"></i> New List
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div v-for="list in lists" :key="list.id" class="col-xl-4 col-md-6">
                <div class="card border shadow-none h-100 mb-0">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div>
                                <h5 class="card-title fs-16 mb-1">{{ list.name }}</h5>
                                <p v-if="list.description" class="text-muted fs-11 mb-2">{{ list.description }}</p>
                            </div>
                            <span v-if="list.is_dynamic" class="badge text-bg-info border border-info-subtle">Dynamic</span>
                        </div>

                        <div class="d-flex gap-3 mb-4">
                            <div class="text-center">
                                <div class="fw-bold fs-4 text-primary line-height-1 mb-1">{{ list.subscribers_count }}</div>
                                <div class="text-muted fs-11 text-uppercase fw-semibold letter-spacing-1">Total</div>
                            </div>
                            <div class="text-center">
                                <div class="fw-bold fs-4 text-success line-height-1 mb-1">{{ list.active_subscribers_count }}</div>
                                <div class="text-muted fs-11 text-uppercase fw-semibold letter-spacing-1">Active</div>
                            </div>
                            <div class="text-center">
                                <div class="fw-bold fs-4 text-danger line-height-1 mb-1">{{ list.subscribers_count - list.active_subscribers_count }}</div>
                                <div class="text-muted fs-11 text-uppercase fw-semibold letter-spacing-1">Inactive</div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <Link :href="route('marketing.lists.show', list.id)" class="btn btn-sm btn-icon btn-light flex-fill" title="View Subscribers">
                                <Icon icon="solar:eye-bold-duotone" class="me-1" /> View
                            </Link>
                            <Link :href="route('marketing.lists.edit', list.id)" class="btn btn-sm btn-icon btn-light" title="Edit Properties">
                                <Icon icon="solar:pen-new-square-bold-duotone" />
                            </Link>
                            <button @click="deleteList(list.id)" class="btn btn-sm btn-icon btn-light text-danger" title="Delete List">
                                <Icon icon="solar:trash-bin-trash-bold-duotone" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="lists.length === 0" class="col-12 text-center py-5 text-muted">
                <Icon icon="solar:users-group-rounded-bold-duotone" class="fs-48 opacity-25 mb-2" />
                <p class="mb-0">No mailing lists found. Build your audience to start sending!</p>
                <Link :href="route('marketing.lists.create')" class="btn btn-sm btn-primary mt-2">Create First List</Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.line-height-1 { line-height: 1; }
.letter-spacing-1 { letter-spacing: 0.5px; }
</style>
