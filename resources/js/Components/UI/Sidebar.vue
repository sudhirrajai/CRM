<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

// For handling menu state if needed
const activeMenu = ref(null);
</script>

<template>
    <!-- Sidenav Menu Start -->
    <div class="sidenav-menu">

        <!-- Brand Logo -->
        <Link :href="route('dashboard')" class="logo">
            <span class="logo-light">
                <span class="logo-lg"><img :src="$page.props.menuLogo" alt="logo" :style="{ maxHeight: $page.props.menuLogoHeight + 'px', width: 'auto', objectFit: 'contain' }"></span>
                <span class="logo-sm text-center"><img :src="$page.props.menuLogo" alt="small logo" style="max-height: 22px; width: auto; object-fit: contain;"></span>
            </span>

            <span class="logo-dark">
                <span class="logo-lg"><img :src="$page.props.menuLogo" alt="dark logo" :style="{ maxHeight: $page.props.menuLogoHeight + 'px', width: 'auto', objectFit: 'contain' }"></span>
                <span class="logo-sm text-center"><img :src="$page.props.menuLogo" alt="small logo" style="max-height: 22px; width: auto; object-fit: contain;"></span>
            </span>
        </Link>

        <!-- Sidebar Hover Menu Toggle Button -->
        <button class="button-sm-hover">
            <i class="ti ti-circle align-middle"></i>
        </button>

        <!-- Full Sidebar Menu Close Button -->
        <button class="button-close-fullsidebar">
            <i class="ti ti-x align-middle"></i>
        </button>

        <div data-simplebar>

            <!--- Sidenav Menu -->
            <ul class="side-nav">

                <li class="side-nav-item" :class="{ 'active': $page.component === 'Dashboard' }">
                    <Link :href="route('dashboard')" class="side-nav-link" :class="{ 'active': $page.component === 'Dashboard' }">
                        <span class="menu-icon"><i class="ti ti-dashboard"></i></span>
                        <span class="menu-text"> Dashboard </span>
                    </Link>
                </li>

                <li class="side-nav-title mt-2 text-uppercase fs-12">CRM Modules</li>

                <li class="side-nav-item" :class="{ 'active': $page.component.startsWith('Clients') }" v-if="$page.props.auth.roles.includes('admin') || $page.props.auth.roles.includes('staff')">
                    <Link :href="route('clients.index')" class="side-nav-link" :class="{ 'active': $page.component.startsWith('Clients') }">
                        <span class="menu-icon"><i class="ti ti-users"></i></span>
                        <span class="menu-text"> Clients </span>
                    </Link>
                </li>
                
                <li class="side-nav-item" :class="{ 'active': $page.component.startsWith('Projects') }">
                    <Link :href="route('projects.index')" class="side-nav-link" :class="{ 'active': $page.component.startsWith('Projects') }">
                        <span class="menu-icon"><i class="ti ti-briefcase"></i></span>
                        <span class="menu-text"> Projects </span>
                    </Link>
                </li>

                <li class="side-nav-item" :class="{ 'active': $page.component.startsWith('Invoices') }">
                    <Link :href="route('invoices.index')" class="side-nav-link" :class="{ 'active': $page.component.startsWith('Invoices') }">
                        <span class="menu-icon"><i class="ti ti-file-invoice"></i></span>
                        <span class="menu-text"> Invoices </span>
                    </Link>
                </li>

                <li class="side-nav-title mt-2 text-uppercase fs-12" v-if="$page.props.auth.roles.includes('admin') || $page.props.auth.roles.includes('staff')">Accounting</li>

                <li class="side-nav-item" :class="{ 'active': $page.component.startsWith('Expenses/Index') }" v-if="$page.props.auth.roles.includes('admin') || $page.props.auth.roles.includes('staff')">
                    <Link :href="route('expenses.index')" class="side-nav-link" :class="{ 'active': $page.component.startsWith('Expenses/Index') }">
                        <span class="menu-icon"><i class="ti ti-receipt"></i></span>
                        <span class="menu-text"> Expenses </span>
                    </Link>
                </li>

                <li class="side-nav-item" :class="{ 'active': $page.component.startsWith('Expenses/Categories') }" v-if="$page.props.auth.roles.includes('admin') || $page.props.auth.roles.includes('staff')">
                    <Link :href="route('expense-categories.index')" class="side-nav-link" :class="{ 'active': $page.component.startsWith('Expenses/Categories') }">
                        <span class="menu-icon"><i class="ti ti-category"></i></span>
                        <span class="menu-text"> Categories </span>
                    </Link>
                </li>

                <li class="side-nav-item" :class="{ 'active': $page.component.startsWith('Reports/BalanceSheet') }" v-if="$page.props.auth.roles.includes('admin') || $page.props.auth.roles.includes('staff')">
                    <Link :href="route('reports.balance-sheet')" class="side-nav-link" :class="{ 'active': $page.component.startsWith('Reports/BalanceSheet') }">
                        <span class="menu-icon"><i class="ti ti-report-analytics"></i></span>
                        <span class="menu-text"> Balance Sheet </span>
                    </Link>
                </li>

                <li class="side-nav-title mt-2 text-uppercase fs-12" v-if="$page.props.auth.roles.includes('admin') || $page.props.auth.roles.includes('staff')">Infrastructure</li>

                <li class="side-nav-item" :class="{ 'active': $page.component.startsWith('Servers') }" v-if="$page.props.auth.roles.includes('admin') || $page.props.auth.roles.includes('staff')">
                    <Link :href="route('servers.index')" class="side-nav-link" :class="{ 'active': $page.component.startsWith('Servers') }">
                        <span class="menu-icon"><i class="ti ti-server"></i></span>
                        <span class="menu-text"> Servers </span>
                    </Link>
                </li>

                <li class="side-nav-item" :class="{ 'active': $page.component.startsWith('Hostings') }">
                    <Link :href="route('hostings.index')" class="side-nav-link" :class="{ 'active': $page.component.startsWith('Hostings') }">
                        <span class="menu-icon"><i class="ti ti-cloud-network"></i></span>
                        <span class="menu-text"> Hosting / VPS </span>
                    </Link>
                </li>

                <li v-if="$page.props.auth.permissions.includes('users.view') || $page.props.auth.permissions.includes('roles.view') || $page.props.auth.permissions.includes('settings.view')" class="side-nav-title mt-2 text-uppercase fs-12">Administration</li>

                <li v-if="$page.props.auth.permissions.includes('users.view')" class="side-nav-item" :class="{ 'active': $page.component.startsWith('Users/') }">
                    <Link :href="route('users.index')" class="side-nav-link" :class="{ 'active': $page.component.startsWith('Users/') }">
                        <span class="menu-icon"><i class="ti ti-users-group"></i></span>
                        <span class="menu-text"> Users </span>
                    </Link>
                </li>

                <li v-if="$page.props.auth.permissions.includes('roles.view')" class="side-nav-item" :class="{ 'active': $page.component.startsWith('Roles/') }">
                    <Link :href="route('roles.index')" class="side-nav-link" :class="{ 'active': $page.component.startsWith('Roles/') }">
                        <span class="menu-icon"><i class="ti ti-shield"></i></span>
                        <span class="menu-text"> Roles & Permissions </span>
                    </Link>
                </li>

                <li v-if="$page.props.auth.permissions.includes('settings.view')" class="side-nav-item" :class="{ 'active': $page.component.startsWith('Settings/') }">
                    <Link :href="route('settings.index')" class="side-nav-link" :class="{ 'active': $page.component.startsWith('Settings/') }">
                        <span class="menu-icon"><i class="ti ti-settings"></i></span>
                        <span class="menu-text"> Settings </span>
                    </Link>
                </li>
            </ul>
        </div>
    </div>
</template>
