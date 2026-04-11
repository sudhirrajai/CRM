<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Icon } from '@iconify/vue';

const props = defineProps({
    automation: Object,
    triggerEvents: Object,
    templates: Array,
});

const form = useForm({
    name: props.automation?.name || '',
    description: props.automation?.description || '',
    trigger_event: props.automation?.trigger_event || '',
    steps: props.automation?.steps || [],
});

const showStepModal = ref(false);
const editingStepIndex = ref(null);
const newStep = ref({
    type: 'email',
    delay_hours: 0,
    email_template_id: '',
});

function addStep() {
    form.steps.push({ ...newStep.value });
    showStepModal.value = false;
    resetNewStep();
}

function updateStep() {
    form.steps[editingStepIndex.value] = { ...newStep.value };
    showStepModal.value = false;
    resetNewStep();
}

function removeStep(index) {
    if (confirm('Remove this step?')) {
        form.steps.splice(index, 1);
    }
}

function editStep(index) {
    editingStepIndex.value = index;
    newStep.value = { ...form.steps[index] };
    showStepModal.value = true;
}

function resetNewStep() {
    newStep.value = { type: 'email', delay_hours: 0, email_template_id: '' };
    editingStepIndex.value = null;
}

function submit() {
    if (props.automation?.id) {
        form.put(route('marketing.automations.update', props.automation.id));
    } else {
        form.post(route('marketing.automations.store'));
    }
}
</script>

