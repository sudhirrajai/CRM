<script setup>
import { ref, computed, onMounted } from 'vue';

const emit = defineEmits(['select', 'close']);

const RECENT_EMOJIS_KEY = 'chat_recent_emojis';
const DEFAULT_RECENT = ['❤️', '😂', '👍', '🔥', '🙌', '✨', '😊', '🤔'];

const categories = [
    { name: 'Recent', icon: 'ti-history', emojis: [] }, // Will be populated from localStorage
    { name: 'Smileys', icon: 'ti-mood-smile', emojis: ['😀', '😃', '😄', '😁', '😅', '😂', '🤣', '😊', '😇', '🙂', '🙃', '😉', '😌', '😍', '🥰', '😘', '😗', '😙', '😚', '😋', '😛', '😝', '😜', '🤪', '🤨', '🧐', '🤓', '😎', '🤩', '🥳', '😏', '😒', '😞', '😔', '😟', '😕', '🙁', '☹️', '😮', '😯', '😲', '😳', '🥺', '😦', '😧', '😨', '😰', '😥', '😢', '😭', '😱', '😖', '😣', '😞', '😓', '😩', '😫', '😤', '😡', '😠', '🤬', '🤯', '😳', '🥵', '🥶', '😱', '😨', '😰', '😥', '😓', '🤗', '🤔', '🤭', '🤫', '🤥', '😶', '😐', '😑', '😬', '🙄', '😯', '😦', '😧', '😮', '😲', '🥱', '😴', '🤤', '😪', '😵', '🤐', '🥴', '🤢', '🤮', '🤧', '😷', '🤒', '🤕'] },
    { name: 'Gestures', icon: 'ti-hand-stop', emojis: ['👋', '🤚', '🖐️', '✋', '🖖', '👌', '🤏', '✌️', '🤞', '🤟', '🤘', '🤙', '👈', '👉', '👆', '🖕', '👇', '☝️', '👍', '👎', '✊', '👊', '🤛', '🤜', '👏', '🙌', '👐', '🤲', '🤝', '🙏', '✍️', '💅', '🤳', '💪', '🦾'] },
    { name: 'Hearts', icon: 'ti-heart', emojis: ['❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍', '🤎', '💔', '❣️', '💕', '💞', '💓', '💗', '💖', '💘', '💝', '💟'] },
    { name: 'Special', icon: 'ti-star', emojis: ['✨', '🌟', '⭐', '🔥', '💥', '💢', '💨', '💦', '💤', '💯', '✅', '❌', '⚠️', '🚀', '🎉', '🎊', '🎁', '🎈', '⚡', '💡', '🔔'] }
];

const activeTab = ref('Smileys');
const recentEmojis = ref([]);

onMounted(() => {
    const stored = localStorage.getItem(RECENT_EMOJIS_KEY);
    if (stored) {
        recentEmojis.value = JSON.parse(stored);
    } else {
        recentEmojis.value = DEFAULT_RECENT;
    }
});

const currentEmojis = computed(() => {
    if (activeTab.value === 'Recent') return recentEmojis.value;
    const category = categories.find(c => c.name === activeTab.value);
    return category ? category.emojis : [];
});

const selectEmoji = (emoji) => {
    // Update Recent
    const newRecent = [emoji, ...recentEmojis.value.filter(e => e !== emoji)].slice(0, 24);
    recentEmojis.value = newRecent;
    localStorage.setItem(RECENT_EMOJIS_KEY, JSON.stringify(newRecent));
    
    emit('select', emoji);
};
</script>

