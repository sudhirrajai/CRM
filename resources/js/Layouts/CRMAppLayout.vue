<script setup>
import Sidebar from '@/Components/crm/Sidebar.vue';
import Topbar from '@/Components/crm/Topbar.vue';
import { onMounted } from 'vue';

// Initialize template JS if needed
onMounted(() => {
    // When the component is mounted, we may need to trigger layout scripts
    // or initialize tooltips/popovers if the static app.js doesn't do it automatically for dynamic content.
    if (window.App) {
        window.App.init();
    }
});
</script>

<template>
    <div class="wrapper">
        <Sidebar />
        
        <div class="page-content">
            <Topbar />
            
            <div class="page-container">
                <slot />
            </div>

            <footer class="footer">
                <div class="page-container">
                    <div class="row">
                        <div class="col-md-6 text-center text-md-start">
                            {{ new Date().getFullYear() }} © CRM - Developed by Antigravity
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</template>

<style>
/* Adjustments for Inertia/Vue layout if needed */
.page-content {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}
.footer {
    margin-top: auto;
}
</style>
