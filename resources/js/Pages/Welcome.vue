<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>

<template>
    <Head title="Welcome to VMCore CRM" />

    <div class="min-vh-100 bg-white text-dark d-flex flex-column font-sans" style="font-family: 'Inter', sans-serif; overflow-x: hidden;">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg sticky-top bg-white/80 backdrop-blur-md border-bottom py-3">
            <div class="container d-flex justify-content-between align-items-center">
                <Link :href="route('dashboard')" class="navbar-brand d-flex align-items-center gap-2">
                    <img src="/assets/images/vmcore.png" alt="VMCore Logo" height="32">
                    <span class="fw-bold fs-4 text-primary">VMCore</span>
                </Link>

                <div class="d-flex align-items-center gap-3">
                    <template v-if="$page.props.auth.user">
                        <Link :href="route('dashboard')" class="btn btn-primary rounded-pill px-4 shadow-sm hover:scale-105 transition-all">
                            Dashboard
                        </Link>
                    </template>
                    <template v-else>
                        <Link :href="route('login')" class="btn btn-outline-primary rounded-pill px-4 hover:bg-primary hover:text-white transition-all">
                            Login
                        </Link>
                        <Link v-if="canRegister" :href="route('register')" class="btn btn-primary rounded-pill px-4 shadow-sm hover:scale-105 transition-all">
                            Get Started
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <header class="py-5 py-lg-10 position-relative overflow-hidden bg-light">
            <div class="container text-center pt-10">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 mb-4 animate-bounce">
                            New: Professional Invoicing System
                        </span>
                        <h1 class="display-3 fw-900 text-dark mb-4 tracking-tight">
                            Elevate Your Business with <span class="text-primary gradient-text">VMCore CRM</span>
                        </h1>
                        <p class="lead text-muted mb-5 fs-5 px-lg-5">
                            The all-in-one platform to manage clients, track projects, automate invoicing, and monitor hosting allocations. Designed for modern professional services.
                        </p>
                        <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                            <Link v-if="!$page.props.auth.user" :href="route('register')" class="btn btn-primary btn-lg rounded-pill px-5 py-3 shadow-lg fs-6 fw-bold">
                                Start Your Journey
                            </Link>
                            <Link :href="route('login')" class="btn btn-white btn-lg rounded-pill px-5 py-3 shadow-sm border fs-6 fw-bold">
                                Live Demo
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Decorative background elements -->
            <div class="position-absolute top-0 start-0 w-100 h-100 overflow-hidden z-n1 pointer-events-none opacity-25">
                <div class="blur-circle bg-primary opacity-10" style="top: -100px; left: -100px; width: 400px; height: 400px;"></div>
                <div class="blur-circle bg-purple opacity-10" style="bottom: -150px; right: -50px; width: 500px; height: 500px;"></div>
            </div>
        </header>

        <!-- Features Grid -->
        <section class="py-10 bg-white">
            <div class="container">
                <div class="text-center mb-8">
                    <h2 class="fw-bold fs-2 text-dark mb-3">Complete Command Over Your Operations</h2>
                    <p class="text-muted fs-5">Everything you need to scale your agency or consulting business.</p>
                </div>
                
                <div class="row g-4">
                    <div class="col-md-6 col-lg-3">
                        <div class="card h-100 border-0 shadow-hover p-4 text-center rounded-4 transition-all">
                            <div class="feature-icon bg-primary-subtle text-primary mb-4 mx-auto rounded-circle d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                <i class="ti ti-users fs-2"></i>
                            </div>
                            <h3 class="h5 fw-bold mb-3">Client Management</h3>
                            <p class="text-muted small">Maintain detailed profiles, contact logs, and project history for every client in one place.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card h-100 border-0 shadow-hover p-4 text-center rounded-4 transition-all">
                            <div class="feature-icon bg-success-subtle text-success mb-4 mx-auto rounded-circle d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                <i class="ti ti-briefcase fs-2"></i>
                            </div>
                            <h3 class="h5 fw-bold mb-3">Project Tracking</h3>
                            <p class="text-muted small">Monitor development progress, milestones, and deadlines with intuitive status workflows.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card h-100 border-0 shadow-hover p-4 text-center rounded-4 transition-all">
                            <div class="feature-icon bg-warning-subtle text-warning mb-4 mx-auto rounded-circle d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                <i class="ti ti-file-invoice fs-2"></i>
                            </div>
                            <h3 class="h5 fw-bold mb-3">Smart Invoicing</h3>
                            <p class="text-muted small">Generate professional PDF invoices, track payments, and automate follow-ups with ease.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card h-100 border-0 shadow-hover p-4 text-center rounded-4 transition-all">
                            <div class="feature-icon bg-info-subtle text-info mb-4 mx-auto rounded-circle d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                <i class="ti ti-server fs-2"></i>
                            </div>
                            <h3 class="h5 fw-bold mb-3">Infrastructure</h3>
                            <p class="text-muted small">Allocate hosting resources and manage server configurations across your entire client base.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-10">
            <div class="container">
                <div class="bg-primary rounded-5 p-5 p-lg-10 shadow-lg position-relative overflow-hidden text-center">
                    <h2 class="display-5 fw-bold text-white mb-4">Ready to Transform Your Workflow?</h2>
                    <p class="text-white opacity-75 fs-5 mb-5 px-lg-10">
                        Join companies that trust VMCore CRM for their daily operations. Start your free trial today.
                    </p>
                    <Link v-if="!$page.props.auth.user" :href="route('register')" class="btn btn-white btn-lg rounded-pill px-10 py-3 shadow-md fw-bold">
                        Create Account
                    </Link>
                    <Link v-else :href="route('dashboard')" class="btn btn-white btn-lg rounded-pill px-10 py-3 shadow-md fw-bold">
                        Go to Dashboard
                    </Link>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="mt-auto py-5 bg-white border-top">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                        <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-2 mb-2">
                            <img src="/assets/images/vmcore.png" alt="VMCore" height="24">
                            <span class="fw-bold text-primary">VMCore CRM</span>
                        </div>
                        <p class="text-muted small mb-0">&copy; {{ new Date().getFullYear() }} VMCore. All rights reserved.</p>
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        <div class="d-flex justify-content-center justify-content-md-end gap-3">
                            <a href="#" class="text-muted hover-primary transition-all"><i class="ti ti-brand-twitter fs-4"></i></a>
                            <a href="#" class="text-muted hover-primary transition-all"><i class="ti ti-brand-linkedin fs-4"></i></a>
                            <a href="#" class="text-muted hover-primary transition-all"><i class="ti ti-brand-github fs-4"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

