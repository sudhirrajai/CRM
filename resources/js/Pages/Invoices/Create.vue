<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    clients: { type: Array, required: true },
    projects: { type: Array, required: true },
    hostings: { type: Array, required: true },
    currencies: { type: Array, required: true },
});

const form = useForm({
    client_id: '',
    shared_client_ids: [],
    extra_recipients: [],
    extra_recipients_input: '',
    project_id: '',
    currency_id: '',
    invoice_number: 'INV-' + Math.floor(1000 + Math.random() * 9000),
    issue_date: new Date().toISOString().split('T')[0],
    due_date: '',
    total_amount: 0,
    status: 'draft',
    notes: '',
    payment_mode: '',
    payment_reference: '',
    payment_note: '',
    send_email: false,
    selected_crs: [],
    items: [{ description: '', quantity: 1, unit_price: 0, total: 0 }],
});

const selectedProject = ref(null);
const selectedProjectServiceId = ref('');
const selectedHostingServiceId = ref('');

const filteredProjects = computed(() =>
    props.projects.filter((project) => !form.client_id || project.client_id === form.client_id)
);

const filteredHostings = computed(() =>
    props.hostings.filter((hosting) => !form.client_id || hosting.client_id === form.client_id)
);

const projectServiceOptions = computed(() =>
    filteredProjects.value.map((project) => ({
        id: project.id,
        label: `${project.name}${project.budget ? ` - ${Number(project.budget).toFixed(2)}` : ''}`,
        description: `Project Service: ${project.name}`,
        unit_price: Number(project.budget || 0),
    }))
);

const hostingServiceOptions = computed(() =>
    filteredHostings.value.map((hosting) => ({
        id: hosting.id,
        label: `${hosting.domain} (${hosting.billing_cycle || 'N/A'}) - ${Number(hosting.price || 0).toFixed(2)}`,
        description: `Hosting Service: ${hosting.domain}${hosting.plan_details ? ` - ${hosting.plan_details}` : ''}`,
        unit_price: Number(hosting.price || 0),
        project_id: hosting.project_id || '',
    }))
);

watch(() => form.project_id, (newProjectId) => {
    if (newProjectId) {
        selectedProject.value = props.projects.find(p => p.id === newProjectId);
        form.selected_crs = [];
    } else {
        selectedProject.value = null;
        form.selected_crs = [];
    }
});

watch(() => form.client_id, () => {
    if (form.project_id && !filteredProjects.value.some((project) => project.id === form.project_id)) {
        form.project_id = '';
    }
    selectedProjectServiceId.value = '';
    selectedHostingServiceId.value = '';
});

watch(() => form.items, () => {
    calculateTotal();
}, { deep: true });

const toggleCR = (cr) => {
    const index = form.selected_crs.indexOf(cr.id);
    if (index > -1) {
        form.selected_crs.splice(index, 1);
    } else {
        form.selected_crs.push(cr.id);
    }
    calculateTotal();
};

const updateItemTotal = (index) => {
    const item = form.items[index];
    const qty = Number(item.quantity || 0);
    const price = Number(item.unit_price || 0);
    item.total = Number((qty * price).toFixed(2));
};

const addItem = (prefill = {}) => {
    const quantity = Number(prefill.quantity ?? 1);
    const unitPrice = Number(prefill.unit_price ?? 0);

    form.items.push({
        description: prefill.description ?? '',
        quantity,
        unit_price: unitPrice,
        total: Number((quantity * unitPrice).toFixed(2)),
    });
};

const removeItem = (index) => {
    if (form.items.length === 1) {
        form.items[0] = { description: '', quantity: 1, unit_price: 0, total: 0 };
    } else {
        form.items.splice(index, 1);
    }
    calculateTotal();
};

const addSelectedProjectService = () => {
    const selected = projectServiceOptions.value.find((project) => project.id === selectedProjectServiceId.value);
    if (!selected) return;

    addItem(selected);
    selectedProjectServiceId.value = '';
    calculateTotal();
};

const addSelectedHostingService = () => {
    const selected = hostingServiceOptions.value.find((hosting) => hosting.id === selectedHostingServiceId.value);
    if (!selected) return;

    addItem(selected);
    if (!form.project_id && selected.project_id) {
        form.project_id = selected.project_id;
    }
    selectedHostingServiceId.value = '';
    calculateTotal();
};

const calculateTotal = () => {
    const lineItemsTotal = form.items.reduce((sum, item) => sum + Number(item.total || 0), 0);
    const crTotal = (selectedProject.value?.change_requests || [])
        .filter((cr) => form.selected_crs.includes(cr.id))
        .reduce((sum, cr) => sum + Number(cr.amount || 0), 0);

    form.total_amount = Number((lineItemsTotal + crTotal).toFixed(2));
};

