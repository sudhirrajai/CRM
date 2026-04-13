<script setup>
import { Head, Link } from '@inertiajs/vue3';
import LandingLayout from '@/Layouts/LandingLayout.vue';
import { nextTick, onMounted } from 'vue';
import {
    ArrowRight,
    BarChart3,
    Bell,
    CheckCircle2,
    CreditCard,
    FolderKanban,
    LayoutDashboard,
    MessageSquare,
    Play,
    Receipt,
    ShieldCheck,
    TrendingUp,
    Users,
} from 'lucide-vue-next';

const highlights = [
    { label: 'Active Clients', value: '248', note: '+18 this month', icon: Users, tone: 'primary' },
    { label: 'Open Projects', value: '36', note: '12 deliver this week', icon: FolderKanban, tone: 'success' },
    { label: 'Invoices Raised', value: 'Rs 8.4L', note: '92% collected on time', icon: Receipt, tone: 'warning' },
    { label: 'Net Growth', value: '+24%', note: 'Across services and retainers', icon: TrendingUp, tone: 'info' },
];

const features = [
    {
        title: 'Client and lead visibility',
        description: 'See clients, pipeline, payments, and active work in one clean operational view.',
        icon: Users,
    },
    {
        title: 'Project-first execution',
        description: 'Move from proposal to delivery with milestones, discussions, and status tracking.',
        icon: FolderKanban,
    },
    {
        title: 'Fast invoicing and billing',
        description: 'Raise invoices, attach services, track due dates, and keep billing tied to actual work.',
        icon: Receipt,
    },
    {
        title: 'Expense and margin control',
        description: 'Monitor costs alongside revenue so teams can act before profitability slips.',
        icon: CreditCard,
    },
    {
        title: 'Team updates in context',
        description: 'Keep comments, changes, and client communication close to the project record.',
        icon: MessageSquare,
    },
    {
        title: 'Role-based access',
        description: 'Give admins, staff, and clients the exact view they need without clutter.',
        icon: ShieldCheck,
    },
];

const workflow = [
    {
        step: '01',
        title: 'Capture clients and opportunities',
        description: 'Start with incoming leads, client records, and a live view of your pipeline.',
    },
    {
        step: '02',
        title: 'Turn work into projects',
        description: 'Organize scopes, milestones, change requests, and collaboration in one place.',
    },
    {
        step: '03',
        title: 'Invoice with confidence',
        description: 'Generate invoices from delivered services and track collections without leaving the CRM.',
    },
];

const plans = [
    {
        name: 'Starter',
        price: 'Free',
        description: 'For lean teams setting up a disciplined client workflow.',
        accent: 'border-primary',
        features: ['Client records', 'Projects and discussions', 'Basic invoices'],
    },
    {
        name: 'Growth',
        price: 'Rs 1,499/mo',
        description: 'For agencies that need billing, reporting, and more operational control.',
        accent: 'border-success',
        features: ['Everything in Starter', 'Advanced invoice flow', 'Revenue visibility'],
    },
    {
        name: 'Scale',
        price: 'Custom',
        description: 'For businesses that want tailored processes and larger team controls.',
        accent: 'border-warning',
        features: ['Role controls', 'Priority support', 'Custom rollout'],
    },
];

const testimonials = [
    {
        name: 'Ananya Sharma',
        role: 'Operations Lead, Pixel Loom',
        quote: 'The landing promise now matches the product: clients, projects, and invoicing all feel connected instead of patched together.',
    },
    {
        name: 'Rohan Mehta',
        role: 'Founder, Northstar Media',
        quote: 'Our team adopted it quickly because the interface feels like an operations dashboard, not a generic template.',
    },
    {
        name: 'Sara Thomas',
        role: 'Finance Manager, BrightForge',
        quote: 'Billing follow-up became much easier once invoices, services, and project context lived in the same place.',
    },
];

onMounted(() => {
    nextTick(() => {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.querySelectorAll('.reveal').forEach((element) => element.classList.add('visible'));
            return;
        }

        const observer = new IntersectionObserver(
            (entries) => entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            }),
            { threshold: 0.08, rootMargin: '0px 0px -40px 0px' }
        );

        document.querySelectorAll('.reveal').forEach((element) => observer.observe(element));
    });
});
</script>

