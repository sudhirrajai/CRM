<script setup>
import Sidebar from '@/Components/UI/Sidebar.vue';
import Topbar from '@/Components/UI/Topbar.vue';
import Footer from '@/Components/UI/Footer.vue';
import ThemeCustomizer from '@/Components/UI/ThemeCustomizer.vue';
import { onMounted, nextTick } from 'vue';

onMounted(() => {
    nextTick(() => {
        // Initialize theme JS if needed
        if (window.App) {
            try {
                const app = new window.App();
                app.init();
            } catch (e) {
                console.error("Failed to initialize App:", e);
            }
        }

        if (window.ThemeCustomizer) {
            try {
                const themeCustomizer = new window.ThemeCustomizer();
                themeCustomizer.init();
            } catch (e) {
                console.error("Failed to initialize ThemeCustomizer:", e);
            }
        }
    });
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
