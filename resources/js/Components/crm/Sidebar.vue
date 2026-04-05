<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="sidenav-menu">
        <Link :href="route('dashboard')" class="logo">
            <span class="logo-light">
                <span class="logo-lg"><img :src="$page.props.appLogo" alt="logo"></span>
                <span class="logo-sm text-center"><img :src="$page.props.appLogo" alt="small logo"></span>
            </span>
            <span class="logo-dark">
                <span class="logo-lg"><img :src="$page.props.appLogo" alt="dark logo"></span>
                <span class="logo-sm text-center"><img :src="$page.props.appLogo" alt="small logo"></span>
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

                <li v-if="$page.props.auth.permissions.includes('clients.view')" class="side-nav-item">
                    <Link :href="route('clients.index')" class="side-nav-link" :class="{ 'active': $page.component.startsWith('Clients/') }">
                        <span class="menu-icon"><i class="ti ti-users"></i></span>
                        <span class="menu-text"> Clients </span>
                    </Link>
                </li>

                <li v-if="$page.props.auth.permissions.includes('projects.view')" class="side-nav-item">
                    <Link :href="route('projects.index')" class="side-nav-link" :class="{ 'active': $page.component.startsWith('Projects/') }">
                        <span class="menu-icon"><i class="ti ti-case-round-minimalistic"></i></span>
                        <span class="menu-text"> Projects </span>
                    </Link>
                </li>

                <li v-if="$page.props.auth.permissions.includes('projects.view')" class="side-nav-item">
                    <Link :href="route('discussions.index')" class="side-nav-link" :class="{ 'active': $page.component.startsWith('Discussions/') }">
                        <span class="menu-icon"><i class="ti ti-messages"></i></span>
                        <span class="menu-text"> Discussions </span>
                    </Link>
                </li>

                <li v-if="$page.props.auth.permissions.includes('orders.view')" class="side-nav-item">
                    <Link :href="route('orders.index')" class="side-nav-link" :class="{ 'active': $page.component.startsWith('Orders/') }">
                        <span class="menu-icon"><i class="ti ti-basket"></i></span>
                        <span class="menu-text"> Orders </span>
                    </Link>
                </li>

                <li v-if="$page.props.auth.permissions.includes('invoices.view')" class="side-nav-item">
                    <Link :href="route('invoices.index')" class="side-nav-link" :class="{ 'active': $page.component.startsWith('Invoices/') }">
                        <span class="menu-icon"><i class="ti ti-file-invoice"></i></span>
                        <span class="menu-text"> Invoices </span>
                    </Link>
                </li>

                <li v-if="$page.props.auth.permissions.includes('expenses.view') || $page.props.auth.permissions.includes('reports.view')" class="side-nav-title mt-2">Accounting</li>

                <li v-if="$page.props.auth.permissions.includes('expenses.view')" class="side-nav-item">
                    <Link :href="route('expenses.index')" class="side-nav-link" :class="{ 'active': $page.component.startsWith('Expenses/Index') }">
                        <span class="menu-icon"><i class="ti ti-receipt"></i></span>
                        <span class="menu-text"> Expenses </span>
                    </Link>
                </li>

                <li v-if="$page.props.auth.permissions.includes('expense_categories.view')" class="side-nav-item">
                    <Link :href="route('expense-categories.index')" class="side-nav-link" :class="{ 'active': $page.component.startsWith('Expenses/Categories') }">
                        <span class="menu-icon"><i class="ti ti-category"></i></span>
                        <span class="menu-text"> Categories </span>
                    </Link>
                </li>

                <li v-if="$page.props.auth.permissions.includes('reports.view')" class="side-nav-item">
                    <Link :href="route('reports.balance-sheet')" class="side-nav-link" :class="{ 'active': $page.component.startsWith('Reports/BalanceSheet') }">
                        <span class="menu-icon"><i class="ti ti-report-analytics"></i></span>
                        <span class="menu-text"> Balance Sheet </span>
                    </Link>
                </li>

                <li v-if="$page.props.auth.permissions.includes('users.view') || $page.props.auth.permissions.includes('roles.view') || $page.props.auth.permissions.includes('settings.view')" class="side-nav-title mt-2">Administration</li>

                <li v-if="$page.props.auth.permissions.includes('users.view')" class="side-nav-item" :class="{ 'active': $page.component.startsWith('Users/') }">
                    <Link :href="route('users.index')" class="side-nav-link" :class="{ 'active': $page.component.startsWith('Users/') }">
                        <span class="menu-icon"><i class="ti ti-users-group"></i></span>
                        <span class="menu-text"> Users </span>
                    </Link>
                </li>

                <li v-if="$page.props.auth.permissions.includes('roles.view')" class="side-nav-item">
                    <Link :href="route('roles.index')" class="side-nav-link" :class="{ 'active': $page.component.startsWith('Roles/') }">
                        <span class="menu-icon"><i class="ti ti-shield"></i></span>
                        <span class="menu-text"> Roles & Permissions </span>
                    </Link>
                </li>

                <li v-if="$page.props.auth.permissions.includes('settings.view')" class="side-nav-item">
                    <Link :href="route('settings.index')" class="side-nav-link" :class="{ 'active': $page.component.startsWith('Settings/') }">
                        <span class="menu-icon"><i class="ti ti-settings"></i></span>
                        <span class="menu-text"> Settings </span>
                    </Link>
                </li>
            </ul>
        </div>
    </div>
</template>