<template>
    <Head title="VMCore CRM" />

    <LandingLayout>
        <section class="landing-shell w-full">
            <div class="container py-5">
                <div class="row align-items-center g-4 hero-space">
                    <div class="col-xl-6">
                        <div class="reveal">
                            <div class="d-inline-flex align-items-center gap-2 badge text-bg-light border px-3 py-2 rounded-pill mb-3">
                                <span class="rounded-circle bg-primary d-inline-block pulse-dot"></span>
                                CRM Dashboard Experience, Extended To Your First Visit
                            </div>
                            <h1 class="display-4 fw-bold text-dark mb-3">
                                A landing page that now feels like the CRM your team actually uses.
                            </h1>
                            <p class="fs-5 text-muted mb-4 hero-copy">
                                VMCore brings leads, clients, projects, invoices, and reporting into one operational workspace built with the same clean card-driven feel as the dashboard.
                            </p>
                            <div class="d-flex flex-column flex-sm-row gap-3 mb-3 hero-cta-group">
                                <Link :href="route('login')" class="btn btn-primary btn-lg px-4 d-inline-flex align-items-center justify-content-center gap-2">
                                    Open CRM
                                    <ArrowRight :size="18" />
                                </Link>
                                <a href="#features" class="btn btn-light btn-lg px-4 d-inline-flex align-items-center justify-content-center gap-2 border text-decoration-none">
                                    <Play :size="18" />
                                    Explore Features
                                </a>
                            </div>
                            <p class="small text-muted mb-4">No setup friction. Start with your existing client workflow in minutes.</p>
                            <div class="d-flex flex-wrap gap-3 text-muted small">
                                <span class="d-inline-flex align-items-center gap-2">
                                    <CheckCircle2 :size="16" class="text-success" />
                                    Project and billing workflow
                                </span>
                                <span class="d-inline-flex align-items-center gap-2">
                                    <CheckCircle2 :size="16" class="text-success" />
                                    Dashboard-style reporting
                                </span>
                                <span class="d-inline-flex align-items-center gap-2">
                                    <CheckCircle2 :size="16" class="text-success" />
                                    Team and client roles
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6">
                        <div class="reveal reveal-delay-2">
                            <div class="card border-0 shadow-lg hero-preview overflow-hidden">
                                <div class="card-header bg-white border-bottom border-dashed d-flex justify-content-between align-items-center py-3">
                                    <div>
                                        <p class="text-muted text-uppercase fs-12 fw-semibold mb-1">Dashboard Preview</p>
                                        <h4 class="header-title mb-0">Operations Overview</h4>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge text-bg-light border px-3 py-2">
                                            <Bell :size="14" class="me-1" />
                                            4 alerts
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row g-3 mb-3">
                                        <div v-for="highlight in highlights" :key="highlight.label" class="col-sm-6">
                                            <div class="card h-100 mb-0 border-start border-3 stat-card" :class="`border-${highlight.tone}`">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="avatar-md flex-shrink-0">
                                                            <span class="avatar-title rounded-circle fs-20" :class="`text-bg-${highlight.tone}`">
                                                                <component :is="highlight.icon" :size="20" />
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <p class="text-muted fs-12 text-uppercase fw-semibold mb-1">{{ highlight.label }}</p>
                                                            <h4 class="fw-bold mb-1">{{ highlight.value }}</h4>
                                                            <p class="text-muted small mb-0">{{ highlight.note }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-lg-7">
                                            <div class="card h-100 mb-0">
                                                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                                                    <h4 class="header-title mb-0">Revenue Overview</h4>
                                                    <span class="badge text-bg-success">On Track</span>
                                                </div>
                                                <div class="card-body">
                                                    <div class="chart-bars d-flex align-items-end gap-2">
                                                        <div v-for="height in [42, 60, 58, 74, 68, 88, 84]" :key="height" class="bar-slot">
                                                            <div class="bar-fill" :style="{ height: `${height}%` }"></div>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex justify-content-between text-muted fs-12 mt-3">
                                                        <span>Mon</span>
                                                        <span>Tue</span>
                                                        <span>Wed</span>
                                                        <span>Thu</span>
                                                        <span>Fri</span>
                                                        <span>Sat</span>
                                                        <span>Sun</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-5">
                                            <div class="card h-100 mb-0">
                                                <div class="card-header bg-white py-3">
                                                    <h4 class="header-title mb-0">Recent Activity</h4>
                                                </div>
                                                <div class="card-body">
                                                    <div class="activity-item">
                                                        <div class="activity-icon text-bg-primary">
                                                            <LayoutDashboard :size="16" />
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1">New client onboarded</h6>
                                                            <p class="text-muted small mb-0">Acme Studio added with two active projects.</p>
                                                        </div>
                                                    </div>
                                                    <div class="activity-item">
                                                        <div class="activity-icon text-bg-success">
                                                            <Receipt :size="16" />
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1">Invoice paid</h6>
                                                            <p class="text-muted small mb-0">Invoice INV-2408 marked paid this morning.</p>
                                                        </div>
                                                    </div>
                                                    <div class="activity-item mb-0">
                                                        <div class="activity-icon text-bg-warning">
                                                            <BarChart3 :size="16" />
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1">Revenue trend improved</h6>
                                                            <p class="text-muted small mb-0">Monthly collections are 14% above last period.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-2 quick-metrics">
                    <div v-for="highlight in highlights" :key="`${highlight.label}-mini`" class="col-md-3 col-6">
                        <div class="card h-100 mb-0 reveal" :class="`reveal-delay-${(highlights.indexOf(highlight) % 4) + 1}`">
                            <div class="card-body py-3">
                                <p class="text-muted text-uppercase fs-12 fw-semibold mb-1">{{ highlight.label }}</p>
                                <h4 class="fw-bold mb-1">{{ highlight.value }}</h4>
                                <p class="text-muted small mb-0">{{ highlight.note }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="features" class="w-full py-5 bg-light border-top border-bottom">
            <div class="container py-4">
                <div class="row mb-4 align-items-end">
                    <div class="col-lg-7">
                        <p class="text-primary text-uppercase fs-12 fw-semibold mb-2">Features</p>
                        <h2 class="fw-bold mb-2">Designed like the dashboard, not a disconnected marketing site.</h2>
                        <p class="text-muted mb-0">Every section uses the same card hierarchy, muted labels, and CRM tone that runs through the application.</p>
                    </div>
                </div>

                <div class="row g-3">
                    <div v-for="(feature, index) in features" :key="feature.title" class="col-md-6 col-xl-4">
                        <div class="card h-100 reveal" :class="`reveal-delay-${(index % 3) + 1}`">
                            <div class="card-body p-4">
                                <div class="feature-icon text-bg-light border">
                                    <component :is="feature.icon" :size="20" class="text-primary" />
                                </div>
                                <h4 class="header-title mb-2">{{ feature.title }}</h4>
                                <p class="text-muted mb-0">{{ feature.description }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="customers" class="w-full py-5">
            <div class="container py-4">
                <div class="row g-3 mb-4">
                    <div class="col-lg-8">
                        <p class="text-success text-uppercase fs-12 fw-semibold mb-2">Workflow</p>
                        <h2 class="fw-bold mb-2">Built for agencies, service teams, and operations-heavy CRM work.</h2>
                        <p class="text-muted mb-0">The landing page now mirrors the way VMCore structures work inside the product: capture, execute, invoice, and review.</p>
                    </div>
                </div>

                <div class="row g-3">
                    <div v-for="(item, index) in workflow" :key="item.step" class="col-lg-4">
                        <div class="card h-100 border-start border-3 reveal" :class="['border-primary', `reveal-delay-${index + 1}`]">
                            <div class="card-body p-4">
                                <span class="badge text-bg-light border mb-3">{{ item.step }}</span>
                                <h4 class="header-title mb-2">{{ item.title }}</h4>
                                <p class="text-muted mb-0">{{ item.description }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="pricing" class="w-full py-5 bg-light border-top border-bottom">
            <div class="container py-4">
                <div class="row mb-4">
                    <div class="col-lg-7">
                        <p class="text-warning text-uppercase fs-12 fw-semibold mb-2">Plans</p>
                        <h2 class="fw-bold mb-2">Simple pricing blocks styled like internal CRM panels.</h2>
                        <p class="text-muted mb-0">The pricing section keeps the same visual discipline as the rest of the app instead of switching to a different brand language.</p>
                    </div>
                </div>

                <div class="row g-3">
                    <div v-for="(plan, index) in plans" :key="plan.name" class="col-lg-4">
                        <div class="card h-100 border-start border-3 reveal" :class="[plan.accent, `reveal-delay-${index + 1}`]">
                            <div class="card-body p-4 d-flex flex-column">
                                <div class="mb-4">
                                    <p class="text-muted text-uppercase fs-12 fw-semibold mb-1">{{ plan.name }}</p>
                                    <h3 class="fw-bold mb-2">{{ plan.price }}</h3>
                                    <p class="text-muted mb-0">{{ plan.description }}</p>
                                </div>
                                <div class="vstack gap-2 mb-4">
                                    <div v-for="feature in plan.features" :key="feature" class="d-flex align-items-start gap-2 text-muted">
                                        <CheckCircle2 :size="16" class="text-success mt-1 flex-shrink-0" />
                                        <span>{{ feature }}</span>
                                    </div>
                                </div>
                                <Link :href="route('login')" class="btn btn-primary mt-auto">
                                    Get Started
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="docs" class="w-full py-5">
            <div class="container py-4">
                <div class="card border-0 shadow-sm crm-banner reveal">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-8">
                                <p class="text-uppercase fs-12 fw-semibold text-primary mb-2">Product Story</p>
                                <h2 class="fw-bold text-dark mb-2">A cleaner first impression, with the same CRM essence users see after login.</h2>
                                <p class="text-muted mb-0">
                                    The new landing page uses the dashboard's visual cues: calm surfaces, informative cards, restrained accents, and business-focused messaging.
                                </p>
                            </div>
                            <div class="col-lg-4 text-lg-end">
                                <Link :href="route('login')" class="btn btn-primary btn-lg me-lg-2 mb-2 mb-lg-0">
                                    Launch CRM
                                </Link>
                                <a href="#blog" class="btn btn-light btn-lg border text-decoration-none">
                                    View Highlights
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="blog" class="w-full py-5 bg-light border-top">
            <div class="container py-4">
                <div class="row mb-4">
                    <div class="col-lg-7">
                        <p class="text-info text-uppercase fs-12 fw-semibold mb-2">Customers</p>
                        <h2 class="fw-bold mb-2">What teams notice first: clarity, structure, and billing context.</h2>
                        <p class="text-muted mb-0">These testimonial cards also follow the dashboard's simpler information-first treatment.</p>
                    </div>
                </div>

                <div class="row g-3">
                    <div v-for="(testimonial, index) in testimonials" :key="testimonial.name" class="col-lg-4">
                        <div class="card h-100 reveal" :class="`reveal-delay-${index + 1}`">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center gap-2 mb-3 text-warning">
                                    <CheckCircle2 :size="16" />
                                    <CheckCircle2 :size="16" />
                                    <CheckCircle2 :size="16" />
                                </div>
                                <p class="text-muted mb-4">"{{ testimonial.quote }}"</p>
                                <div>
                                    <h6 class="mb-1">{{ testimonial.name }}</h6>
                                    <p class="text-muted small mb-0">{{ testimonial.role }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </LandingLayout>
</template>

<style scoped>
.landing-shell {
    background:
        radial-gradient(circle at top right, rgba(62, 96, 213, 0.08), transparent 30%),
        radial-gradient(circle at 12% 20%, rgba(70, 183, 255, 0.08), transparent 26%),
        linear-gradient(180deg, #f8f9fc 0%, #ffffff 40%);
}

.hero-space {
    padding-top: 6rem;
    padding-bottom: 2rem;
}

.hero-copy {
    max-width: 42rem;
}

.hero-cta-group .btn {
    min-height: 3.25rem;
}

.hero-preview {
    border-radius: 1rem;
}

.pulse-dot {
    width: 0.55rem;
    height: 0.55rem;
    box-shadow: 0 0 0 0 rgba(62, 96, 213, 0.45);
    animation: pulse 1.8s infinite;
}

.stat-card {
    background: linear-gradient(180deg, #ffffff 0%, #fbfcff 100%);
}

.chart-bars {
    height: 220px;
}

.bar-slot {
    flex: 1;
    height: 100%;
    background: #eef2f7;
    border-radius: 0.75rem 0.75rem 0.35rem 0.35rem;
    position: relative;
    overflow: hidden;
}

.bar-fill {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    border-radius: 0.75rem 0.75rem 0.35rem 0.35rem;
    background: linear-gradient(180deg, #7aa2ff 0%, #3e60d5 100%);
}

.activity-item {
    display: flex;
    align-items: flex-start;
    gap: 0.875rem;
    margin-bottom: 1rem;
}

.activity-icon {
    width: 2rem;
    height: 2rem;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.feature-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 0.75rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
}

.crm-banner {
    background: linear-gradient(135deg, #ffffff 0%, #f5f8ff 100%);
}

.quick-metrics .card {
    border-color: #e9edf4;
}

.reveal {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.7s ease, transform 0.7s ease;
}

.reveal.visible {
    opacity: 1;
    transform: translateY(0);
}

.reveal-delay-1 {
    transition-delay: 0.08s;
}

.reveal-delay-2 {
    transition-delay: 0.16s;
}

.reveal-delay-3 {
    transition-delay: 0.24s;
}

.reveal-delay-4 {
    transition-delay: 0.32s;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(62, 96, 213, 0.45);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(62, 96, 213, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(62, 96, 213, 0);
    }
}

@media (max-width: 991.98px) {
    .hero-space {
        padding-top: 4.5rem;
        padding-bottom: 1rem;
    }

    .hero-copy {
        max-width: 100%;
    }

    .chart-bars {
        height: 180px;
    }
}

@media (max-width: 767.98px) {
    h1.display-4 {
        font-size: clamp(1.85rem, 6.5vw, 2.35rem);
        line-height: 1.2;
    }

    .hero-cta-group .btn {
        width: 100%;
    }

    .hero-preview .card-body {
        padding: 1rem !important;
    }

    .feature-icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.6rem;
    }

    .chart-bars {
        height: 150px;
    }

    .bar-slot {
        border-radius: 0.6rem 0.6rem 0.3rem 0.3rem;
    }

    .bar-fill {
        border-radius: 0.6rem 0.6rem 0.3rem 0.3rem;
    }

    .quick-metrics h4 {
        font-size: 1.05rem;
    }

    .activity-item {
        gap: 0.7rem;
    }

    .crm-banner .card-body {
        padding: 1.25rem !important;
    }
}
</style>
