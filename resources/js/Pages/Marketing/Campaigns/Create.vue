<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    templates: Array,
    lists: Array,
});

const form = useForm({
    name: '',
    subject: '',
    from_name: '',
    from_email: '',
    reply_to: '',
    email_template_id: '',
    mailing_list_id: '',
    scheduled_at: '',
});

const currentStep = ref(1);

function nextStep() { if (currentStep.value < 4) currentStep.value++; }
function prevStep() { if (currentStep.value > 1) currentStep.value--; }

function submit() {
    form.post(route('marketing.campaigns.store'));
}
</script>

<template>
    <Head title="Create Campaign" />
    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column mb-4 mt-2">
                    <div class="flex-grow-1">
                        <h4 class="fs-18 text-uppercase fw-bold m-0">Create New Campaign</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border shadow-none mb-0">
            <div class="card-header border-bottom border-dashed py-3 px-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-1 overflow-hidden">
                        <div v-for="s in 4" :key="s" class="d-flex align-items-center gap-1">
                            <span class="avatar-xs d-flex align-items-center justify-content-center rounded-circle fs-10 fw-bold border" :class="[currentStep >= s ? 'bg-primary text-white border-primary' : 'bg-light text-muted border-secondary-subtle']">{{ s }}</span>
                            <span v-if="s < 4" class="fs-10 text-muted mx-1">---</span>
                        </div>
                    </div>
                    <div class="d-flex gap-1">
                        <span v-if="currentStep === 1" class="badge text-bg-light border text-muted fs-11 fw-normal">Basic Info</span>
                        <span v-if="currentStep === 2" class="badge text-bg-light border text-muted fs-11 fw-normal">Email Content</span>
                        <span v-if="currentStep === 3" class="badge text-bg-light border text-muted fs-11 fw-normal">Audience & Schedule</span>
                        <span v-if="currentStep === 4" class="badge text-bg-light border text-muted fs-11 fw-normal">Review & Launch</span>
                    </div>
                </div>
            </div>

            <div class="card-body py-4">
                <div class="row justify-content-center">
                    <div class="col-xl-8">
                        <form @submit.prevent="submit">
                            <!-- Step 1: Basics -->
                            <div v-if="currentStep === 1" class="animate-fade-in">
                                <h5 class="mb-4 text-center">Let's start with the basics</h5>
                                <div class="mb-3">
                                    <label class="form-label fs-12 text-uppercase fw-bold letter-spacing-1">Campaign Name</label>
                                    <input v-model="form.name" type="text" class="form-control" placeholder="e.g. April Monthly Newsletter" required />
                                    <div v-if="form.errors.name" class="text-danger fs-11 mt-1">{{ form.errors.name }}</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fs-12 text-uppercase fw-bold letter-spacing-1">Email Subject Line</label>
                                    <input v-model="form.subject" type="text" class="form-control" placeholder="What your subscribers will see in their inbox" required />
                                    <div v-if="form.errors.subject" class="text-danger fs-11 mt-1">{{ form.errors.subject }}</div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fs-12 text-uppercase fw-bold letter-spacing-1">From Name</label>
                                        <input v-model="form.from_name" type="text" class="form-control" placeholder="e.g. Support Team" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fs-12 text-uppercase fw-bold letter-spacing-1">From Email</label>
                                        <input v-model="form.from_email" type="email" class="form-control" placeholder="e.g. support@example.com" />
                                    </div>
                                </div>
                            </div>

                            <!-- Step 2: Template -->
                            <div v-if="currentStep === 2" class="animate-fade-in">
                                <h5 class="mb-4 text-center">Choose your design</h5>
                                <div class="row g-3">
                                    <div v-for="template in templates" :key="template.id" class="col-md-6">
                                        <div @click="form.email_template_id = template.id" class="cursor-pointer border rounded p-3 transition-all h-100" :class="form.email_template_id === template.id ? 'border-primary bg-primary-subtle' : 'border-dashed border-secondary-subtle bg-light-subtle hover-bg-light'">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="avatar-sm bg-white border border-dashed rounded-circle d-flex align-items-center justify-content-center">
                                                    <i class="ti ti-template fs-18 text-primary"></i>
                                                </div>
                                                <div class="overflow-hidden">
                                                    <h6 class="mb-1 text-truncate fs-14">{{ template.name }}</h6>
                                                    <p class="text-muted fs-11 mb-0 text-truncate">{{ template.subject }}</p>
                                                </div>
                                                <div v-if="form.email_template_id === template.id" class="ms-auto">
                                                    <i class="ti ti-circle-check-filled text-primary fs-20"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="templates.length === 0" class="col-12 text-center text-muted">
                                        <p>No templates found. Go to <Link :href="route('marketing.templates.index')" class="text-primary fw-bold">Templates</Link> to create one.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 3: Audience & Schedule -->
                            <div v-if="currentStep === 3" class="animate-fade-in">
                                <h5 class="mb-4 text-center">Who are we sending to?</h5>
                                <div class="mb-4">
                                     <label class="form-label fs-12 text-uppercase fw-bold letter-spacing-1">Select Mailing List</label>
                                     <select v-model="form.mailing_list_id" class="form-select" required>
                                        <option value="">Select a list...</option>
                                        <option v-for="list in lists" :key="list.id" :value="list.id">{{ list.name }} ({{ list.subscribers_count }} subscribers)</option>
                                     </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fs-12 text-uppercase fw-bold letter-spacing-1">Schedule (Optional)</label>
                                    <input v-model="form.scheduled_at" type="datetime-local" class="form-control" />
                                    <small class="text-muted d-block mt-1">Leave empty to send immediately after launch.</small>
                                </div>
                            </div>

                            <!-- Step 4: Review -->
                            <div v-if="currentStep === 4" class="animate-fade-in text-center">
                                <h5 class="mb-4">Everything looks good?</h5>
                                <div class="bg-light p-4 rounded text-start mb-4 border border-dashed">
                                    <div class="row g-3">
                                        <div class="col-md-6 border-end border-dashed border-secondary-subtle">
                                            <p class="mb-1 text-muted fs-11 text-uppercase fw-bold letter-spacing-1">Campaign Info</p>
                                            <h6 class="mb-2">{{ form.name }}</h6>
                                            <p class="mb-0 fs-12"><strong>Subject:</strong> {{ form.subject }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1 text-muted fs-11 text-uppercase fw-bold letter-spacing-1">Recipient List</p>
                                            <h6 class="mb-2">{{ lists.find(l => l.id === form.mailing_list_id)?.name || 'No list selected' }}</h6>
                                            <p class="mb-0 fs-12"><strong>Schedule:</strong> {{ form.scheduled_at || 'Immediate' }}</p>
                                        </div>
                                    </div>
                                </div>

                                <button @click="submit" class="btn btn-primary px-5 py-2 fw-bold shadow-sm" :disabled="form.processing">
                                    <i class="ti ti-rocket me-2"></i> Launch Campaign
                                </button>
                            </div>

                            <!-- Navigation Buttons -->
                            <div class="d-flex justify-content-between mt-5 pt-3 border-top border-dashed">
                                <button type="button" v-if="currentStep > 1" @click="prevStep" class="btn btn-light"><i class="ti ti-arrow-left me-1"></i> Back</button>
                                <div v-else></div>
                                <button type="button" v-if="currentStep < 4" @click="nextStep" class="btn btn-primary" :disabled="!form.name || (currentStep === 2 && !form.email_template_id) || (currentStep === 3 && !form.mailing_list_id)">Next <i class="ti ti-arrow-right ms-1"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-fade-in { animation: fadeIn 0.3s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.letter-spacing-1 { letter-spacing: 0.5px; }
.hover-bg-light:hover { background-color: rgba(0,0,0,0.02) !important; }
.transition-all { transition: all 0.2s ease-in-out; }
</style>
