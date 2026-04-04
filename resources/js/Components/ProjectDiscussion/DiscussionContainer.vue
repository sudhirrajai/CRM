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

const searchInput = ref(null);
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
    <div class="discussion-layout">
        <div class="discussion-main-row">
            <!-- Main Discussion Column -->
            <div class="discussion-chat-col">
                <!-- Header -->
                <div class="discussion-header">
                    <div class="d-flex align-items-center gap-3 flex-shrink-0">
                        <div class="avatar-box bg-indigo-subtle text-indigo rounded-3 d-flex align-items-center justify-content-center fw-bold" style="width: 42px; height: 42px;">
                            <i class="ti ti-brand-hipchat fs-3"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold text-dark small-mobile-title">{{ project.name }}</h6>
                            <div class="x-small text-success d-flex align-items-center gap-1">
                                <span class="online-indicator"></span> {{ onlineUsers.length }} online
                            </div>
                        </div>
                    </div>

                    <!-- Header Actions (Search) -->
                    <div class="discussion-search-wrap">
                        <div class="search-container position-relative">
                            <i class="ti ti-search position-absolute top-50 translate-middle-y text-muted" style="left: 14px; font-size: 0.85rem;"></i>
                            <input 
                                ref="searchInput"
                                v-model="searchQuery" 
                                type="text" 
                                class="form-control search-input rounded-pill bg-light border-0 py-2 ps-5 pe-3 small" 
                                placeholder="Search messages"
                                @focus="isSearchFocused = true"
                                @blur="isSearchFocused = false"
                            >
                        </div>
                    </div>
                </div>
                
                <!-- Chat View -->
                <div class="discussion-messages-area bg-dot-pattern">
                    <div class="discussion-scroll-inner custom-scrollbar" id="discussion-scroll">
                        <div v-if="loading" class="d-flex justify-content-center align-items-center h-100">
                            <div class="spinner-grow text-indigo" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        
                        <!-- Empty Search results -->
                        <div v-else-if="searchQuery && filteredDiscussions.length === 0" class="d-flex flex-column justify-content-center align-items-center h-100 p-5 text-center animate-fade-in">
                            <div class="bg-light p-4 rounded-circle mb-3 shadow-boron-sm">
                                <i class="ti ti-search-off fs-1 text-indigo opacity-50"></i>
                            </div>
                            <h5 class="fw-bold text-dark">No matches found</h5>
                            <p class="text-muted small">We couldn't find anything for "{{ searchQuery }}"</p>
                            <button @click="searchQuery = ''" class="btn btn-sm btn-indigo rounded-pill px-4">Clear search</button>
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
    border-right: 1px solid #e9ecef;
}

/* Header */
.discussion-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 16px;
    background: #fff;
    border-bottom: 1px solid #e9ecef;
    flex-shrink: 0;
    z-index: 10;
}

.discussion-search-wrap {
    flex-shrink: 1;
    min-width: 0;
    max-width: 240px;
    width: 100%;
}

/* Messages area */
.discussion-messages-area {
    flex: 1 1 0;
    min-height: 0;
    position: relative;
    overflow: hidden;
}

.discussion-scroll-inner {
    height: 100%;
    overflow-y: auto;
    padding: 16px;
}

/* Input area */
.discussion-input-area {
    flex-shrink: 0;
    padding: 12px 16px;
    background: #fff;
    border-top: 1px solid #e9ecef;
}

/* --- Right sidebar column --- */
.discussion-sidebar-col {
    flex: 0 0 280px;
    max-width: 280px;
    min-height: 0;
    display: flex;
    flex-direction: column;
    background: #f8fafc;
    border-left: 1px solid #e9ecef;
}

.discussion-sidebar-inner {
    flex: 1 1 0;
    overflow-y: auto;
    padding: 16px;
}

/* ============================
   UTILITY CLASSES
   ============================ */
.bg-boron-light { background-color: #f4f7fa !important; }
.shadow-boron { box-shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, 0.03) !important; }
.shadow-boron-sm { box-shadow: 0 0.25rem 0.5rem rgba(18, 38, 63, 0.05) !important; }

.text-indigo { color: #4c49e2 !important; }
.bg-indigo-subtle { background-color: rgba(76, 73, 226, 0.08) !important; }
.btn-indigo { background-color: #4c49e2 !important; color: white !important; }
.btn-indigo:hover { background-color: #413dd1 !important; transform: translateY(-1px); }

.online-indicator {
    width: 7px;
    height: 7px;
    background-color: #22c55e;
    border-radius: 50%;
    display: inline-block;
    box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.1);
}

.bg-dot-pattern {
    background-color: #ffffff;
    background-image: radial-gradient(#e2e8f0 0.5px, transparent 0.5px);
    background-size: 20px 20px;
}

.search-input {
    transition: all 0.2s ease;
    border: 1px solid transparent !important;
    font-size: 0.85rem;
}

.search-input:focus {
    background-color: #fff !important;
    border-color: #4c49e2 !important;
    box-shadow: 0 0 0 0.2rem rgba(76, 73, 226, 0.1) !important;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 5px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #e2e8f0;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background-color: #cbd5e1;
}

.x-small {
    font-size: 0.75rem;
}

.transition-all {
    transition: all 0.2s ease;
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
        flex: 0 0 320px;
        max-width: 320px;
    }
}

@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
</style>
