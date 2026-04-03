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
const replyTo = ref(null);

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

const handleReplyStart = (msg) => {
    replyTo.value = msg;
    nextTick(() => {
        const input = document.querySelector('.message-input-container textarea');
        if (input) input.focus();
    });
};

const cancelReply = () => {
    replyTo.value = null;
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

let handleGlobalKeydown = null;

onMounted(() => {
    fetchDiscussions();
    
    // Global keyboard shortcut for search
    handleGlobalKeydown = (e) => {
        if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
            e.preventDefault();
            searchInput.value?.focus();
        }
    };
    
    window.addEventListener('keydown', handleGlobalKeydown);
    
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
    if (handleGlobalKeydown) {
        window.removeEventListener('keydown', handleGlobalKeydown);
    }
});
</script>

<template>
    <div class="row g-0 h-100 flex-nowrap overflow-hidden">
        <!-- Main Chat Area -->
        <div class="col-12 col-lg-8 d-flex flex-column border-end position-relative h-100 bg-white">
            <!-- Header -->
            <div class="p-3 border-bottom d-flex align-items-center justify-content-between bg-white sticky-top z-1 shadow-sm">
                <div class="d-flex align-items-center flex-shrink-1 overflow-hidden me-2">
                    <div class="bg-primary-subtle text-primary p-2 rounded-3 me-3 d-none d-sm-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="ti ti-messages fs-4"></i>
                    </div>
                    <div class="overflow-hidden">
                        <h5 class="mb-0 fw-bold text-truncate">{{ project.name }} - Discussion</h5>
                        <div class="d-flex align-items-center text-success small">
                            <span class="pulse-dot me-2"></span>
                            <span>{{ onlineUsers.length }} active now</span>
                        </div>
                    </div>
                </div>
                
                <div class="search-box position-relative flex-grow-1 flex-md-grow-0 me-1" style="max-width: 280px;">
                    <i class="ti ti-search position-absolute top-50 translate-middle-y start-0 ms-3 text-muted opacity-75" style="font-size: 0.95rem;"></i>
                    <input 
                        ref="searchInput"
                        v-model="searchQuery" 
                        type="text" 
                        class="form-control form-control-sm bg-light-subtle border-0 rounded-pill search-input shadow-none transition-all" 
                        style="padding-left: 2.75rem; padding-right: 3.5rem;"
                        placeholder="Search messages... (/)"
                        @keydown.esc="searchQuery = ''; $event.target.blur();"
                    >
                    <div class="position-absolute top-50 end-0 translate-middle-y me-2 d-flex align-items-center gap-1">
                        <span v-if="searchQuery && filteredDiscussions.length > 0" class="badge bg-primary rounded-pill x-small px-2 py-1 opacity-75">
                            {{ filteredDiscussions.length }} {{ filteredDiscussions.length === 1 ? 'match' : 'matches' }}
                        </span>
                        <button v-if="searchQuery" @click="searchQuery = ''; $refs.searchInput.focus();" class="btn btn-link btn-sm p-1 text-muted border-0 shadow-none hover-text-danger">
                            <i class="ti ti-x fs-6"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Message List Area -->
            <div class="flex-grow-1 d-flex flex-column h-600-responsive bg-white shadow-inner overflow-hidden">
                <div class="flex-grow-1 overflow-auto p-2 p-sm-4 custom-scrollbar" id="discussion-scroll" style="min-width: 0;">
                    <div v-if="loading" class="d-flex justify-content-center align-items-center h-100">
                        <div class="spinner-grow text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    
                    <!-- Empty State for Search -->
                    <div v-else-if="searchQuery && filteredDiscussions.length === 0" class="d-flex flex-column justify-content-center align-items-center h-100 text-muted transition-all">
                        <div class="bg-light p-4 rounded-circle mb-3">
                            <i class="ti ti-search-off fs-1 opacity-50"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">No matches found</h5>
                        <p class="small text-muted mb-3">We couldn't find anything for "{{ searchQuery }}"</p>
                        <button @click="searchQuery = ''" class="btn btn-sm btn-outline-primary rounded-pill px-4">Clear search</button>
                    </div>
                    
                    <!-- Empty State for Empty Chat -->
                    <div v-else-if="!searchQuery && discussions.length === 0" class="d-flex flex-column justify-content-center align-items-center h-100 text-muted opacity-50">
                        <i class="ti ti-message-dots fs-1 mb-2"></i>
                        <p class="fw-bold text-dark">Start a live conversation</p>
                    </div>
                    
                    <MessageList 
                        v-else 
                        :discussions="filteredDiscussions" 
                        :project="project"
                        :members="members"
                        :search-query="searchQuery"
                        :last-read-id="lastReadId"
                        @message-updated="fetchDiscussions" 
                        @message-deleted="fetchDiscussions"
                        @reply="handleReplyStart"
                    />
                </div>
                
                <!-- Feedback (Typing Indicator) -->
                <TypingIndicator :users="usersTyping" />
                
                <!-- Input Area -->
                <div class="p-3 bg-white border-top">
                    <MessageInput 
                        :project="project" 
                        :members="members"
                        :reply-to="replyTo"
                        :parent-id="replyTo?.id"
                        @sent="(msg) => { handleNewMessage(msg); cancelReply(); }" 
                        @typing="sendTypingWhisper"
                        @cancel-reply="cancelReply"
                    />
                </div>
            </div>
        </div>

        <!-- Right Info Sidebar -->
        <div class="col-lg-4 d-none d-xl-flex flex-column bg-light-subtle h-100 border-start overflow-hidden">
            <div class="flex-grow-1 overflow-auto custom-scrollbar">
                <div class="p-4">
                    <FileSummary :project="project" :discussions="discussions" />
                    <div class="my-4"></div>
                    <OnlineUsers 
                        :project="project" 
                        :members="members" 
                        :online-users="onlineUsers" 
                        @member-added="fetchDiscussions" 
                        @member-removed="fetchDiscussions"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.h-600-responsive {
    height: 70vh;
}

@media (min-width: 992px) {
    .h-600-responsive {
        height: 650px;
    }
}

@media (max-width: 576px) {
    .small-mobile-title {
        font-size: 1rem;
    }
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

.transition-all {
    transition: all 0.2s ease;
}

.hover-opacity-100:hover {
    opacity: 1 !important;
}

.search-input {
    transition: all 0.3s ease;
    border: 1px solid transparent !important;
}

.search-input:focus {
    background-color: #fff !important;
    border-color: #6366f1 !important;
    box-shadow: 0 0 0 0.25rem rgba(99, 102, 241, 0.1) !important;
}

@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.98); }
    to { opacity: 1; transform: scale(1); }
}
</style>
