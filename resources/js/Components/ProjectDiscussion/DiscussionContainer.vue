<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import MessageList from './MessageList.vue';
import MessageInput from './MessageInput.vue';
import FileSummary from './FileSummary.vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    project: {
        type: Object,
        required: true
    }
});

const discussions = ref([]);
const loading = ref(true);
const searchQuery = ref('');
const showFilesOnly = ref(false);

const fetchDiscussions = async () => {
    loading.value = true;
    try {
        const response = await axios.get(route('projects.discussions.index', props.project.id));
        discussions.value = response.data.data;
    } catch (error) {
        console.error('Error fetching discussions:', error);
    } finally {
        loading.value = false;
    }
};

const handleMessageSent = (newMessage) => {
    if (newMessage.parent_id) {
        // Find parent and add reply
        const parent = discussions.value.find(d => d.id === newMessage.parent_id);
        if (parent) {
            if (!parent.replies) parent.replies = [];
            parent.replies.push(newMessage);
        }
    } else {
        discussions.value.unshift(newMessage);
    }
};

const filteredDiscussions = computed(() => {
    let filtered = discussions.value;
    
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(d => 
            (d.message && d.message.toLowerCase().includes(query)) ||
            (d.attachments && d.attachments.some(a => a.file_name.toLowerCase().includes(query))) ||
            (d.replies && d.replies.some(r => r.message && r.message.toLowerCase().includes(query)))
        );
    }

    if (showFilesOnly.value) {
        filtered = filtered.filter(d => (d.attachments && d.attachments.length > 0) || (d.replies && d.replies.some(r => r.attachments && r.attachments.length > 0)));
    }

    return filtered;
});

onMounted(() => {
    fetchDiscussions();
});
</script>

<template>
    <div class="row discussion-container">
        <div class="col-lg-8 col-md-12">
            <div class="card shadow-sm border-0 h-100 mb-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-bottom">
                    <h5 class="mb-0 fw-bold"><i class="ti ti-messages me-2 text-primary"></i> Project Discussion</h5>
                    <div class="d-flex gap-2">
                        <div class="input-group input-group-sm" style="width: 200px;">
                            <span class="input-group-text bg-light border-end-0"><i class="ti ti-search text-muted"></i></span>
                            <input v-model="searchQuery" type="text" class="form-control bg-light border-start-0" placeholder="Search messages...">
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-0 d-flex flex-column" style="height: 600px;">
                    <!-- Message List Area -->
                    <div class="flex-grow-1 overflow-auto p-4 bg-light-subtle" id="discussion-scroll">
                        <div v-if="loading" class="d-flex justify-content-center align-items-center h-100">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        
                        <div v-else-if="filteredDiscussions.length === 0" class="d-flex flex-column justify-content-center align-items-center h-100 text-muted">
                            <i class="ti ti-message-off fs-1 mb-2"></i>
                            <p>No messages found in this project.</p>
                        </div>
                        
                        <MessageList 
                            v-else 
                            :discussions="filteredDiscussions" 
                            :project="project"
                            @message-updated="fetchDiscussions" 
                            @message-deleted="fetchDiscussions"
                        />
                    </div>
                    
                    <!-- Input Area -->
                    <div class="border-top p-3 bg-white">
                        <MessageInput :project="project" @sent="handleMessageSent" />
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-12 mt-4 mt-lg-0">
            <FileSummary :project="project" :discussions="discussions" />
        </div>
    </div>
</template>

<style scoped>
.discussion-container {
    animation: fadeIn 0.3s ease-in-out;
}

#discussion-scroll {
    scroll-behavior: smooth;
}

#discussion-scroll::-webkit-scrollbar {
    width: 6px;
}

#discussion-scroll::-webkit-scrollbar-thumb {
    background-color: #e2e8f0;
    border-radius: 10px;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
