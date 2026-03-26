<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    settings: {
        type: Object,
        required: true
    }
});

const activeTab = ref('general');

const logoForms = {
    app_logo: useForm({ logo: null, type: 'app_logo' }),
    menu_logo: useForm({ logo: null, type: 'menu_logo' }),
    pdf_logo: useForm({ logo: null, type: 'pdf_logo' }),
};

const brandingForm = useForm({
    brand_name: props.settings.brand_name || '',
    contact_phone: props.settings.contact_phone || '',
    contact_email: props.settings.contact_email || '',
    contact_address: props.settings.contact_address || '',
    app_logo_height: props.settings.app_logo_height || '80',
    menu_logo_height: props.settings.menu_logo_height || '40',
    pdf_logo_height: props.settings.pdf_logo_height || '50',
});

const smtpForm = useForm({
    smtp_host: props.settings.smtp_host || '',
    smtp_port: props.settings.smtp_port || '',
    smtp_username: props.settings.smtp_username || '',
    smtp_password: props.settings.smtp_password || '',
    smtp_encryption: props.settings.smtp_encryption || '',
    smtp_from_address: props.settings.smtp_from_address || '',
    smtp_from_name: props.settings.smtp_from_name || '',
    test_email: '',
});

const testSmtp = () => {
    smtpForm.post(route('settings.test-smtp'), {
        preserveScroll: true,
    });
};

const invoiceForm = useForm({
    invoice_30_day_reminder: props.settings.invoice_30_day_reminder === '1' || props.settings.invoice_30_day_reminder === true,
    invoice_15_day_reminder: props.settings.invoice_15_day_reminder === '1' || props.settings.invoice_15_day_reminder === true,
    invoice_due_date_reminder: props.settings.invoice_due_date_reminder === '1' || props.settings.invoice_due_date_reminder === true,
});

const updateLogo = (type) => {
    logoForms[type].post(route('settings.logo'), {
        preserveScroll: true,
        onSuccess: () => logoForms[type].reset(),
    });
};

const updateBranding = () => {
    brandingForm.post(route('settings.update'), {
        preserveScroll: true,
    });
};

const updateSmtp = () => {
    smtpForm.post(route('settings.update'), {
        preserveScroll: true,
    });
};

const updateInvoice = () => {
    invoiceForm.post(route('settings.update'), {
        preserveScroll: true,
    });
};

const handleFileChange = (e, type) => {
    logoForms[type].logo = e.target.files[0];
};
</script>

