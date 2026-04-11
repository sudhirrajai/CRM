<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Icon } from '@iconify/vue';

const props = defineProps({
    list: Object,
    subscribers: Array,
});

const showImport = ref(false);
const importForm = useForm({
    csv_file: null,
});

function handleFileUpload(e) {
    importForm.csv_file = e.target.files[0];
}

function submitImport() {
    importForm.post(route('marketing.lists.import', props.list.id), {
        onSuccess: () => { showImport.value = false; importForm.reset(); },
    });
}

function removeSubscriber(id) {
    if (confirm('Remove this subscriber from the list?')) {
        router.delete(route('marketing.lists.subscribers.destroy', { list: props.list.id, subscriber: id }));
    }
}
</script>

<template>
    <Head :title="list.name + ' Subscribers'" />
    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column mb-4 mt-2">
                    <div class="flex-grow-1">
                        <Link :href="route('marketing.lists.index')" class="text-muted fs-12 mb-1 d-block"><i class="ti ti-arrow-left me-1"></i> All Lists</Link>
                        <h4 class="fs-18 text-uppercase fw-bold m-0">{{ list.name }} <span class="text-muted fs-14 fw-normal">({{ list.subscribers_count }} total)</span></h4>
                    </div>
                    <div class="d-flex gap-2">
                        <button @click="showImport = !showImport" class="btn btn-primary btn-sm">
                            <i class="ti ti-file-import me-1"></i> Import CSV
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Import Panel -->
        <div v-if="showImport" class="card border shadow-none mb-3 animate-fade-in">
            <div class="card-header border-bottom border-dashed py-3">
                <h5 class="card-title fs-14 mb-0 text-uppercase fw-bold m-0 text-primary">Import Subscribers</h5>
            </div>
            <div class="card-body">
                <form @submit.prevent="submitImport" class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label fs-11 text-uppercase fw-bold m-0">CSV File</label>
                        <input type="file" class="form-control" accept=".csv" @change="handleFileUpload" required />
                        <small class="text-muted fs-10">Columns: email, first_name, last_name</small>
                    </div>
                    <div class="col-md-2">
                         <button type="submit" class="btn btn-primary w-100" :disabled="importForm.processing">Upload</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border shadow-none mb-0">
             <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom table-centered table-nowrap table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="fs-12 ps-3">Email Address</th>
                                <th class="fs-12">Name</th>
                                <th class="fs-12">Status</th>
                                <th class="fs-12">Source</th>
                                <th class="fs-12">Date Added</th>
                                <th class="fs-12 text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="sub in subscribers" :key="sub.id" :class="{ 'opacity-50': sub.unsubscribed_at }">
                                <td class="ps-3 fw-medium">{{ sub.email }}</td>
                                <td>{{ sub.first_name }} {{ sub.last_name }}</td>
                                <td>
                                    <span v-if="sub.unsubscribed_at" class="badge text-bg-light border text-danger fs-10">Unsubscribed</span>
                                    <span v-else class="badge text-bg-success border fs-10">Active</span>
                                </td>
                                <td><span class="badge text-bg-light border text-muted fs-10">{{ sub.source || 'Import' }}</span></td>
                                <td class="text-muted fs-11">{{ new Date(sub.created_at).toLocaleDateString() }}</td>
                                <td class="text-end pe-3">
                                    <button @click="removeSubscriber(sub.id)" class="btn btn-sm btn-icon btn-light text-danger" title="Remove Subscriber">
                                        <Icon icon="solar:trash-bin-trash-bold-duotone" />
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="subscribers.length === 0">
                                <td colspan="6" class="text-center py-5 text-muted">No subscribers yet for this list.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
             </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-fade-in { animation: fadeIn 0.3s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
</style>
