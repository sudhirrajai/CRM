<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    description: '',
});

function submit() {
    form.post(route('marketing.lists.store'));
}
</script>

<template>
    <Head title="Create Mailing List" />
    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column mb-4 mt-2">
                    <div class="flex-grow-1">
                        <Link :href="route('marketing.lists.index')" class="text-muted fs-12 mb-1 d-block"><i class="ti ti-arrow-left me-1"></i> All Lists</Link>
                        <h4 class="fs-18 text-uppercase fw-bold m-0">Create Mailing List</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-xl-6 col-md-8">
                <div class="card border shadow-none mb-0">
                    <div class="card-header border-bottom border-dashed py-3 px-3">
                        <h5 class="card-title fs-16 mb-0">List Details</h5>
                    </div>
                    <div class="card-body">
                         <form @submit.prevent="submit">
                            <div class="mb-4">
                                <label class="form-label fs-11 text-uppercase fw-bold m-0">List Name</label>
                                <input v-model="form.name" type="text" class="form-control" placeholder="e.g. VIP Customers" required />
                                <div v-if="form.errors.name" class="text-danger fs-11 mt-1">{{ form.errors.name }}</div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fs-11 text-uppercase fw-bold m-0">Description</label>
                                <textarea v-model="form.description" class="form-control" rows="3" placeholder="Explain the purpose of this list..."></textarea>
                                <div v-if="form.errors.description" class="text-danger fs-11 mt-1">{{ form.errors.description }}</div>
                            </div>
                            <div class="mt-4 pt-3 border-top border-dashed d-flex gap-2">
                                <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm" :disabled="form.processing">Create List</button>
                                <Link :href="route('marketing.lists.index')" class="btn btn-light px-4">Cancel</Link>
                            </div>
                         </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
