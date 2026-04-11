<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    template: Object,
});

const form = useForm({
    name: props.template?.name || '',
    subject: props.template?.subject || '',
    category: props.template?.category || '',
    editor_type: props.template?.editor_type || 'quill',
    html_content: props.template?.html_content || '',
    mjml_content: props.template?.mjml_content || '',
});

const editorRef = ref(null);
let editorInstance = null;

onMounted(() => {
    if (form.editor_type === 'dragdrop') {
        initGrapes();
    } else {
        initQuill();
    }
});

onBeforeUnmount(() => {
    if (editorInstance) {
        if (typeof editorInstance.destroy === 'function') editorInstance.destroy();
    }
});

function initQuill() {
    if (window.Quill) {
        editorInstance = new window.Quill(editorRef.value, {
            theme: 'snow',
            placeholder: 'Write your email content here...',
            modules: { toolbar: true },
        });
        editorInstance.root.innerHTML = form.html_content;
        editorInstance.on('text-change', () => {
            form.html_content = editorInstance.root.innerHTML;
        });
    } else {
        setTimeout(initQuill, 300);
    }
}

function initGrapes() {
    if (window.grapesjs) {
        editorInstance = window.grapesjs.init({
            container: editorRef.value,
            fromElement: true,
            storageManager: false,
            plugins: ['grapesjs-preset-newsletter'],
        });
        editorInstance.setComponents(form.html_content || '<div>Start designing</div>');
        editorInstance.on('update', () => {
             form.html_content = editorInstance.runCommand('gjs-get-inlined-html');
        });
    } else {
        setTimeout(initGrapes, 300);
    }
}

function submit() {
    if (props.template?.id) {
        form.put(route('marketing.templates.update', props.template.id));
    } else {
        form.post(route('marketing.templates.store'));
    }
}
</script>

<template>
    <Head :title="template?.id ? 'Edit Template' : 'Design Template'" />
    <AuthenticatedLayout>
        <div class="row">
            <div class="col-12">
                <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column mb-4 mt-2">
                    <div class="flex-grow-1">
                        <Link :href="route('marketing.templates.index')" class="text-muted fs-12 mb-1 d-block"><i class="ti ti-arrow-left me-1"></i> Back to Templates</Link>
                        <h4 class="fs-18 text-uppercase fw-bold m-0">{{ template?.id ? 'Edit' : 'Create' }} Email Template</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-xl-9">
                <div class="card border shadow-none mb-0">
                    <div class="card-header border-bottom border-dashed py-3 px-3 d-flex justify-content-between align-items-center">
                        <h5 class="card-title fs-16 mb-0">{{ form.editor_type === 'dragdrop' ? 'Newsletter Builder' : 'Rich Text Content' }}</h5>
                        <div class="d-flex gap-2 align-items-center">
                            <span v-if="form.processing" class="spinner-border spinner-border-sm text-primary"></span>
                            <button @click="submit" class="btn btn-sm btn-primary fw-bold px-4" :disabled="form.processing">Save Changes</button>
                        </div>
                    </div>
                    <div class="card-body p-0 border-bottom border-dashed">
                        <!-- TinyMCE or GrapesJS container -->
                        <div ref="editorRef" :style="{ minHeight: form.editor_type === 'dragdrop' ? '600px' : '400px' }"></div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3">
                 <div class="card border shadow-none mb-3">
                    <div class="card-header border-bottom border-dashed py-3">
                        <h5 class="card-title fs-14 mb-0 text-uppercase fw-bold m-0">Template Details</h5>
                    </div>
                    <div class="card-body">
                         <div class="mb-3">
                            <label class="form-label fs-11 text-uppercase fw-bold m-0">Template Name</label>
                            <input v-model="form.name" type="text" class="form-control form-control-sm" placeholder="Internal name" required />
                         </div>
                         <div class="mb-3">
                            <label class="form-label fs-11 text-uppercase fw-bold m-0">Default Subject</label>
                            <input v-model="form.subject" type="text" class="form-control form-control-sm" placeholder="Email subject line" />
                         </div>
                         <div class="mb-0">
                            <label class="form-label fs-11 text-uppercase fw-bold m-0">Category</label>
                            <input v-model="form.category" type="text" class="form-control form-control-sm" placeholder="e.g. Onboarding" />
                         </div>
                    </div>
                 </div>

                 <div class="card border shadow-none mb-0">
                    <div class="card-header border-bottom border-dashed py-3">
                        <h5 class="card-title fs-14 mb-0 text-uppercase fw-bold m-0">Helpful Shortcuts</h5>
                    </div>
                    <div class="card-body">
                         <p class="fs-12 text-muted mb-2">Use tags to personalize your emails:</p>
                         <ul class="list-unstyled mb-0 d-flex flex-wrap gap-1">
                            <li v-for="tag in ['{first_name}', '{last_name}', '{email}', '{unsubscribe_url}', '{company_name}']" :key="tag">
                                <span class="badge text-bg-light border fs-10 cursor-pointer" @click="navigator.clipboard.writeText(tag)">{{ tag }}</span>
                            </li>
                         </ul>
                    </div>
                 </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
/* Reset some GrapesJS defaults to fit theme */
.gjs-cv-canvas { background-color: #f8fafc; }
.ql-toolbar { border-top: 0 !important; border-left: 0 !important; border-right: 0 !important; border-color: #e2e8f0 !important; }
.ql-container { border: 0 !important; font-family: 'Inter', sans-serif !important; font-size: 14px !important; }
</style>
