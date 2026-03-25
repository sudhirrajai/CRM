<script setup>
import Sidebar from '@/Components/UI/Sidebar.vue';
import Topbar from '@/Components/UI/Topbar.vue';
import Footer from '@/Components/UI/Footer.vue';
import ThemeCustomizer from '@/Components/UI/ThemeCustomizer.vue';
import { onMounted } from 'vue';

onMounted(() => {
    // Initialize theme JS if needed
    if (window.App) {
        window.App.init();
    }
});
</script>

<template>
    <div class="wrapper">
        <Sidebar />
        <Topbar />
        
        <div class="page-content">
            <div class="page-container">
                <slot />
            </div>

            <Footer />
        </div>

        <ThemeCustomizer />
    </div>
</template>