<template>
    <Head :title="automation?.id ? 'Edit Automation' : 'New Automation'" />
    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column mb-4 mt-2">
                    <div class="flex-grow-1">
                        <Link :href="route('marketing.automations.index')" class="text-muted fs-12 mb-1 d-block"><i class="ti ti-arrow-left me-1"></i> All Automations</Link>
                        <h4 class="fs-18 text-uppercase fw-bold m-0">{{ automation?.id ? 'Edit' : 'Create' }} Automation Sequence</h4>
                    </div>
                    <div class="d-flex gap-2">
                        <button @click="submit" class="btn btn-primary btn-sm px-4 fw-bold shadow-sm" :disabled="form.processing">Save Sequence</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <!-- Sidebar Settings -->
            <div class="col-xl-4 col-md-5">
                <div class="card border shadow-none mb-3">
                     <div class="card-header border-bottom border-dashed py-3 px-3">
                        <h5 class="card-title fs-14 mb-0 text-uppercase fw-bold m-0 text-primary">Automation Settings</h5>
                    </div>
                    <div class="card-body">
                         <div class="mb-3">
                            <label class="form-label fs-11 text-uppercase fw-bold m-0 text-muted">Internal Name</label>
                            <input v-model="form.name" type="text" class="form-control form-control-sm" placeholder="e.g. Welcome Series" required />
                         </div>
                         <div class="mb-3">
                            <label class="form-label fs-11 text-uppercase fw-bold m-0 text-muted">Trigger Event</label>
                            <select v-model="form.trigger_event" class="form-select form-select-sm" required>
                                <option value="">Select Trigger...</option>
                                <option v-for="(label, event) in triggerEvents" :key="event" :value="event">{{ label }}</option>
                            </select>
                            <small class="text-muted fs-10 mt-1 d-block">This event will start the automation for a contact.</small>
                         </div>
                         <div class="mb-0">
                            <label class="form-label fs-11 text-uppercase fw-bold m-0 text-muted">Description</label>
                            <textarea v-model="form.description" class="form-control form-control-sm" rows="2" placeholder="Optional context..."></textarea>
                         </div>
                    </div>
                </div>

                <div class="card border shadow-none bg-light-subtle rounded border-dashed text-center py-4">
                     <Icon icon="solar:clining-robot-bold-duotone" class="fs-36 text-primary mb-2 mx-auto" />
                     <h6 class="fw-bold mb-1">How it works</h6>
                     <p class="fs-11 text-muted px-4 mb-0">Subscribers who trigger the event will enter this sequence and receive emails based on your defined delays.</p>
                </div>
            </div>

            <!-- Visual Flow -->
            <div class="col-xl-8 col-md-7">
                <div class="automation-flow position-relative pb-5">
                    <!-- Trigger Node -->
                    <div class="d-flex justify-content-center mb-4">
                        <div class="node-trigger text-center bg-primary text-white rounded p-3 shadow-sm border border-primary" style="min-width: 200px;">
                            <Icon icon="solar:bolt-bold-duotone" class="fs-24 mb-1" />
                            <h6 class="mb-0 fw-bold fs-13">{{ triggerEvents[form.trigger_event] || 'No Trigger Selected' }}</h6>
                            <p class="fs-10 opacity-75 mb-0">The starting point</p>
                        </div>
                    </div>

                    <!-- Steps -->
                    <div v-for="(step, index) in form.steps" :key="index" class="step-container position-relative">
                        <!-- Connector -->
                        <div class="connector-line mx-auto bg-primary-subtle" style="width: 2px; height: 40px;"></div>

                        <!-- Delay Node -->
                        <div v-if="step.delay_hours > 0" class="d-flex justify-content-center mb-3">
                             <div class="badge bg-white border border-primary-subtle text-primary rounded-pill px-3 py-1 fs-10 fw-normal shadow-sm">
                                <Icon icon="solar:clock-circle-bold-duotone" class="me-1" /> Wait {{ step.delay_hours }} hours
                             </div>
                        </div>
                        <div v-else class="d-flex justify-content-center mb-3">
                             <div class="badge bg-white border border-secondary-subtle text-muted rounded-pill px-3 py-1 fs-10 fw-normal">
                                Send Immediately
                             </div>
                        </div>

                         <!-- Connector -->
                         <div class="connector-line mx-auto bg-primary-subtle" style="width: 2px; height: 10px;"></div>

                        <!-- Action Node -->
                        <div class="d-flex justify-content-center mb-4">
                            <div class="card node-action border shadow-none mb-0 w-75 hover-border-primary transition-all position-relative">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar-sm flex-shrink-0 bg-primary-subtle text-primary rounded d-flex align-items-center justify-content-center">
                                            <Icon icon="solar:letter-bold-duotone" class="fs-20" />
                                        </div>
                                        <div class="overflow-hidden flex-grow-1">
                                            <h6 class="mb-1 text-truncate fs-13">Send Email: {{ templates.find(t => t.id === step.email_template_id)?.name || 'Missing Template' }}</h6>
                                            <p class="fs-10 text-muted mb-0">Step {{ index + 1 }}</p>
                                        </div>
                                        <div class="d-flex gap-1 ms-2">
                                            <button @click="editStep(index)" class="btn btn-sm btn-icon btn-light"><i class="ti ti-edit fs-14"></i></button>
                                            <button @click="removeStep(index)" class="btn btn-sm btn-icon btn-light text-danger"><i class="ti ti-trash fs-14"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add Step Button -->
                    <div class="connector-line mx-auto bg-primary-subtle" style="width: 2px; height: 40px;"></div>
                    <div class="d-flex justify-content-center mt-2">
                         <button @click="showStepModal = true; resetNewStep()" class="btn btn-outline-primary btn-sm border-dashed rounded-pill px-4 fw-bold shadow-sm bg-white hover-bg-primary hover-text-white transition-all">
                            <i class="ti ti-plus me-1"></i> Add Next Step
                         </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Step Modal -->
        <div v-if="showStepModal" class="modal fade show d-block" style="background: rgba(0,0,0,0.4);">
             <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="card-header border-bottom border-dashed py-3 px-3 d-flex justify-content-between align-items-center">
                        <h5 class="card-title fs-16 mb-0">{{ editingStepIndex !== null ? 'Edit Step' : 'New Step' }}</h5>
                        <button @click="showStepModal = false" class="btn-close"></button>
                    </div>
                    <div class="card-body py-4">
                        <div class="mb-3">
                            <label class="form-label fs-11 text-uppercase fw-bold m-0">Send Email Template</label>
                            <select v-model="newStep.email_template_id" class="form-select form-select-sm" required>
                                <option value="">Select Template...</option>
                                <option v-for="t in templates" :key="t.id" :value="t.id">{{ t.name }}</option>
                            </select>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fs-11 text-uppercase fw-bold m-0">Delay After Previous Step (Hours)</label>
                            <input v-model="newStep.delay_hours" type="number" min="0" class="form-control form-control-sm" placeholder="0 = Immediate" />
                        </div>
                    </div>
                    <div class="modal-footer border-top border-dashed">
                        <button @click="showStepModal = false" class="btn btn-sm btn-light">Cancel</button>
                        <button v-if="editingStepIndex !== null" @click="updateStep" class="btn btn-sm btn-primary px-4 fw-bold" :disabled="!newStep.email_template_id">Update Step</button>
                        <button v-else @click="addStep" class="btn btn-sm btn-primary px-4 fw-bold" :disabled="!newStep.email_template_id">Add Step</button>
                    </div>
                </div>
             </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.node-trigger { border-style: solid; }
.hover-border-primary:hover { border-color: #3e60d5 !important; transform: scale(1.02); }
.transition-all { transition: all 0.2s ease-in-out; }
.connector-line { transition: background-color 0.3s; }
.step-container:hover .connector-line { background-color: #3e60d5; }
</style>