<template>
    <div class="emoji-picker shadow-indigo-lg border rounded-4 bg-white overflow-hidden animate-pop-in">
        <div class="emoji-tabs d-flex border-bottom bg-light-subtle p-1 gap-1">
            <button 
                v-for="cat in categories" 
                :key="cat.name"
                @click="activeTab = cat.name"
                class="btn btn-sm flex-grow-1 py-2 border-0 rounded-3 transition-all d-flex align-items-center justify-content-center tab-btn"
                :class="activeTab === cat.name ? 'active-tab shadow-sm' : 'text-muted opacity-60 hover-opacity-100'"
                :title="cat.name"
            >
                <i class="ti fs-5" :class="cat.icon"></i>
            </button>
        </div>
        
        <div class="emoji-content position-relative">
            <div class="emoji-grid p-2 p-sm-3 custom-scrollbar" style="max-height: 280px; overflow-y: auto;">
                <div class="d-flex align-items-center justify-content-between mb-2 px-1">
                    <span class="text-muted x-small fw-bold text-uppercase letter-spacing-1">{{ activeTab }}</span>
                </div>
                <div class="emoji-items-grid">
                    <button 
                        v-for="emoji in currentEmojis" 
                        :key="emoji"
                        @click="selectEmoji(emoji)"
                        class="btn btn-link p-0 fs-4 text-decoration-none emoji-btn transition-all rounded-3 d-flex align-items-center justify-content-center"
                    >
                        {{ emoji }}
                    </button>
                    <div v-if="activeTab === 'Recent' && recentEmojis.length === 0" class="col-span-full py-4 text-center text-muted small">
                        No recent emojis yet
                    </div>
                </div>
            </div>
        </div>
        
        <div class="p-2 border-top bg-light-subtle d-flex justify-content-between align-items-center px-3">
            <div class="d-flex align-items-center gap-2">
                <div class="emoji-preview-dot"></div>
                <span class="x-small text-muted fw-semibold">Emoji Picker</span>
            </div>
            <button @click="$emit('close')" class="btn btn-sm btn-link p-0 text-indigo hover-text-dark x-small text-decoration-none fw-bold">Dismiss</button>
        </div>
    </div>
</template>

<style scoped>
.emoji-picker {
    position: absolute;
    bottom: 100%;
    left: 0;
    width: 320px;
    margin-bottom: 20px;
    user-select: none;
    z-index: 2000 !important;
    max-height: 420px;
    background: #ffffff;
    border: 1px solid rgba(0,0,0,0.08) !important;
}

.shadow-indigo-lg {
    box-shadow: 0 15px 35px -5px rgba(76, 73, 226, 0.15), 0 10px 15px -10px rgba(76, 73, 226, 0.1) !important;
}

@media (max-width: 576px) {
    .emoji-picker {
        width: 280px;
        right: -40px;
        left: auto;
    }
}

.tab-btn {
    position: relative;
    z-index: 1;
}

.tab-btn:hover {
    background: rgba(0,0,0,0.03);
}

.active-tab {
    background: #ffffff !important;
    color: #4c49e2 !important;
}

.active-tab::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 25%;
    right: 25%;
    height: 2px;
    background: #4c49e2;
    border-radius: 2px;
}

.emoji-items-grid {
    display: grid;
    grid-template-columns: repeat(8, 1fr);
    grid-auto-rows: 36px;
    gap: 2px;
}

@media (max-width: 576px) {
    .emoji-items-grid {
        grid-template-columns: repeat(7, 1fr);
    }
}

.emoji-btn {
    width: 100%;
    height: 100%;
    border-radius: 8px;
    font-size: 1.4rem !important;
    transition: all 0.1s ease-out;
}

.emoji-btn:hover {
    background-color: #f1f5f9;
    transform: scale(1.2);
    z-index: 5;
}

.emoji-preview-dot {
    width: 6px;
    height: 6px;
    background: #4c49e2;
    border-radius: 50%;
}

.x-small {
    font-size: 0.65rem;
}

.letter-spacing-1 {
    letter-spacing: 0.08rem;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 5px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #e2e8f0;
    border-radius: 10px;
}

.bg-light-subtle {
    background-color: #f8fafc !important;
}

.text-indigo { color: #4c49e2 !important; }

/* Animations */
.animate-pop-in {
    animation: popIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes popIn {
    from { opacity: 0; transform: scale(0.9) translateY(20px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}

.hover-opacity-100:hover {
    opacity: 1 !important;
}

.opacity-60 {
    opacity: 0.6;
}
</style>

