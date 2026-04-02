<script setup>
import { ref, onMounted, computed, nextTick, onUnmounted } from 'vue';
import axios from 'axios';
import MessageList from './MessageList.vue';
import MessageInput from './MessageInput.vue';
import FileSummary from './FileSummary.vue';
import TypingIndicator from './TypingIndicator.vue';
import OnlineUsers from './OnlineUsers.vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    project: {
        type: Object,
        required: true
    }
});

const discussions = ref([]);
const members = ref([]);
const loading = ref(true);
const searchQuery = ref('');
const lastReadId = ref(null);

// Real-time State
const onlineUsers = ref([]);
const usersTyping = ref([]);
const echoChannel = ref(null);

const page = usePage();
const authUser = computed(() => page.props.auth.user);

const fetchDiscussions = async () => {
    loading.value = true;
    try {
        const response = await axios.get(route('projects.discussions.index', props.project.id));
        discussions.value = response.data.discussions.data;
        members.value = response.data.project_members || [];
        lastReadId.value = response.data.read_status?.last_read_message_id || null;
    } catch (error) {
        console.error('Error fetching discussions:', error);
    } finally {
        loading.value = false;
        scrollToBottom();
    }
};

const scrollToBottom = () => {
    nextTick(() => {
        const container = document.getElementById('discussion-scroll');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    });
};

const markAsRead = async () => {
    if (discussions.value.length === 0) return;
    const latestMessageId = discussions.value[discussions.value.length - 1].id;
    if (latestMessageId === lastReadId.value) return;

    try {
        await axios.post(route('projects.discussions.read', props.project.id), {
            last_read_message_id: latestMessageId
        });
        lastReadId.value = latestMessageId;
    } catch (error) {
        console.error('Error marking as read:', error);
    }
};

const setupEcho = () => {
    if (!window.Echo) {
        console.error('Echo is not initialized. Please ensure bootstrap.js is set up correctly.');
        return;
    }

    // Join Private/Presence channel
    echoChannel.value = window.Echo.join(`project.${props.project.id}`)
        .here((users) => {
            onlineUsers.value = users;
        })
        .joining((user) => {
            onlineUsers.value.push(user);
        })
        .leaving((user) => {
            onlineUsers.value = onlineUsers.value.filter(u => u.id !== user.id);
        })
        .listen('.discussion.message.new', (e) => {
            handleNewMessage(e.message);
        })
        .listen('.discussion.message.updated', (e) => {
            handleMessageUpdated(e.message);
        })
        .listen('.discussion.message.deleted', (e) => {
            handleMessageDeleted(e);
        })
        .listen('.discussion.read', (e) => {
            handleReadEvent(e);
        })
        .listenForWhisper('typing', (e) => {
            handleTypingIndicator(e);
        });
};

const handleNewMessage = (newMessage) => {
    if (newMessage.parent_id) {
        const parent = discussions.value.find(d => d.id === newMessage.parent_id);
        if (parent) {
            if (!parent.replies) parent.replies = [];
            parent.replies.push(newMessage);
        }
    } else {
        discussions.value.push(newMessage);
        scrollToBottom();
    }
};

const handleMessageUpdated = (updatedMessage) => {
    if (updatedMessage.parent_id) {
        const parent = discussions.value.find(d => d.id === updatedMessage.parent_id);
        if (parent) {
            const index = parent.replies.findIndex(r => r.id === updatedMessage.id);
            if (index !== -1) parent.replies[index] = updatedMessage;
        }
    } else {
        const index = discussions.value.findIndex(d => d.id === updatedMessage.id);
        if (index !== -1) discussions.value[index] = updatedMessage;
    }
};

const handleMessageDeleted = (e) => {
    if (e.parentId) {
        const parent = discussions.value.find(d => d.id === e.parentId);
        if (parent) {
            parent.replies = parent.replies.filter(r => r.id !== e.messageId);
        }
    } else {
        discussions.value = discussions.value.filter(d => d.id !== e.messageId);
    }
};

const handleReadEvent = (e) => {
    // Only update if it's not the current user (own read receipts handled locally)
    if (e.userId === authUser.value.id) return;

    // Update all messages in discussions that were read by this user
    // A production app would compare dates. For now, we update read_by array.
    discussions.value.forEach(msg => {
        if (!msg.read_by) msg.read_by = [];
        if (!msg.read_by.some(u => u.id === e.userId)) {
            // Check if msg date is <= last read date (would need last read msg data)
            // Simplified: adding to the read_by list for messages.
            msg.read_by.push({ id: e.userId, name: e.userName });
        }
    });
};

