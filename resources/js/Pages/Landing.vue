<script setup>
import { Head, Link } from '@inertiajs/vue3';
import LandingLayout from '@/Layouts/LandingLayout.vue';
import { onMounted, nextTick } from 'vue';
import { 
    Play, Star, LayoutDashboard, Users, FolderKanban, Receipt, Settings, Bell, 
    CreditCard, MessageSquare, ShieldCheck, Check 
} from 'lucide-vue-next';

/* ═══════════ DATA ═══════════ */

const features = [
    { title: "Client Management", desc: "Keep track of all your clients in one organized workspace with rich profiles.", icon: Users },
    { title: "Project Tracking", desc: "Monitor progress, assign tasks, and hit every deadline with ease.", icon: FolderKanban },
    { title: "Smart Invoicing", desc: "Generate professional invoices in seconds and get paid faster.", icon: Receipt },
    { title: "Expense Tracking", desc: "Keep a close eye on your budget and log expenses effortlessly.", icon: CreditCard },
    { title: "Project Discussions", desc: "Collaborate directly within projects. Keep context where it belongs.", icon: MessageSquare },
    { title: "Role-Based Access", desc: "Granular permissions ensure your team only sees what they need to.", icon: ShieldCheck },
];

const steps = [
    { title: "Create your account", desc: "Sign up in seconds and land in a pre-configured workspace." },
    { title: "Add clients & projects", desc: "Import your data or start fresh with intuitive creation flows." },
    { title: "Send invoices & get paid", desc: "Generate invoices automatically and track payments effortlessly." },
];

const testimonials = [
    {
      quote: "VMCore completely replaced our messy spreadsheets. We now spend 50% less time on admin and more time closing deals.",
      name: "Sarah Jenkins", role: "Operations Manager", avatarBg: "bg-blue-100", initial: "S"
    },
    {
      quote: "The interface is so clean and fast. Billing which used to take days is now done in a matter of clicks.",
      name: "Michael Chang", role: "Founder, Apex Digital", avatarBg: "bg-boron-100", initial: "M"
    },
    {
      quote: "We tried Salesforce and HubSpot, but they were too bloated. This CRM hits the sweet spot for modern agencies.",
      name: "Elena Rodriguez", role: "Sales Director", avatarBg: "bg-amber-100", initial: "E"
    }
];

const plans = [
    {
      name: "Starter", desc: "Perfect for freelancers just starting out.", price: "0", isPopular: false,
      features: ["Up to 3 clients", "Basic invoicing", "Community support"],
      btnText: "Start for free", btnClass: "bg-white text-zinc-900 border border-zinc-200 hover:bg-zinc-50"
    },
    {
      name: "Professional", desc: "For growing teams that need more power.", price: "19", isPopular: true,
      features: ["Unlimited clients", "Advanced reporting", "Custom domains", "Priority support"],
      btnText: "Start free trial", btnClass: "bg-white text-zinc-950 hover:bg-zinc-100"
    },
    {
      name: "Enterprise", desc: "Custom solutions for large organizations.", price: "49", isPopular: false,
      features: ["White-label CRM", "Dedicated account manager", "SSO integration", "API access"],
      btnText: "Contact sales", btnClass: "bg-white text-zinc-900 border border-zinc-200 hover:bg-zinc-50"
    }
];

const logos = ["Vercel", "Acme Corp", "Linear", "Stripe", "Next.js", "Supabase"];
const stats = [
    { value: "2", unit: "M+", label: "Payments processed" },
    { value: "1.5", unit: "k", label: "Active projects" },
    { value: "99.9", unit: "%", label: "Uptime" },
    { value: "4.9", unit: "/5", label: "Satisfaction" },
];

/* ═══════════ SCROLL REVEAL ═══════════ */
onMounted(() => {
    nextTick(() => {
        const observer = new IntersectionObserver(
            (entries) => entries.forEach(e => {
                if (e.isIntersecting) {
                    e.target.classList.add('visible');
                    observer.unobserve(e.target);
                }
            }),
            { threshold: 0.08, rootMargin: '0px 0px -40px 0px' }
        );
        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
    });
});
</script>

