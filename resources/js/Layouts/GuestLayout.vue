<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="auth-bg d-flex min-vh-100 justify-content-center align-items-center bg-light">
        <div class="row g-0 justify-content-center w-100 m-3">
            <div class="col-xl-4 col-lg-5 col-md-6">
                <div class="card overflow-hidden text-center h-100 p-xxl-4 p-3 mb-0 shadow-lg">
                    <div class="auth-brand mb-3">
                        <Link href="/" class="d-inline-block">
                            <img src="/assets/images/logo-dark.png" alt="dark logo" height="30" class="logo-dark">
                        </Link>
                    </div>
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