<template>
    <Head title="Settings" />

    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">Admin Settings</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body p-0">
                        <div class="nav flex-column nav-pills" role="tablist">
                            <button @click="activeTab = 'general'" class="nav-link text-start rounded-0 py-3 border-bottom" :class="{ 'active': activeTab === 'general' }">
                                <i class="ti ti-settings me-2"></i> General Settings
                            </button>
                            <button @click="activeTab = 'smtp'" class="nav-link text-start rounded-0 py-3 border-bottom" :class="{ 'active': activeTab === 'smtp' }">
                                <i class="ti ti-mail me-2"></i> SMTP Credentials
                            </button>
                            <button @click="activeTab = 'invoices'" class="nav-link text-start rounded-0 py-3" :class="{ 'active': activeTab === 'invoices' }">
                                <i class="ti ti-file-invoice me-2"></i> Invoice Settings
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <div class="card">
                    <div class="card-body">
                        <!-- Success/Error Alerts -->
                        <div v-if="$page.props.flash.success" class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ $page.props.flash.success }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <div v-if="$page.props.flash.error" class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ $page.props.flash.error }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>

                        <!-- General Settings -->
                        <div v-if="activeTab === 'general'">
                            <h4 class="header-title mb-4">Branding & Contact Information</h4>
                            
                            <!-- Text-based Branding Settings -->
                            <form @submit.prevent="updateBranding" class="mb-5 pb-4 border-bottom">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Brand Name</label>
                                        <input type="text" v-model="brandingForm.brand_name" class="form-control" placeholder="e.g., VMCore CRM">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Contact Email</label>
                                        <input type="email" v-model="brandingForm.contact_email" class="form-control" placeholder="e.g., support@vmcore.in">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Contact Phone</label>
                                        <input type="text" v-model="brandingForm.contact_phone" class="form-control" placeholder="e.g., +91 999 999 9999">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Contact Address</label>
                                        <textarea v-model="brandingForm.contact_address" class="form-control" rows="1" placeholder="e.g., 123 VMCore Plaza, Mumbai"></textarea>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-sm btn-primary" :disabled="brandingForm.processing">
                                            <i class="ti ti-device-floppy me-1"></i> Save Branding Info
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <h4 class="header-title mb-4">Logo Settings</h4>
                            
                            <!-- App Logo (General/Login) -->
                            <form @submit.prevent="updateLogo('app_logo')" class="mb-5 pb-4 border-bottom">
                                <div class="row mb-3">
                                    <label class="col-md-3 col-form-label">App Logo (Login/General)</label>
                                    <div class="col-md-9 text-center p-3 bg-light rounded">
                                        <img :src="$page.props.appLogo" alt="App Logo" class="img-fluid" style="max-height: 80px;">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-md-3 col-form-label">Update App Logo</label>
                                    <div class="col-md-5">
                                        <input type="file" @change="e => handleFileChange(e, 'app_logo')" class="form-control mb-2">
                                        <button type="submit" class="btn btn-sm btn-primary" :disabled="logoForms.app_logo.processing">Upload App Logo</button>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Logo Height (px)</label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" v-model="brandingForm.app_logo_height" class="form-control">
                                            <button type="button" @click="updateBranding" class="btn btn-outline-secondary">Apply</button>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <!-- Menu Logo (Sidebar) -->
                            <form @submit.prevent="updateLogo('menu_logo')" class="mb-5 pb-4 border-bottom">
                                <div class="row mb-3">
                                    <label class="col-md-3 col-form-label">Menu Logo (Sidebar)</label>
                                    <div class="col-md-9 text-center p-3 bg-dark rounded">
                                        <img :src="$page.props.menuLogo" alt="Menu Logo" class="img-fluid" style="max-height: 40px;">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-md-3 col-form-label">Update Menu Logo</label>
                                    <div class="col-md-5">
                                        <input type="file" @change="e => handleFileChange(e, 'menu_logo')" class="form-control mb-2">
                                        <button type="submit" class="btn btn-sm btn-primary" :disabled="logoForms.menu_logo.processing">Upload Menu Logo</button>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Logo Height (px)</label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" v-model="brandingForm.menu_logo_height" class="form-control">
                                            <button type="button" @click="updateBranding" class="btn btn-outline-secondary">Apply</button>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <!-- PDF Logo (Invoices) -->
                            <form @submit.prevent="updateLogo('pdf_logo')">
                                <div class="row mb-3">
                                    <label class="col-md-3 col-form-label">PDF Logo (Invoices)</label>
                                    <div class="col-md-9 text-center p-3 bg-light border rounded">
                                        <img :src="$page.props.pdfLogo" alt="PDF Logo" class="img-fluid" style="max-height: 60px;">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-md-3 col-form-label">Update PDF Logo</label>
                                    <div class="col-md-5">
                                        <input type="file" @change="e => handleFileChange(e, 'pdf_logo')" class="form-control mb-2">
                                        <button type="submit" class="btn btn-sm btn-primary" :disabled="logoForms.pdf_logo.processing">Upload PDF Logo</button>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Logo Height (px)</label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" v-model="brandingForm.pdf_logo_height" class="form-control">
                                            <button type="button" @click="updateBranding" class="btn btn-outline-secondary">Apply</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- SMTP Settings -->
                        <div v-if="activeTab === 'smtp'">
                            <h4 class="header-title mb-4">Email Configuration (SMTP)</h4>
                            <form @submit.prevent="updateSmtp">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">SMTP Host</label>
                                        <input type="text" v-model="smtpForm.smtp_host" class="form-control" placeholder="e.g., smtp.mailtrap.io">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">SMTP Port</label>
                                        <input type="text" v-model="smtpForm.smtp_port" class="form-control" placeholder="e.g., 587 or 2525">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">SMTP Username</label>
                                        <input type="text" v-model="smtpForm.smtp_username" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">SMTP Password</label>
                                        <input type="password" v-model="smtpForm.smtp_password" class="form-control">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Encryption Type</label>
                                        <select v-model="smtpForm.smtp_encryption" class="form-select">
                                            <option value="">None</option>
                                            <option value="tls">TLS</option>
                                            <option value="ssl">SSL</option>
                                        </select>
                                    </div>
                                </div>
                                <hr class="my-4">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">From Email Address</label>
                                        <input type="email" v-model="smtpForm.smtp_from_address" class="form-control" placeholder="e.g., info@vmcore.com">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">From Name</label>
                                        <input type="text" v-model="smtpForm.smtp_from_name" class="form-control" placeholder="e.g., VMCore CRM">
                                    </div>
                                </div>
                                <hr class="my-4">
                                <div class="row align-items-end mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Test Email Recipient</label>
                                        <input type="email" v-model="smtpForm.test_email" class="form-control" placeholder="Enter email to receive test message">
                                    </div>
                                    <div class="col-md-6">
                                        <button type="button" @click="testSmtp" class="btn btn-info w-100" :disabled="smtpForm.processing">
                                            <i class="ti ti-mail-forward me-1"></i> Send Test Email
                                        </button>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn btn-primary" :disabled="smtpForm.processing">
                                            <i class="ti ti-device-floppy me-1"></i> Save SMTP Settings
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div v-if="activeTab === 'invoices'">
                            <h4 class="header-title mb-4">Invoice Automation Settings</h4>
                            <form @submit.prevent="updateInvoice">
                                <div class="mb-4">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="reminder30" v-model="invoiceForm.invoice_30_day_reminder">
                                        <label class="form-check-label" for="reminder30">First Reminder: 30 days before due date</label>
                                    </div>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="reminder15" v-model="invoiceForm.invoice_15_day_reminder">
                                        <label class="form-check-label" for="reminder15">Second Reminder: 15 days before due date</label>
                                    </div>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="reminderDue" v-model="invoiceForm.invoice_due_date_reminder">
                                        <label class="form-check-label" for="reminderDue">Final Reminder: On due date</label>
                                    </div>
                                </div>

                                <div class="alert alert-info py-2">
                                    <small><i class="ti ti-info-circle me-1"></i>
                                    Note: Hosting services will be automatically suspended 7 days after the due date if the invoice remains unpaid.</small>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary" :disabled="invoiceForm.processing">
                                            <i class="ti ti-device-floppy me-1"></i> Save Invoice Settings
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.nav-pills .nav-link.active {
    background-color: var(--bs-primary);
    color: #fff;
}
.nav-link {
    color: var(--bs-body-color);
    transition: all 0.3s ease;
}
.nav-link:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.1);
}
</style>
