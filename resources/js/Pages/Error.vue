<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: Number,
        required: true
    }
});

const title = computed(() => {
    return {
        503: 'Service Unavailable',
        500: 'Server Error',
        404: 'Page Not Found',
        403: 'Access Forbidden',
    }[props.status] || 'Error';
});

const description = computed(() => {
    return {
        503: 'Maintenance in progress. Please check back soon.',
        500: 'Internal server error. Our team has been notified.',
        404: 'The page you are looking for has been moved or removed.',
        403: 'You do not have permission to view this resource.',
    }[props.status] || 'An unexpected error occurred.';
});
</script>

<template>
    <Head :title="title" />
    <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light p-4">
        <div class="card border-0 shadow-lg text-center p-5" style="max-width: 500px; border-radius: 1.5rem;">
            <div class="mb-4">
                <img :src="$page.props.appLogo" alt="Logo" :height="60" class="mx-auto">
            </div>
            <div class="mb-4">
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 80px; height: 80px;">
                    <i class="ti ti-lock-off fs-1" v-if="status === 403"></i>
                    <i class="ti ti-search fs-1" v-else-if="status === 404"></i>
                    <i class="ti ti-alert-triangle fs-1" v-else></i>
                </div>
            </div>
            <h1 class="display-3 fw-bold mb-2">{{ status }}</h1>
            <h3 class="fw-semibold mb-4 text-dark">{{ title }}</h3>
            <p class="text-muted mb-5 fs-5">{{ description }}</p>
            <Link :href="route('dashboard')" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm">
                Return Home
            </Link>
        </div>
    </div>
</template>
