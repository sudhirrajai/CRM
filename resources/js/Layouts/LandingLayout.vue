<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { Hexagon, Menu, X } from 'lucide-vue-next';

const page = usePage();
const appName = computed(() => page.props.appName || 'VMCore');
const mobileOpen = ref(false);
const scrolled = ref(false);

function onScroll() {
    scrolled.value = window.scrollY > 10;
}

onMounted(() => {
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
});

onUnmounted(() => window.removeEventListener('scroll', onScroll));
</script>

<template>
    <div class="min-h-screen bg-white text-zinc-950 font-sans antialiased selection:bg-boron-100 selection:text-boron-900 flex flex-col items-center" style="font-family: 'Geist', sans-serif;">
        
        <!-- NAV -->
        <nav
            class="fixed top-0 left-0 right-0 z-50 h-[60px] flex items-center justify-center transition-shadow duration-300"
            :class="scrolled
                ? 'bg-white/90 backdrop-blur shadow-sm border-b border-zinc-200'
                : 'bg-white border-b border-transparent'"
        >
            <div class="w-full max-w-5xl px-10 flex items-center justify-between">
                <!-- Logo -->
                <Link href="/" class="flex items-center gap-2 cursor-pointer">
                    <div class="w-8 h-8 bg-zinc-950 rounded flex items-center justify-center text-white">
                        <Hexagon :size="18" fill="currentColor" />
                    </div>
                    <span class="font-semibold text-zinc-950 text-lg tracking-tight">{{ appName }}</span>
                </Link>

                <!-- Desktop Links -->
                <div class="hidden md:flex items-center gap-1">
                    <a v-for="link in ['Features', 'Pricing', 'Customers', 'Docs', 'Blog']" 
                       :key="link"
                       :href="`#${link.toLowerCase()}`" 
                       class="text-sm font-medium text-zinc-500 hover:text-zinc-950 hover:bg-zinc-100 rounded-lg px-3 py-1.5 transition-colors cursor-pointer"
                       style="text-decoration: none;"
                    >
                        {{ link }}
                    </a>
                </div>

                <!-- Desktop CTA -->
                <div class="hidden sm:flex items-center gap-3">
                    <Link :href="route('login')" class="text-sm font-medium text-zinc-500 hover:text-zinc-950 px-3 py-1.5 transition-colors cursor-pointer" style="text-decoration: none;">
                        Log in
                    </Link>
                    <Link :href="route('login')" class="text-sm font-medium text-white bg-zinc-950 hover:bg-zinc-800 rounded-lg px-4 py-2 transition-colors cursor-pointer shadow-sm border-0" style="text-decoration: none;">
                        Start for free &rarr;
                    </Link>
                </div>

                <!-- Mobile Toggle -->
                <button @click="mobileOpen = !mobileOpen" class="md:hidden text-zinc-500 hover:text-zinc-900 p-1 border-0 bg-transparent">
                    <X v-if="mobileOpen" :size="20" />
                    <Menu v-else :size="20" />
                </button>
            </div>

            <!-- Mobile dropdown -->
            <div v-show="mobileOpen" class="absolute top-[60px] left-0 w-full md:hidden bg-white border-b border-zinc-200 px-5 pb-5 pt-3 shadow-lg flex flex-col gap-1">
                <a v-for="link in ['Features', 'Pricing', 'Customers', 'Docs', 'Blog']" 
                   :key="link"
                   :href="`#${link.toLowerCase()}`" 
                   @click="mobileOpen = false"
                   class="block py-2.5 text-sm font-medium text-zinc-600 hover:text-zinc-950 transition-colors"
                   style="text-decoration: none;"
                >
                    {{ link }}
                </a>
                <div class="pt-3 border-t border-zinc-100 flex flex-col gap-2">
                    <Link :href="route('login')" class="text-center py-2.5 text-sm font-medium text-zinc-600 border border-zinc-200 rounded-lg transition-colors" style="text-decoration: none;">Log in</Link>
                    <Link :href="route('login')" class="text-center py-2.5 text-sm font-medium text-white bg-zinc-950 rounded-lg hover:bg-zinc-800 transition-colors border-0" style="text-decoration: none;">Start for free &rarr;</Link>
                </div>
            </div>
        </nav>

        <!-- MAIN -->
        <main class="w-full flex flex-col items-center flex-1">
            <slot />
        </main>

        <!-- FOOTER -->
        <footer class="w-full bg-white border-t border-zinc-200 pt-16 pb-8 mt-auto">
            <div class="max-w-5xl mx-auto px-10">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-12 md:gap-8 mb-16">
                    <!-- Brand -->
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 bg-zinc-950 rounded flex items-center justify-center text-white">
                                <Hexagon :size="14" fill="currentColor" />
                            </div>
                            <span class="font-semibold text-zinc-950 text-base tracking-tight">{{ appName }}</span>
                        </div>
                        <p class="text-sm text-zinc-500 max-w-[200px] m-0">
                            The modern CRM built for growing teams and agencies. Focus on closing deals, not spreadsheets.
                        </p>
                    </div>

                    <!-- Links 1 -->
                    <div>
                        <h4 class="font-medium text-zinc-950 mb-4 text-[13px] uppercase tracking-wider">Product</h4>
                        <ul class="flex flex-col gap-3 m-0 p-0 list-none">
                            <li v-for="link in ['Features', 'Integrations', 'Pricing', 'Changelog', 'Roadmap']" :key="link">
                                <a href="#" class="text-sm text-zinc-500 hover:text-zinc-950 transition-colors" style="text-decoration: none;">
                                    {{ link }}
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Links 2 -->
                    <div>
                        <h4 class="font-medium text-zinc-950 mb-4 text-[13px] uppercase tracking-wider">Support</h4>
                        <ul class="flex flex-col gap-3 m-0 p-0 list-none">
                            <li v-for="link in ['Documentation', 'API Reference', 'Help Center', 'Community', 'Status']" :key="link">
                                <a href="#" class="text-sm text-zinc-500 hover:text-zinc-950 transition-colors" style="text-decoration: none;">
                                    {{ link }}
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Links 3 -->
                    <div>
                        <h4 class="font-medium text-zinc-950 mb-4 text-[13px] uppercase tracking-wider">Company</h4>
                        <ul class="flex flex-col gap-3 m-0 p-0 list-none">
                            <li v-for="link in ['About', 'Customers', 'Blog', 'Careers', 'Contact']" :key="link">
                                <a href="#" class="text-sm text-zinc-500 hover:text-zinc-950 transition-colors" style="text-decoration: none;">
                                    {{ link }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Bottom -->
                <div class="flex flex-col md:flex-row items-center justify-between pt-8 border-t border-zinc-200">
                    <p class="text-sm text-zinc-500 mb-4 md:mb-0 m-0">
                        &copy; {{ new Date().getFullYear() }} {{ appName }} Inc. All rights reserved.
                    </p>
                    <div class="flex items-center gap-6">
                        <a href="#" class="text-sm text-zinc-500 hover:text-zinc-900 transition-colors" style="text-decoration: none;">Privacy</a>
                        <a href="#" class="text-sm text-zinc-500 hover:text-zinc-900 transition-colors" style="text-decoration: none;">Terms</a>
                        <a href="#" class="text-sm text-zinc-500 hover:text-zinc-900 transition-colors" style="text-decoration: none;">Security</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<style>
/* CSS Transitions for scroll reveal matching framer-motion defaults */
.reveal {
    opacity: 0;
    transform: perspective(1000px) translateY(40px) rotateX(-8deg) scale(0.96);
    transform-origin: top center;
    transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
}
.reveal.visible {
    opacity: 1;
    transform: perspective(1000px) translateY(0) rotateX(0deg) scale(1);
}
.reveal-delay-1 { transition-delay: 0.1s; }
.reveal-delay-2 { transition-delay: 0.2s; }
.reveal-delay-3 { transition-delay: 0.3s; }
.reveal-delay-4 { transition-delay: 0.4s; }
.reveal-delay-5 { transition-delay: 0.5s; }
.reveal-delay-6 { transition-delay: 0.6s; }

/* Reset default margins on h1-h6 and p from app bootstrap */
h1, h2, h3, h4, h5, h6, p {
    margin-top: 0;
}
</style>
