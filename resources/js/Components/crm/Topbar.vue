<script setup>
import { Link, useForm } from '@inertiajs/vue3';

const logout = () => {
    useForm({}).post(route('logout'));
};
</script>

<template>
    <header class="app-topbar">
        <div class="page-container topbar-menu">
            <div class="d-flex align-items-center gap-2">
                <Link :href="route('dashboard')" class="logo d-flex d-xl-none">
                    <span class="logo-light">
                        <span class="logo-sm"><img src="/assets/images/vmcore.png" alt="small logo"></span>
                    </span>
                    <span class="logo-dark">
                        <span class="logo-sm"><img src="/assets/images/vmcore.png" alt="small logo"></span>
                    </span>
                </Link>

                <button class="sidenav-toggle-button btn btn-secondary btn-icon">
                    <i class="ti ti-menu-deep fs-24"></i>
                </button>

                <div class="topbar-search text-muted d-none d-xl-flex gap-2 align-items-center" data-bs-toggle="modal" data-bs-target="#searchModal" type="button">
                    <i class="ti ti-search fs-18"></i>
                    <span class="me-2">Search something..</span>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <div class="topbar-item d-none d-sm-flex">
                    <button class="topbar-link btn btn-outline-primary btn-icon" id="light-dark-mode" type="button">
                        <i class="ti ti-moon fs-22"></i>
                    </button>
                </div>

                <div class="topbar-item">
                    <div class="dropdown">
                        <a class="topbar-link btn btn-outline-primary dropdown-toggle drop-arrow-none" data-bs-toggle="dropdown" data-bs-offset="0,22" type="button" aria-haspopup="false" aria-expanded="false">
                            <img src="/assets/images/users/avatar-1.jpg" width="24" class="rounded-circle me-lg-2 d-flex" alt="user-image">
                            <span class="d-lg-flex flex-column gap-1 d-none">
                                {{ $page.props.auth.user.name }}
                            </span>
                            <i class="ti ti-chevron-down d-none d-lg-block align-middle ms-2"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <div class="dropdown-header noti-title">
                                <h6 class="text-overflow m-0">Welcome !</h6>
                            </div>
                            <Link :href="route('profile.edit')" class="dropdown-item">
                                <i class="ti ti-user-hexagon me-1 fs-17 align-middle"></i>
                                <span class="align-middle">My Account</span>
                            </Link>
                            <div class="dropdown-divider"></div>
                            <button @click="logout" class="dropdown-item active fw-semibold text-danger w-100 text-start">
                                <i class="ti ti-logout me-1 fs-17 align-middle"></i>
                                <span class="align-middle">Sign Out</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>