.fw-900 { font-weight: 900; }
.tracking-tight { letter-spacing: -0.025em; }

.gradient-text {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}

.bg-primary { background-color: #4f46e5 !important; }
.text-primary { color: #4f46e5 !important; }
.btn-primary { 
    background-color: #4f46e5; 
    border-color: #4f46e5; 
}
.btn-primary:hover {
    background-color: #4338ca;
    border-color: #4338ca;
}
.btn-outline-primary {
    color: #4f46e5;
    border-color: #4f46e5;
}
.btn-outline-primary:hover {
    background-color: #4f46e5;
    border-color: #4f46e5;
    color: white;
}

.btn-white {
    background-color: white;
    color: #4f46e5;
    border-color: transparent;
}
.btn-white:hover {
    background-color: #f8fafc;
    color: #4338ca;
}

.bg-primary-subtle { background-color: #eef2ff; }
.bg-success-subtle { background-color: #ecfdf5; }
.bg-warning-subtle { background-color: #fffbeb; }
.bg-info-subtle { background-color: #f0f9ff; }

.text-success { color: #10b981 !important; }
.text-warning { color: #f59e0b !important; }
.text-info { color: #0ea5e9 !important; }

.blur-circle {
    position: absolute;
    filter: blur(100px);
    border-radius: 50%;
}

.shadow-hover:hover {
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
    transform: translateY(-5px);
}

.transition-all {
    transition: all 0.3s ease;
}

.py-10 { padding-top: 5rem; padding-bottom: 5rem; }
.pt-10 { padding-top: 5rem; }

@keyframes bounce {
    0%, 100% { transform: translateY(-5%); animation-timing-function: cubic-bezier(0.8,0,1,1); }
    50% { transform: translateY(0); animation-timing-function: cubic-bezier(0,0,0.2,1); }
}
.animate-bounce {
    animation: bounce 2s infinite;
}

.hover-scale-105:hover { transform: scale(1.05); }

.bg-white\/80 { background-color: rgba(255, 255, 255, 0.8) !important; }
.backdrop-blur-md { backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); }

.shadow-lg { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important; }
</style>
