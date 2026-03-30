<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    project: Object
});

const files = ref([]);
const loading = ref(true);
const uploading = ref(false);
const fileInput = ref(null);
const uploadError = ref('');

// Share Modal State
const showShareModal = ref(false);
const selectedFile = ref(null);
const shareExpiresAt = ref('');
const shareUrl = ref('');
const isUnlimited = ref(true);

const fetchFiles = async () => {
    loading.ref = true;
    try {
        const response = await axios.get(route('projects.files.index', props.project.id));
        files.value = response.data;
    } catch (error) {
        console.error('Error fetching files:', error);
    } finally {
        loading.value = false;
    }
};

const handleFileUpload = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('file', file);

    uploading.value = true;
    uploadError.value = '';

    try {
        const response = await axios.post(route('projects.files.upload', props.project.id), formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        files.value.unshift(response.data);
        if (fileInput.value) fileInput.value.value = '';
    } catch (error) {
        uploadError.value = error.response?.data?.message || 'Upload failed. Check file size.';
    } finally {
        uploading.value = false;
    }
};

const downloadFile = (file) => {
    window.open(route('projects.files.download', file.id), '_blank');
};

const deleteFile = async (file) => {
    if (!confirm('Are you sure you want to delete this file?')) return;

    try {
        await axios.delete(route('projects.files.destroy', file.id));
        files.value = files.value.filter(f => f.id !== file.id);
    } catch (error) {
        alert('Failed to delete file.');
    }
};

const openShareModal = (file) => {
    selectedFile.value = file;
    shareUrl.value = file.share_token ? route('public.files.download', file.share_token) : '';
    shareExpiresAt.value = file.expires_at ? file.expires_at.split('T')[0] : '';
    isUnlimited.value = !file.expires_at;
    showShareModal.value = true;
};

const generateShareLink = async () => {
    try {
        const response = await axios.post(route('projects.files.share', selectedFile.value.id), {
            expires_at: isUnlimited.value ? null : shareExpiresAt.value
        });
        shareUrl.value = response.data.share_url;
        // Update local file state
        const idx = files.value.findIndex(f => f.id === selectedFile.value.id);
        if (idx !== -1) {
            files.value[idx].share_token = response.data.share_url.split('/').pop();
            files.value[idx].expires_at = response.data.expires_at;
        }
    } catch (error) {
        alert('Failed to generate share link.');
    }
};

const revokeShareLink = async () => {
    try {
        await axios.delete(route('projects.files.revoke-share', selectedFile.value.id));
        shareUrl.value = '';
        const idx = files.value.findIndex(f => f.id === selectedFile.value.id);
        if (idx !== -1) {
            files.value[idx].share_token = null;
            files.value[idx].expires_at = null;
        }
    } catch (error) {
        alert('Failed to revoke share link.');
    }
};

const copyToClipboard = () => {
    navigator.clipboard.writeText(shareUrl.value);
    alert('Link copied to clipboard!');
};

const formatSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString();
};

onMounted(fetchFiles);
</script>