const submit = () => {
    calculateTotal();
    form.extra_recipients = form.extra_recipients_input
        .split(/[\n,;]+/)
        .map((email) => email.trim().toLowerCase())
        .filter((email, index, arr) => email && arr.indexOf(email) === index);

    form.post(route('invoices.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Generate Invoice" />

    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">Generate Invoice</h4>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-9 col-xl-8">
                <div class="card">
                    <div class="card-body">
                        <form @submit.prevent="submit">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="invoice_number" class="form-label">Invoice Number <span class="text-danger">*</span></label>
                                    <input type="text" id="invoice_number" v-model="form.invoice_number" class="form-control" :class="{ 'is-invalid': form.errors.invoice_number }" required>
                                    <div class="invalid-feedback" v-if="form.errors.invoice_number">{{ form.errors.invoice_number }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="client_id" class="form-label">Client <span class="text-danger">*</span></label>
                                    <select id="client_id" v-model="form.client_id" class="form-select" :class="{ 'is-invalid': form.errors.client_id }" required>
                                        <option value="" disabled>Select Client</option>
                                        <option v-for="client in clients" :key="client.id" :value="client.id">
                                            {{ client.name }} ({{ client.company || 'Individual' }})
                                        </option>
                                    </select>
                                    <div class="invalid-feedback" v-if="form.errors.client_id">{{ form.errors.client_id }}</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="project_id" class="form-label">Related Project</label>
                                    <select id="project_id" v-model="form.project_id" class="form-select" :class="{ 'is-invalid': form.errors.project_id }">
                                        <option value="">None (General Billing)</option>
                                        <option v-for="project in filteredProjects" :key="project.id" :value="project.id">
                                            {{ project.name }}
                                        </option>
                                    </select>
                                    <div class="invalid-feedback" v-if="form.errors.project_id">{{ form.errors.project_id }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="currency_id" class="form-label">Currency <span class="text-danger">*</span></label>
                                    <select id="currency_id" v-model="form.currency_id" class="form-select" :class="{ 'is-invalid': form.errors.currency_id }" required>
                                        <option value="" disabled>Select Currency</option>
                                        <option v-for="currency in currencies" :key="currency.id" :value="currency.id">
                                            {{ currency.name }} ({{ currency.code }})
                                        </option>
                                    </select>
                                    <div class="invalid-feedback" v-if="form.errors.currency_id">{{ form.errors.currency_id }}</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="shared_client_ids" class="form-label">Partner Clients</label>
                                    <select
                                        id="shared_client_ids"
                                        v-model="form.shared_client_ids"
                                        class="form-select"
                                        :class="{ 'is-invalid': form.errors.shared_client_ids }"
                                        multiple
                                    >
                                        <option
                                            v-for="client in clients.filter((c) => c.id !== form.client_id)"
                                            :key="client.id"
                                            :value="client.id"
                                        >
                                            {{ client.name }} ({{ client.company || 'Individual' }})
                                        </option>
                                    </select>
                                    <div class="form-text">Hold Ctrl/Cmd to choose multiple partner clients.</div>
                                    <div class="invalid-feedback" v-if="form.errors.shared_client_ids">{{ form.errors.shared_client_ids }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="extra_recipients_input" class="form-label">Additional Recipient Emails</label>
                                    <textarea
                                        id="extra_recipients_input"
                                        v-model="form.extra_recipients_input"
                                        rows="3"
                                        class="form-control"
                                        :class="{ 'is-invalid': form.errors.extra_recipients || form.errors['extra_recipients.0'] }"
                                        placeholder="partner@example.com, accounts@example.com"
                                    />
                                    <div class="form-text">Separate emails with commas or new lines.</div>
                                    <div class="invalid-feedback" v-if="form.errors.extra_recipients">{{ form.errors.extra_recipients }}</div>
                                    <div class="invalid-feedback" v-else-if="form.errors['extra_recipients.0']">{{ form.errors['extra_recipients.0'] }}</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Add Project Service</label>
                                    <div class="input-group">
                                        <select v-model="selectedProjectServiceId" class="form-select">
                                            <option value="">Select project service</option>
                                            <option v-for="projectService in projectServiceOptions" :key="projectService.id" :value="projectService.id">
                                                {{ projectService.label }}
                                            </option>
                                        </select>
                                        <button class="btn btn-outline-primary" type="button" @click="addSelectedProjectService">
                                            Add
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Add Hosting Service</label>
                                    <div class="input-group">
                                        <select v-model="selectedHostingServiceId" class="form-select">
                                            <option value="">Select hosting service</option>
                                            <option v-for="hostingService in hostingServiceOptions" :key="hostingService.id" :value="hostingService.id">
                                                {{ hostingService.label }}
                                            </option>
                                        </select>
                                        <button class="btn btn-outline-primary" type="button" @click="addSelectedHostingService">
                                            Add
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label mb-0">Invoice Items</label>
                                        <button type="button" class="btn btn-sm btn-light" @click="addItem()">
                                            <i class="ti ti-plus me-1"></i> Add Item
                                        </button>
                                    </div>
                                    <div class="table-responsive border rounded">
                                        <table class="table table-sm mb-0 align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Description</th>
                                                    <th style="width: 120px;">Qty</th>
                                                    <th style="width: 160px;">Unit Price</th>
                                                    <th style="width: 160px;">Total</th>
                                                    <th style="width: 60px;"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(item, index) in form.items" :key="index">
                                                    <td>
                                                        <input v-model="item.description" type="text" class="form-control form-control-sm" placeholder="Service description">
                                                    </td>
                                                    <td>
                                                        <input v-model.number="item.quantity" type="number" min="1" step="1" class="form-control form-control-sm" @input="updateItemTotal(index)">
                                                    </td>
                                                    <td>
                                                        <input v-model.number="item.unit_price" type="number" min="0" step="0.01" class="form-control form-control-sm" @input="updateItemTotal(index)">
                                                    </td>
                                                    <td>
                                                        <input v-model.number="item.total" type="number" min="0" step="0.01" class="form-control form-control-sm" @input="calculateTotal">
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-link text-danger p-0" @click="removeItem(index)">
                                                            <i class="ti ti-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="text-danger small mt-1" v-if="form.errors.items">{{ form.errors.items }}</div>
                                </div>
                            </div>

                            <div v-if="selectedProject && selectedProject.change_requests?.length > 0" class="row mb-3">
                                <div class="col-12">
                                    <label class="form-label">Select Change Requests to Include</label>
                                    <div class="list-group">
                                        <label v-for="cr in selectedProject.change_requests" :key="cr.id" class="list-group-item d-flex justify-content-between align-items-center cursor-pointer">
                                            <div>
                                                <input type="checkbox" class="form-check-input me-2" :checked="form.selected_crs.includes(cr.id)" @change="toggleCR(cr)">
                                                <span>{{ cr.title }}</span>
                                                <small class="text-muted d-block">{{ cr.description }}</small>
                                            </div>
                                            <span class="badge bg-primary rounded-pill">{{ selectedProject.client?.currency?.code || 'USD' }} {{ cr.amount }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="issue_date" class="form-label">Issue Date <span class="text-danger">*</span></label>
                                    <input type="date" id="issue_date" v-model="form.issue_date" class="form-control" :class="{ 'is-invalid': form.errors.issue_date }" required>
                                    <div class="invalid-feedback" v-if="form.errors.issue_date">{{ form.errors.issue_date }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="due_date" class="form-label">Due Date <span class="text-danger">*</span></label>
                                    <input type="date" id="due_date" v-model="form.due_date" class="form-control" :class="{ 'is-invalid': form.errors.due_date }" required>
                                    <div class="invalid-feedback" v-if="form.errors.due_date">{{ form.errors.due_date }}</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="total_amount" class="form-label">Total Amount <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" id="total_amount" v-model="form.total_amount" class="form-control" :class="{ 'is-invalid': form.errors.total_amount }" required>
                                    <div class="invalid-feedback" v-if="form.errors.total_amount">{{ form.errors.total_amount }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select id="status" v-model="form.status" class="form-select" :class="{ 'is-invalid': form.errors.status }" required>
                                        <option value="draft">Draft</option>
                                        <option value="sent">Sent</option>
                                        <option value="paid">Paid</option>
                                        <option value="overdue">Overdue</option>
                                    </select>
                                    <div class="invalid-feedback" v-if="form.errors.status">{{ form.errors.status }}</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea id="notes" v-model="form.notes" class="form-control" :class="{ 'is-invalid': form.errors.notes }" rows="3"></textarea>
                                    <div class="invalid-feedback" v-if="form.errors.notes">{{ form.errors.notes }}</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="payment_mode" class="form-label">Payment Mode</label>
                                    <select id="payment_mode" v-model="form.payment_mode" class="form-select" :class="{ 'is-invalid': form.errors.payment_mode }">
                                        <option value="">Not specified</option>
                                        <option value="cash">Cash</option>
                                        <option value="cheque">Cheque</option>
                                        <option value="bank">Bank</option>
                                        <option value="paypal">PayPal</option>
                                        <option value="upi">UPI</option>
                                    </select>
                                    <div class="invalid-feedback" v-if="form.errors.payment_mode">{{ form.errors.payment_mode }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label for="payment_reference" class="form-label">Payment Reference</label>
                                    <input
                                        type="text"
                                        id="payment_reference"
                                        v-model="form.payment_reference"
                                        class="form-control"
                                        :class="{ 'is-invalid': form.errors.payment_reference }"
                                        placeholder="Txn/cheque/reference number"
                                    >
                                    <div class="invalid-feedback" v-if="form.errors.payment_reference">{{ form.errors.payment_reference }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label for="payment_note" class="form-label">Payment Note</label>
                                    <input
                                        type="text"
                                        id="payment_note"
                                        v-model="form.payment_note"
                                        class="form-control"
                                        :class="{ 'is-invalid': form.errors.payment_note }"
                                        placeholder="Optional note"
                                    >
                                    <div class="invalid-feedback" v-if="form.errors.payment_note">{{ form.errors.payment_note }}</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="form-check">
                                        <input type="checkbox" id="send_email" v-model="form.send_email" class="form-check-input">
                                        <label for="send_email" class="form-check-label user-select-none">Send copy to all invoice recipients via email</label>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <Link :href="route('invoices.index')" class="btn btn-light me-2">Cancel</Link>
                                <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                    Generate Invoice
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