<template>
    <Head title="VMCore — CRM for Modern Teams" />

    <LandingLayout>
        
        <!-- ══════════════════════════════ -->
        <!-- 1 · HERO                       -->
        <!-- ══════════════════════════════ -->
        <section class="relative pt-[200px] mb-32 px-10 w-full max-w-5xl mx-auto flex flex-col items-center text-center overflow-hidden">
            <!-- Background radial gradient -->
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-boron-500 opacity-[0.06] blur-[120px] rounded-full pointer-events-none -z-10"></div>

            <!-- New Badge -->
            <div class="reveal flex items-center gap-2 px-3 py-1.5 rounded-full border border-boron-200 bg-boron-50 text-boron-700 text-xs font-semibold tracking-wide uppercase mb-8 shadow-sm">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-boron-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-boron-500"></span>
                </span>
                New — Workflow automation just launched
            </div>

            <!-- H1 -->
            <h1 class="reveal reveal-delay-1 text-[52px] md:text-[68px] font-bold leading-[1.05] tracking-[-2.5px] text-zinc-950 mb-6 max-w-4xl m-0">
                Manage clients seamlessly. <br />
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-boron-700 to-boron-500">
                    Close deals faster.
                </span>
            </h1>

            <!-- Subtitle -->
            <p class="reveal reveal-delay-2 text-lg md:text-xl text-zinc-500 max-w-lg font-light mb-10 m-0">
                The modern CRM built for speed and simplicity. Replace complex spreadsheets with an intuitive workspace designed for growing teams.
            </p>

            <!-- Actions -->
            <div class="reveal reveal-delay-3 flex flex-col sm:flex-row items-center gap-4 mb-14 w-full sm:w-auto">
                <Link :href="route('login')" style="text-decoration: none;" class="w-full sm:w-auto px-6 py-3 bg-zinc-950 text-white font-medium rounded-xl shadow-md hover:bg-zinc-800 hover:-translate-y-[2px] transition-all flex items-center justify-center gap-2">
                    Start for free &rarr;
                </Link>
                <button class="w-full sm:w-auto px-6 py-3 bg-white text-zinc-900 border border-zinc-200 font-medium rounded-xl shadow-sm hover:bg-zinc-50 hover:-translate-y-[2px] transition-all flex items-center justify-center gap-2 cursor-pointer m-0">
                    <Play :size="18" class="text-zinc-400" fill="currentColor" />
                    Watch 2-min demo
                </button>
            </div>

            <!-- Social Proof -->
            <div class="reveal reveal-delay-4 flex flex-col sm:flex-row items-center gap-4 mb-20">
                <div class="flex -space-x-3">
                    <div v-for="i in 4" :key="i" class="w-10 h-10 rounded-full border-2 border-white bg-zinc-100 shadow-sm flex items-center justify-center text-xs font-medium text-zinc-400 overflow-hidden">
                        <img :src="`https://i.pravatar.cc/100?img=${i + 10}`" :alt="`User ${i}`" class="w-full h-full object-cover" />
                    </div>
                </div>
                <div class="flex flex-col items-start gap-1">
                    <div class="flex items-center gap-1 text-amber-400">
                        <Star v-for="i in 5" :key="i" :size="14" fill="currentColor" />
                    </div>
                    <div class="text-sm font-medium text-zinc-600 m-0">
                        Loved by <span class="text-zinc-900 font-semibold">10,000+</span> users
                    </div>
                </div>
            </div>

            <!-- Mockup Component Embed -->
            <div class="reveal reveal-delay-5 w-full">
                <div class="relative mx-auto w-full max-w-[900px] text-left">
                    <div class="absolute -inset-1 rounded-[24px] bg-gradient-to-r from-boron-300 via-boron-100 to-boron-300 opacity-40 blur-lg mix-blend-multiply"></div>
                    <div class="relative w-full rounded-2xl border border-zinc-200 bg-white shadow-[0_20px_48px_rgba(0,0,0,0.08)] overflow-hidden flex flex-col h-[520px]">
                        <!-- macOS Title Bar -->
                        <div class="h-10 border-b border-zinc-200 bg-zinc-50 flex items-center px-4 w-full">
                            <div class="flex gap-1.5 p-1 w-[200px]">
                                <div class="w-3 h-3 rounded-full bg-red-400 border border-red-500/20"></div>
                                <div class="w-3 h-3 rounded-full bg-amber-400 border border-amber-500/20"></div>
                                <div class="w-3 h-3 rounded-full bg-boron-400 border border-boron-500/20"></div>
                            </div>
                            <div class="flex-1 flex justify-center">
                                <div class="h-7 w-48 bg-white rounded-md border border-zinc-200 shadow-sm flex items-center justify-center text-[11px] font-medium text-zinc-500">
                                    dashboard.vmcore.app
                                </div>
                            </div>
                            <div class="w-[200px] flex justify-end"></div>
                        </div>

                        <!-- Content area -->
                        <div class="flex flex-1 overflow-hidden">
                            <!-- Sidebar -->
                            <div class="w-[200px] bg-zinc-50 border-r border-zinc-200 p-3 flex-col gap-1 hidden md:flex">
                                <div class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-sm transition-colors cursor-pointer bg-white text-zinc-900 font-medium shadow-sm border border-zinc-200">
                                    <LayoutDashboard :size="14" /> Dashboard
                                </div>
                                <div class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-sm transition-colors cursor-pointer text-zinc-500 hover:text-zinc-900 hover:bg-zinc-100 font-medium">
                                    <Users :size="14" /> Clients
                                </div>
                                <div class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-sm transition-colors cursor-pointer text-zinc-500 hover:text-zinc-900 hover:bg-zinc-100 font-medium">
                                    <FolderKanban :size="14" /> Projects
                                </div>
                                <div class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-sm transition-colors cursor-pointer text-zinc-500 hover:text-zinc-900 hover:bg-zinc-100 font-medium">
                                    <Receipt :size="14" /> Invoices
                                </div>
                                <div class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-sm transition-colors cursor-pointer text-zinc-500 hover:text-zinc-900 hover:bg-zinc-100 font-medium">
                                    <Settings :size="14" /> Settings
                                </div>
                            </div>

                            <!-- Main Content -->
                            <div class="flex-1 bg-white p-6 overflow-hidden flex flex-col gap-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="font-semibold text-zinc-950 m-0 text-base">Overview</h3>
                                        <p class="text-[13px] text-zinc-500 m-0">Your agency metrics for this month</p>
                                    </div>
                                    <div class="w-8 h-8 rounded-full border border-zinc-200 flex items-center justify-center text-zinc-400">
                                        <Bell :size="14" />
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <div class="bg-white border border-zinc-200 rounded-xl p-4 shadow-sm flex flex-col gap-1">
                                        <div class="text-[13px] text-zinc-500 font-medium">Revenue</div>
                                        <div class="text-xl font-semibold text-zinc-950">$12,450</div>
                                        <div class="text-[11px] font-medium mt-1 text-boron-600">+14% from last month</div>
                                    </div>
                                    <div class="bg-white border border-zinc-200 rounded-xl p-4 shadow-sm flex flex-col gap-1">
                                        <div class="text-[13px] text-zinc-500 font-medium">Clients</div>
                                        <div class="text-xl font-semibold text-zinc-950">34</div>
                                        <div class="text-[11px] font-medium mt-1 text-boron-600">+2 from last month</div>
                                    </div>
                                    <div class="bg-white border border-zinc-200 rounded-xl p-4 shadow-sm flex flex-col gap-1">
                                        <div class="text-[13px] text-zinc-500 font-medium">Open Deals</div>
                                        <div class="text-xl font-semibold text-zinc-950">7</div>
                                        <div class="text-[11px] font-medium mt-1 text-zinc-400">-1 from last month</div>
                                    </div>
                                    <div class="bg-white border border-zinc-200 rounded-xl p-4 shadow-sm flex flex-col gap-1">
                                        <div class="text-[13px] text-zinc-500 font-medium">Invoices Due</div>
                                        <div class="text-xl font-semibold text-zinc-950">$4,200</div>
                                        <div class="text-[11px] font-medium mt-1 text-zinc-400">3 days from last month</div>
                                    </div>
                                </div>

                                <div class="flex flex-col md:flex-row gap-4 h-full">
                                    <div class="flex-1 bg-white border border-zinc-200 rounded-xl p-4 shadow-sm flex flex-col">
                                        <h4 class="text-[13px] font-semibold text-zinc-950 mb-4 m-0">Revenue trend</h4>
                                        <div class="flex-1 flex items-end justify-between gap-2 px-1 pb-1 border-b border-zinc-100">
                                            <div v-for="(h, i) in [40, 65, 45, 80, 55, 90, 75]" :key="i" class="w-full bg-zinc-100 rounded-t-md relative group hover:bg-boron-50 transition-colors" :style="{ height: `${h}%` }">
                                                <div class="absolute bottom-0 w-full bg-boron-500 rounded-t-md opacity-0 group-hover:opacity-100 transition-opacity" :style="{ height: `${h}%` }"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-1 bg-white border border-zinc-200 rounded-xl p-4 shadow-sm flex flex-col">
                                        <h4 class="text-[13px] font-semibold text-zinc-950 mb-3 m-0">Recent Pipeline</h4>
                                        <div class="flex flex-col gap-2 flex-1">
                                            <div class="flex items-center justify-between pb-2 border-b border-zinc-100">
                                                <span class="text-[13px] font-medium text-zinc-700">Acme Corp Redesign</span>
                                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-md border text-boron-700 bg-boron-50 border-boron-200">Won</span>
                                            </div>
                                            <div class="flex items-center justify-between pb-2 border-b border-zinc-100">
                                                <span class="text-[13px] font-medium text-zinc-700">Stark Ind. API</span>
                                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-md border text-amber-700 bg-amber-50 border-amber-200">In Progress</span>
                                            </div>
                                            <div class="flex items-center justify-between pb-2 border-b border-zinc-100 border-0">
                                                <span class="text-[13px] font-medium text-zinc-700">Wayne Ent. Audit</span>
                                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-md border text-zinc-600 bg-zinc-50 border-zinc-200">Proposal</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════ -->
        <!-- 2 · LOGOS BAND                 -->
        <!-- ══════════════════════════════ -->
        <section class="w-full border-y border-zinc-200 bg-white py-16 md:py-24 flex flex-col items-center overflow-hidden">
            <div class="max-w-5xl mx-auto px-10 w-full flex flex-col items-center">
                <p class="text-xs uppercase tracking-[0.2em] font-medium text-zinc-400 mb-8 m-0">
                    Trusted by innovative teams worldwide
                </p>
                <div class="flex flex-wrap justify-center gap-8 md:gap-16 w-full opacity-60 grayscale hover:grayscale-0 transition-all duration-500">
                    <div v-for="(logo, i) in logos" :key="logo" class="reveal text-xl md:text-2xl font-bold text-zinc-800 tracking-tight" :class="`reveal-delay-${(i % 5) + 1}`">
                        {{ logo }}
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════ -->
        <!-- 3 · STATS                      -->
        <!-- ══════════════════════════════ -->
        <section class="w-full bg-zinc-950 py-32 px-10">
            <div class="max-w-5xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-12">
                <div v-for="(stat, i) in stats" :key="i" class="reveal flex flex-col items-center text-center gap-2" :class="`reveal-delay-${i + 1}`">
                    <div class="text-5xl font-bold tracking-tight text-white flex items-baseline">
                        {{ stat.value }}<span class="text-boron-500">{{ stat.unit }}</span>
                    </div>
                    <div class="text-sm text-zinc-400 max-w-[120px] m-0">{{ stat.label }}</div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════ -->
        <!-- 4 · FEATURES                   -->
        <!-- ══════════════════════════════ -->
        <section id="features" class="py-32 px-10 w-full max-w-5xl mx-auto flex flex-col items-center">
            <div class="reveal text-center mb-16 flex flex-col items-center">
                <span class="text-boron-600 text-xs font-semibold tracking-widest uppercase mb-4 block inline-block">Features</span>
                <h2 class="text-[40px] md:text-[48px] font-bold tracking-tight text-zinc-950 mb-4 m-0">Everything you need to scale.</h2>
                <p class="text-zinc-500 max-w-md mx-auto text-lg leading-relaxed m-0">
                    Powerful tools designed to stay out of your way. Focus on growing your business, we'll handle the rest.
                </p>
            </div>
            <div class="reveal reveal-delay-2 w-full rounded-2xl border border-zinc-200 bg-zinc-200 overflow-hidden shadow-sm">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-[1px]">
                    <div v-for="(f, i) in features" :key="f.title" class="reveal bg-white p-8 hover:bg-zinc-50 transition-colors flex flex-col group cursor-default h-full" :class="`reveal-delay-${(i % 3) + 1}`">
                        <div class="w-10 h-10 rounded-xl bg-zinc-100 border border-zinc-200 flex items-center justify-center mb-5 group-hover:bg-white transition-colors">
                            <component :is="f.icon" :size="20" class="text-zinc-600" />
                        </div>
                        <h3 class="text-lg font-semibold text-zinc-950 mb-2 m-0">{{ f.title }}</h3>
                        <p class="text-zinc-500 leading-relaxed text-[15px] flex-1 m-0">{{ f.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════ -->
        <!-- 5 · HOW IT WORKS               -->
        <!-- ══════════════════════════════ -->
        <section id="how-it-works" class="w-full bg-zinc-50 border-y border-zinc-200 py-32 px-10 overflow-hidden">
            <div class="max-w-5xl mx-auto">
                <div class="reveal text-center mb-20">
                    <h2 class="text-[36px] md:text-[42px] font-bold tracking-tight text-zinc-950 mb-4 m-0">Get started in minutes.</h2>
                    <p class="text-zinc-500 text-lg m-0">No lengthy onboarding or training required.</p>
                </div>
                <div class="relative">
                    <div class="absolute top-5 left-8 right-8 h-[1px] bg-zinc-200 hidden md:block"></div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-12 relative z-10">
                        <div v-for="(step, i) in steps" :key="i" class="reveal flex flex-col items-center text-center md:items-start md:text-left" :class="`reveal-delay-${i + 1}`">
                            <div class="w-10 h-10 rounded-full border border-zinc-200 bg-white shadow-sm flex items-center justify-center font-mono font-bold text-zinc-950 mb-6 shrink-0" style="font-family: 'Geist Mono', monospace;">
                                {{ i + 1 }}
                            </div>
                            <h3 class="text-xl font-semibold text-zinc-950 mb-2 m-0">{{ step.title }}</h3>
                            <p class="text-zinc-500 leading-relaxed max-w-xs m-0">{{ step.desc }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════ -->
        <!-- 6 · TESTIMONIALS               -->
        <!-- ══════════════════════════════ -->
        <section id="testimonials" class="py-32 px-10 w-full max-w-5xl mx-auto">
            <div class="reveal text-center mb-16">
                <span class="text-boron-600 text-xs font-semibold tracking-widest uppercase mb-4 block inline-block">Testimonials</span>
                <h2 class="text-[36px] md:text-[42px] font-bold tracking-tight text-zinc-950 mb-4 m-0">Don't just take our word for it.</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div v-for="(testi, i) in testimonials" :key="i" class="reveal bg-white border border-zinc-200 rounded-2xl p-7 shadow-sm hover:shadow-md hover:border-zinc-300 transition-all flex flex-col gap-6" :class="`reveal-delay-${i + 1}`">
                    <div class="flex gap-1 text-amber-400">
                        <Star v-for="j in 5" :key="j" :size="16" fill="currentColor" />
                    </div>
                    <blockquote class="text-zinc-600 italic leading-relaxed text-[15px] flex-1 m-0">
                        "{{ testi.quote }}"
                    </blockquote>
                    <div class="flex items-center gap-3 mt-auto">
                        <div :class="`w-10 h-10 rounded-full flex items-center justify-center font-bold text-zinc-900 ${testi.avatarBg}`">
                            {{ testi.initial }}
                        </div>
                        <div>
                            <div class="font-semibold text-zinc-950 text-sm m-0">{{ testi.name }}</div>
                            <div class="text-zinc-500 text-[13px] m-0">{{ testi.role }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════ -->
        <!-- 7 · PRICING                    -->
        <!-- ══════════════════════════════ -->
        <section id="pricing" class="py-32 px-10 w-full bg-zinc-50 border-t border-zinc-200">
            <div class="max-w-5xl mx-auto">
                <div class="reveal text-center mb-16">
                    <span class="text-boron-600 text-xs font-semibold tracking-widest uppercase mb-4 block inline-block">Pricing</span>
                    <h2 class="text-[36px] md:text-[42px] font-bold tracking-tight text-zinc-950 mb-4 m-0">Simple, transparent pricing.</h2>
                    <p class="text-zinc-500 text-lg m-0">No hidden fees or unexpected charges.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center flex-1">
                    <div v-for="(plan, i) in plans" :key="i" class="reveal relative rounded-2xl p-8 flex flex-col h-full" :class="[plan.isPopular ? 'bg-zinc-950 text-white transform md:scale-[1.025] shadow-xl border-0 z-10' : 'bg-white text-zinc-950 border border-zinc-200 shadow-sm z-0', `reveal-delay-${i + 1}`]">
                        <div v-if="plan.isPopular" class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-boron-500 text-white text-[11px] font-bold tracking-widest uppercase py-1 px-3 rounded-full">
                            Most Popular
                        </div>
                        <div class="mb-8 border-b border-zinc-200/20 pb-8">
                            <h3 class="text-xl font-semibold mb-2 m-0">{{ plan.name }}</h3>
                            <p :class="`text-sm mb-6 m-0 ${plan.isPopular ? 'text-zinc-400' : 'text-zinc-500'}`">{{ plan.desc }}</p>
                            <div class="flex items-baseline gap-1 mt-4">
                                <span class="text-4xl font-bold tracking-tight m-0">${{ plan.price }}</span>
                                <span :class="`text-sm font-medium m-0 ${plan.isPopular ? 'text-zinc-400' : 'text-zinc-500'}`">/mo</span>
                            </div>
                        </div>
                        <ul class="flex flex-col gap-4 mb-8 flex-1 p-0 m-0 list-none">
                            <li v-for="f in plan.features" :key="f" class="flex items-start gap-3">
                                <Check :size="18" class="text-boron-500 shrink-0 mt-0.5" />
                                <span :class="`text-[15px] ${plan.isPopular ? 'text-zinc-300' : 'text-zinc-700'}`">{{ f }}</span>
                            </li>
                        </ul>
                        <Link :href="route('login')" :class="`w-full py-3 px-4 rounded-xl font-medium transition-colors cursor-pointer text-[15px] flex items-center justify-center border-0 mt-auto shadow-sm ${plan.btnClass}`" style="text-decoration: none;">
                            {{ plan.btnText }}
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════ -->
        <!-- 8 · CTA BANNER                 -->
        <!-- ══════════════════════════════ -->
        <section class="w-full relative py-40 overflow-hidden bg-gradient-to-br from-boron-900 via-boron-800 to-boron-700">
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-boron-500/20 blur-[100px] rounded-full pointer-events-none mix-blend-screen"></div>
            <div class="max-w-5xl mx-auto px-10 relative z-10 flex flex-col items-center text-center">
                <div class="reveal">
                    <h2 class="text-[40px] md:text-[52px] font-bold tracking-tight text-white mb-6 leading-tight max-w-2xl mx-auto m-0">
                        Ready to streamline your entire workflow?
                    </h2>
                    <p class="text-xl text-white/65 max-w-lg mx-auto font-light mb-10 m-0">
                        Join thousands of modern teams building better client relationships. Free for 14 days, no credit card required.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <Link :href="route('login')" class="w-full sm:w-auto px-8 py-3.5 bg-white text-boron-950 font-semibold rounded-xl shadow-lg hover:bg-zinc-50 hover:scale-[1.02] transition-all cursor-pointer border-0" style="text-decoration: none;">
                            Get started for free
                        </Link>
                        <Link :href="route('login')" class="w-full sm:w-auto px-8 py-3.5 bg-transparent border border-white/30 text-white font-semibold rounded-xl hover:bg-white/10 transition-colors cursor-pointer" style="text-decoration: none;">
                            Talk to sales
                        </Link>
                    </div>
                </div>
            </div>
        </section>

    </LandingLayout>
</template>