<template>
    <div class="card border-0 shadow-none">
        <div class="card-body p-0">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="header-title mb-1">Project Shared Files</h4>
                    <p class="text-muted font-13 mb-0">Securely share files between admin and client for this project.</p>
                </div>
                <div>
                    <input type="file" ref="fileInput" @change="handleFileUpload" class="d-none" id="fileUploadInput" />
                    <label for="fileUploadInput" class="btn btn-primary" :class="{ 'disabled': uploading }">
                        <i class="ti ti-upload me-1"></i>
                        {{ uploading ? 'Uploading...' : 'Upload New File' }}
                    </label>
                </div>
            </div>

            <div v-if="uploadError" class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ uploadError }}
                <button type="button" class="btn-close" @click="uploadError = ''"></button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-centered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>File Name</th>
                            <th>Size</th>
                            <th>Uploaded By</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="loading">
                            <td colspan="6" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status"></div>
                            </td>
                        </tr>
                        <tr v-else v-for="file in files" :key="file.id">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-3">
                                        <div class="avatar-title bg-light text-primary rounded">
                                            <i class="ti ti-file font-20"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h5 class="font-14 mb-0"><a href="javascript:void(0)" @click="downloadFile(file)" class="text-dark">{{ file.name }}</a></h5>
                                        <span class="text-muted font-12">{{ file.mime_type }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>{{ formatSize(file.size) }}</td>
                            <td>{{ file.user?.name || 'Unknown' }}</td>
                            <td>{{ formatDate(file.created_at) }}</td>
                            <td>
                                <span v-if="file.share_token" class="badge bg-success-subtle text-success border border-success">Shared</span>
                                <span v-else class="badge bg-light text-dark border">Private</span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <button @click="downloadFile(file)" class="btn btn-sm btn-light-primary" title="Download">
                                        <i class="ti ti-download"></i>
                                    </button>
                                    <button @click="openShareModal(file)" class="btn btn-sm btn-light-success" title="Share">
                                        <i class="ti ti-share"></i>
                                    </button>
                                    <button v-if="$page.props.auth.roles.includes('admin') || $page.props.auth.user.id === file.user_id" @click="deleteFile(file)" class="btn btn-sm btn-light-danger" title="Delete">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!loading && files.length === 0">
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="ti ti-folder-off font-40 d-block mb-2"></i>
                                    No files shared yet.
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Share Modal -->
    <div v-if="showShareModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5)">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Share File: {{ selectedFile?.name }}</h5>
                    <button type="button" class="btn-close btn-close-white" @click="showShareModal = false"></button>
                </div>
                <div class="modal-body">
                    <div v-if="shareUrl" class="mb-4">
                        <label class="form-label">Shareable Link</label>
                        <div class="input-group">
                            <input type="text" class="form-control" :value="shareUrl" readonly />
                            <button class="btn btn-outline-primary" @click="copyToClipboard">
                                <i class="ti ti-copy"></i>
                            </button>
                        </div>
                        <p class="text-muted font-12 mt-1">Anyone with this link can view and download the file.</p>
                        
                        <div class="alert alert-info py-2 px-3 font-13 mb-0 mt-3" v-if="selectedFile?.expires_at">
                            <i class="ti ti-clock me-1"></i> Expires on: {{ formatDate(selectedFile.expires_at) }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label d-block fw-bold">Expiration Setting</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" v-model="isUnlimited" :value="true" id="expUnlimited">
                                <label class="form-check-label" for="expUnlimited">Unlimited Time</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" v-model="isUnlimited" :value="false" id="expCustom">
                                <label class="form-check-label" for="expCustom">Set Expiration</label>
                            </div>
                        </div>
                    </div>

                    <div v-if="!isUnlimited" class="mb-3 animate-fade-in">
                        <label class="form-label fw-bold">Expiration Date</label>
                        <input type="date" class="form-control shadow-sm" v-model="shareExpiresAt" :min="new Date().toISOString().split('T')[0]" />
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button v-if="shareUrl" type="button" class="btn btn-outline-danger me-auto" @click="revokeShareLink">Revoke Link</button>
                    <button type="button" class="btn btn-light" @click="showShareModal = false">Close</button>
                    <button type="button" class="btn btn-primary" @click="generateShareLink">
                        {{ shareUrl ? 'Update Link' : 'Generate Link' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.btn-light-primary { background: #eef2f7; color: #727cf5; border: none; }
.btn-light-primary:hover { background: #727cf5; color: #fff; }
.btn-light-success { background: #e3f9eb; color: #0acf97; border: none; }
.btn-light-success:hover { background: #0acf97; color: #fff; }
.btn-light-danger { background: #fae9ed; color: #fa5c7c; border: none; }
.btn-light-danger:hover { background: #fa5c7c; color: #fff; }
</style>
