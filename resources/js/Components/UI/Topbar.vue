<script setup>
import { Link } from '@inertiajs/vue3';
import NotificationBell from './NotificationBell.vue';
</script>

<template>
    <!-- Topbar Start -->
    <header class="app-topbar">
        <div class="page-container topbar-menu">
            <div class="d-flex align-items-center gap-2">

                <!-- Brand Logo -->
                <Link :href="route('dashboard')" class="logo">
                    <span class="logo-light">
                        <span class="logo-lg"><img :src="$page.props.appLogo" alt="logo" :style="{ maxHeight: $page.props.appLogoHeight + 'px' }"></span>
                        <span class="logo-sm"><img :src="$page.props.appLogo" alt="small logo" style="max-height: 22px;"></span>
                    </span>

                    <span class="logo-dark">
                        <span class="logo-lg"><img :src="$page.props.appLogo" alt="dark logo" :style="{ maxHeight: $page.props.appLogoHeight + 'px' }"></span>
                        <span class="logo-sm"><img :src="$page.props.appLogo" alt="small logo" style="max-height: 22px;"></span>
                    </span>
                </Link>

                <!-- Sidebar Menu Toggle Button -->
                <button class="sidenav-toggle-button btn btn-secondary btn-icon">
                    <i class="ti ti-menu-deep fs-24"></i>
                </button>

                <!-- Button Trigger Search Modal -->
                <!-- <div class="topbar-search text-muted d-none d-xl-flex gap-2 align-items-center" data-bs-toggle="modal" data-bs-target="#searchModal" type="button">
                    <i class="ti ti-search fs-18"></i>
                    <span class="me-2">Search something..</span>
                    <button type="submit" class="ms-auto btn btn-sm btn-primary shadow-none">⌘K</button>
                </div> -->
            </div>

            <div class="d-flex align-items-center gap-2">

                <!-- Search for small devices -->
                <!-- <div class="topbar-item d-flex d-xl-none">
                    <button class="topbar-link btn btn-outline-primary btn-icon" data-bs-toggle="modal" data-bs-target="#searchModal" type="button">
                        <i class="ti ti-search fs-22"></i>
                    </button>
                </div> -->

                <!-- Notification Dropdown -->
                <NotificationBell />

                <!-- Light/Dark Mode Button -->
                <!-- <div class="topbar-item d-none d-sm-flex">
                    <button class="topbar-link btn btn-outline-primary btn-icon" id="light-dark-mode" type="button">
                        <i class="ti ti-moon fs-22"></i>
                    </button>
                </div> -->

                <!-- User Dropdown -->
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

                            <Link :href="route('logout')" method="post" as="button" class="dropdown-item fw-semibold text-danger w-100 text-start">
                                <i class="ti ti-logout me-1 fs-17 align-middle"></i>
                                <span class="align-middle">Sign Out</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>
