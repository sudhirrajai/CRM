<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="sidenav-menu">
        <Link :href="route('dashboard')" class="logo">
            <span class="logo-light">
                <span class="logo-lg"><img src="/assets/images/logo.png" alt="logo"></span>
                <span class="logo-sm text-center"><img src="/assets/images/logo-sm.png" alt="small logo"></span>
            </span>
            <span class="logo-dark">
                <span class="logo-lg"><img src="/assets/images/logo-dark.png" alt="dark logo"></span>
                <span class="logo-sm text-center"><img src="/assets/images/logo-sm.png" alt="small logo"></span>
            </span>
        </Link>

        <button class="button-sm-hover">
            <i class="ti ti-circle align-middle"></i>
        </button>

        <button class="button-close-fullsidebar">
            <i class="ti ti-x align-middle"></i>
        </button>

        <div data-simplebar>
            <ul class="side-nav">
                <li class="side-nav-item">
                    <Link :href="route('dashboard')" class="side-nav-link" :class="{ 'active': $page.component === 'Dashboard' }">
                        <span class="menu-icon"><i class="ti ti-dashboard"></i></span>
                        <span class="menu-text"> Dashboard </span>
                    </Link>
                </li>

                <li class="side-nav-title mt-2">CRM Modules</li>

                <li class="side-nav-item">
                    <Link :href="route('clients.index')" class="side-nav-link" :class="{ 'active': $page.component.startsWith('Clients/') }">
                        <span class="menu-icon"><i class="ti ti-users"></i></span>
                        <span class="menu-text"> Clients </span>
                    </Link>
                </li>

                <li class="side-nav-item">
                    <Link :href="route('projects.index')" class="side-nav-link" :class="{ 'active': $page.component.startsWith('Projects/') }">
                        <span class="menu-icon"><i class="ti ti-case-round-minimalistic"></i></span>
                        <span class="menu-text"> Projects </span>
                    </Link>
                </li>

                <li class="side-nav-item">
                    <Link :href="route('orders.index')" class="side-nav-link" :class="{ 'active': $page.component.startsWith('Orders/') }">
                        <span class="menu-icon"><i class="ti ti-basket"></i></span>
                        <span class="menu-text"> Orders </span>
                    </Link>
                </li>

                <li class="side-nav-item">
                    <Link :href="route('invoices.index')" class="side-nav-link" :class="{ 'active': $page.component.startsWith('Invoices/') }">
                        <span class="menu-icon"><i class="ti ti-file-invoice"></i></span>
                        <span class="menu-text"> Invoices </span>
                    </Link>
                </li>

                <li class="side-nav-title mt-2">Administration</li>

                <li class="side-nav-item">
                    <Link :href="route('users.index')" class="side-nav-link" :class="{ 'active': $page.component.startsWith('Users/') }">
                        <span class="menu-icon"><i class="ti ti-lock"></i></span>
                        <span class="menu-text"> Users & Roles </span>
                    </Link>
                </li>
            </ul>
        </div>
    </div>
</template>
