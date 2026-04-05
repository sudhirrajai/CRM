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
const isSearchFocused = ref(false);
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
    if (e.userId === authUser.value.id) return;
    discussions.value.forEach(msg => {
        if (!msg.read_by) msg.read_by = [];
        if (!msg.read_by.some(u => u.id === e.userId)) {
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

const searchInput = ref(null);
let handleGlobalKeydown = null;

onMounted(() => {
    fetchDiscussions();
    
    handleGlobalKeydown = (e) => {
        if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
            e.preventDefault();
            searchInput.value?.focus();
        }
    };
    
    window.addEventListener('keydown', handleGlobalKeydown);
    
    setupEcho();
    
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
    <div class="discussion-layout">
        <div class="discussion-main-row">
            <!-- Main Discussion Column -->
            <div class="discussion-chat-col">
                <!-- Header - Admin-style -->
                <div class="discussion-header">
                    <div class="d-flex align-items-center gap-3 flex-shrink-0">
                        <div class="avatar-box rounded d-flex align-items-center justify-content-center fw-bold">
                            <i class="ti ti-brand-hipchat fs-4"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-semibold small-mobile-title">{{ project.name }}</h6>
                            <div class="online-status-text d-flex align-items-center gap-1">
                                <span class="online-indicator"></span> {{ onlineUsers.length }} online
                            </div>
                        </div>
                    </div>

                    <!-- Header Actions (Search) -->
                    <div class="discussion-search-wrap">
                        <div class="search-container position-relative">
                            <i class="ti ti-search position-absolute top-50 translate-middle-y text-muted" style="left: 12px; font-size: 0.8rem;"></i>
                            <input 
                                ref="searchInput"
                                v-model="searchQuery" 
                                type="text" 
                                class="form-control form-control-sm search-input ps-4" 
                                placeholder="Search messages"
                                @focus="isSearchFocused = true"
                                @blur="isSearchFocused = false"
                            >
                        </div>
                    </div>
                </div>
                
                <!-- Chat View -->
                <div class="discussion-messages-area">
                    <div class="discussion-scroll-inner custom-scrollbar" id="discussion-scroll">
                        <div v-if="loading" class="d-flex justify-content-center align-items-center h-100">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        
                        <!-- Empty Search results -->
                        <div v-else-if="searchQuery && filteredDiscussions.length === 0" class="d-flex flex-column justify-content-center align-items-center h-100 p-5 text-center animate-fade-in">
                            <div class="bg-light p-4 rounded-circle mb-3">
                                <i class="ti ti-search-off fs-1 text-muted opacity-50"></i>
                            </div>
                            <h6 class="fw-bold text-dark">No matches found</h6>
                            <p class="text-muted small">We couldn't find anything for "{{ searchQuery }}"</p>
                            <button @click="searchQuery = ''" class="btn btn-sm btn-primary px-4">Clear search</button>
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
                            @scroll-to="scrollToBottom"
                        />
                        
                        <!-- Typing Feedback (Floating) -->
                        <div class="typing-container position-absolute bottom-0 start-0 w-100 p-2 pe-none">
                            <TypingIndicator :users="usersTyping" />
                        </div>
                    </div>
                </div>

                <!-- Input Area -->
                <div class="discussion-input-area">
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

            <!-- Right Sidebar: Details -->
            <div class="discussion-sidebar-col d-none d-lg-flex">
                <div class="discussion-sidebar-inner custom-scrollbar">
                    <OnlineUsers :project="project" :online-users="onlineUsers" :members="members" @updated="fetchDiscussions" />
                    <div class="mt-3">
                        <FileSummary :project="project" :discussions="discussions" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* ============================
   DISCUSSION LAYOUT - Flexbox
   ============================ */
.discussion-layout {
    display: flex;
    flex-direction: column;
    height: 100%;
    min-height: 0;
}

.discussion-main-row {
    display: flex;
    flex: 1 1 0;
    min-height: 0;
    overflow: hidden;
}

/* --- Chat column --- */
.discussion-chat-col {
    display: flex;
    flex-direction: column;
    flex: 1 1 0;
    min-width: 0;
    min-height: 0;
    background: #fff;
    border-right: 1px solid #eef2f7;
}

/* Header - Admin style */
.discussion-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 16px;
    background: #f8f9fa;
    border-bottom: 1px solid #eef2f7;
    flex-shrink: 0;
}

.avatar-box {
    width: 40px;
    height: 40px;
    background: rgba(62, 96, 213, 0.1);
    color: var(--bs-primary, #3e60d5);
}

.discussion-search-wrap {
    flex-shrink: 1;
    min-width: 0;
    max-width: 220px;
    width: 100%;
}

.search-input {
    font-size: 0.8rem;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding-left: 32px !important;
    background: #fff;
    height: 34px;
}

.search-input:focus {
    border-color: var(--bs-primary, #3e60d5);
    box-shadow: 0 0 0 0.15rem rgba(62, 96, 213, 0.15);
}

/* Online status text */
.online-status-text {
    font-size: 0.7rem;
    font-weight: 500;
    color: #22c55e;
}

.online-indicator {
    width: 6px;
    height: 6px;
    background-color: #22c55e;
    border-radius: 50%;
    display: inline-block;
}

/* Messages area */
.discussion-messages-area {
    flex: 1 1 0;
    min-height: 0;
    position: relative;
    overflow: hidden;
    background-color: #fafbfe;
    background-image: radial-gradient(#e8ecf1 0.5px, transparent 0.5px);
    background-size: 20px 20px;
}

.discussion-scroll-inner {
    height: 100%;
    overflow-y: auto;
    padding: 16px;
}

/* Input area */
.discussion-input-area {
    flex-shrink: 0;
    padding: 10px 16px 12px;
    background: #fff;
    border-top: 1px solid #eef2f7;
}

/* --- Right sidebar column --- */
.discussion-sidebar-col {
    flex: 0 0 280px;
    max-width: 280px;
    min-height: 0;
    display: flex;
    flex-direction: column;
    background: #f8f9fa;
    border-left: 1px solid #eef2f7;
}

.discussion-sidebar-inner {
    flex: 1 1 0;
    overflow-y: auto;
    padding: 16px;
}

/* ============================
   UTILITY CLASSES
   ============================ */
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #dee2e6;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background-color: #adb5bd;
}

.x-small {
    font-size: 0.75rem;
}

.transition-all {
    transition: all 0.2s ease;
}

.animate-fade-in {
    animation: fadeIn 0.3s ease-out;
}

/* ============================
   RESPONSIVE
   ============================ */
@media (max-width: 991px) {
    .discussion-sidebar-col {
        display: none !important;
    }
    .discussion-chat-col {
        border-right: none;
    }
}

@media (max-width: 576px) {
    .small-mobile-title {
        font-size: 0.9rem;
    }
    .discussion-header {
        padding: 8px 12px;
    }
    .discussion-search-wrap {
        max-width: 160px;
    }
    .discussion-scroll-inner {
        padding: 10px;
    }
    .discussion-input-area {
        padding: 8px 10px;
    }
}

@media (min-width: 1400px) {
    .discussion-sidebar-col {
        flex: 0 0 300px;
        max-width: 300px;
    }
}

@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
</style>