const handleTypingIndicator = (e) => {
    if (e.isTyping) {
        if (!usersTyping.value.some(u => u.id === e.userId)) {
            usersTyping.value.push({ id: e.userId, name: e.userName });
        }
    } else {
        usersTyping.value = usersTyping.value.filter(u => u.id !== e.userId);
    }
};

const sendTypingWhisper = (isTyping) => {
    if (echoChannel.value) {
        echoChannel.value.whisper('typing', {
            userId: authUser.value.id,
            userName: authUser.value.name,
            isTyping: isTyping
        });
    }
};

const filteredDiscussions = computed(() => {
    if (!searchQuery.value) return discussions.value;
    const query = searchQuery.value.toLowerCase();
    return discussions.value.filter(d => 
        (d.message && d.message.toLowerCase().includes(query)) ||
        (d.user && d.user.name.toLowerCase().includes(query))
    );
});

onMounted(() => {
    fetchDiscussions();
    setupEcho();
    
    // Auto-mark as read when scroll bottom
    const container = document.getElementById('discussion-scroll');
    if (container) {
        container.addEventListener('scroll', () => {
            if (container.scrollHeight - container.scrollTop - container.clientHeight < 50) {
                markAsRead();
            }
        });
    }
});

onUnmounted(() => {
    if (echoChannel.value) {
        window.Echo.leave(`project.${props.project.id}`);
    }
});
</script>

<template>
    <div class="row discussion-container p-2">
        <div class="col-lg-8 col-md-12">
            <div class="card shadow border-0 rounded-4 h-100 mb-0 overflow-hidden">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-bottom px-4">
                    <div class="d-flex align-items-center">
                        <div class="header-icon-box bg-primary-subtle p-2 rounded-3 me-3 text-primary">
                            <i class="ti ti-messages fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold">Live Stream</h5>
                            <span class="x-small text-muted fw-normal">{{ onlineUsers.length }} users online</span>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <div class="search-box position-relative" style="width: 250px;">
                            <i class="ti ti-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                            <input v-model="searchQuery" type="text" class="form-control form-control-sm ps-5 bg-light border-0 rounded-pill" placeholder="Jump to message...">
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-0 d-flex flex-column h-600 bg-white shadow-inner">
                    <!-- Message List Area -->
                    <div class="flex-grow-1 overflow-auto p-4 custom-scrollbar" id="discussion-scroll">
                        <div v-if="loading" class="d-flex justify-content-center align-items-center h-100">
                            <div class="spinner-grow text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        
                        <div v-else-if="filteredDiscussions.length === 0" class="d-flex flex-column justify-content-center align-items-center h-100 text-muted opacity-50">
                            <i class="ti ti-message-dots fs-1 mb-2"></i>
                            <p class="fw-bold">Start a live conversation</p>
                        </div>
                        
                        <MessageList 
                            v-else 
                            :discussions="filteredDiscussions" 
                            :project="project"
                            :members="members"
                            :last-read-id="lastReadId"
                            @message-updated="fetchDiscussions" 
                            @message-deleted="fetchDiscussions"
                        />
                    </div>
                    
                    <!-- Feedback (Typing Indicator) -->
                    <TypingIndicator :users="usersTyping" />
                    
                    <!-- Input Area -->
                    <div class="p-3 bg-white border-top">
                        <MessageInput 
                            :project="project" 
                            :members="members"
                            @sent="handleNewMessage" 
                            @typing="sendTypingWhisper"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4 col-md-12 mt-4 mt-lg-0">
            <div class="sticky-top" style="top: 2rem;">
                <FileSummary :project="project" :discussions="discussions" />
                <OnlineUsers :members="members" :online-users="onlineUsers" />
            </div>
        </div>
    </div>
</template>

<style scoped>
.h-600 {
    height: 650px;
}

.discussion-container {
    animation: fadeIn 0.4s ease-out;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #f1f5f9;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background-color: #e2e8f0;
}

.header-icon-box {
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.x-small {
    font-size: 0.75rem;
}

.shadow-inner {
    box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.02);
}

@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.98); }
    to { opacity: 1; transform: scale(1); }
}
</style>
