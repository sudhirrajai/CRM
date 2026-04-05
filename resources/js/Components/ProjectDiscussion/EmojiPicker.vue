<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const emit = defineEmits(['select', 'close']);

const RECENT_EMOJIS_KEY = 'chat_recent_emojis';
const DEFAULT_RECENT = ['❤️', '😂', '👍', '🔥', '🙌', '✨', '😊', '🤔'];

const categories = [
    { name: 'Recent', icon: 'ti-history', emojis: [] },
    { name: 'Smileys', icon: 'ti-mood-smile', emojis: ['😀', '😃', '😄', '😁', '😅', '😂', '🤣', '😊', '😇', '🙂', '🙃', '😉', '😌', '😍', '🥰', '😘', '😗', '😙', '😚', '😋', '😛', '😝', '😜', '🤪', '🤨', '🧐', '🤓', '😎', '🤩', '🥳', '😏', '😒', '😞', '😔', '😟', '😕', '🙁', '☹️', '😮', '😯', '😲', '😳', '🥺', '😦', '😧', '😨', '😰', '😥', '😢', '😭', '😱', '😖', '😣', '😞', '😓', '😩', '😫', '😤', '😡', '😠', '🤬', '🤯', '😳', '🥵', '🥶', '😱', '😨', '😰', '😥', '😓', '🤗', '🤔', '🤭', '🤫', '🤥', '😶', '😐', '😑', '😬', '🙄', '😯', '😦', '😧', '😮', '😲', '🥱', '😴', '🤤', '😪', '😵', '🤐', '🥴', '🤢', '🤮', '🤧', '😷', '🤒', '🤕'] },
    { name: 'Gestures', icon: 'ti-hand-stop', emojis: ['👋', '🤚', '🖐️', '✋', '🖖', '👌', '🤏', '✌️', '🤞', '🤟', '🤘', '🤙', '👈', '👉', '👆', '🖕', '👇', '☝️', '👍', '👎', '✊', '👊', '🤛', '🤜', '👏', '🙌', '👐', '🤲', '🤝', '🙏', '✍️', '💅', '🤳', '💪', '🦾'] },
    { name: 'Hearts', icon: 'ti-heart', emojis: ['❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍', '🤎', '💔', '❣️', '💕', '💞', '💓', '💗', '💖', '💘', '💝', '💟'] },
    { name: 'Special', icon: 'ti-star', emojis: ['✨', '🌟', '⭐', '🔥', '💥', '💢', '💨', '💦', '💤', '💯', '✅', '❌', '⚠️', '🚀', '🎉', '🎊', '🎁', '🎈', '⚡', '💡', '🔔'] }
];

const activeTab = ref('Smileys');
const recentEmojis = ref([]);
const pickerRef = ref(null);

const handleClickOutside = (e) => {
    if (pickerRef.value && !pickerRef.value.contains(e.target)) {
        // Check if the click target is the emoji toggle button (parent handles that)
        const target = e.target.closest('[title="Emoji"]');
        if (!target) {
            emit('close');
        }
    }
};

onMounted(() => {
    const stored = localStorage.getItem(RECENT_EMOJIS_KEY);
    if (stored) {
        recentEmojis.value = JSON.parse(stored);
    } else {
        recentEmojis.value = DEFAULT_RECENT;
    }
    
    // Listen for clicks anywhere on the document
    setTimeout(() => {
        document.addEventListener('mousedown', handleClickOutside, true);
    }, 0);
});

onUnmounted(() => {
    document.removeEventListener('mousedown', handleClickOutside, true);
});

const currentEmojis = computed(() => {
    if (activeTab.value === 'Recent') return recentEmojis.value;
    const category = categories.find(c => c.name === activeTab.value);
    return category ? category.emojis : [];
});

const selectEmoji = (emoji) => {
    const newRecent = [emoji, ...recentEmojis.value.filter(e => e !== emoji)].slice(0, 24);
    recentEmojis.value = newRecent;
    localStorage.setItem(RECENT_EMOJIS_KEY, JSON.stringify(newRecent));
    
    emit('select', emoji);
};
</script>

<template>
    <div ref="pickerRef" class="emoji-picker shadow-lg border rounded bg-white overflow-hidden animate-pop-in">
        <div class="emoji-tabs d-flex border-bottom bg-light p-1 gap-1">
            <button 
                v-for="cat in categories" 
                :key="cat.name"
                @click="activeTab = cat.name"
                class="btn btn-sm flex-grow-1 py-2 border-0 rounded transition-all d-flex align-items-center justify-content-center tab-btn"
                :class="activeTab === cat.name ? 'active-tab shadow-sm' : 'text-muted opacity-60 hover-opacity-100'"
                :title="cat.name"
            >
                <i class="ti" :class="cat.icon" style="font-size: 1rem;"></i>
            </button>
        </div>
        
        <div class="emoji-content position-relative">
            <div class="emoji-grid p-2 p-sm-3 custom-scrollbar" style="max-height: 260px; overflow-y: auto;">
                <div class="d-flex align-items-center justify-content-between mb-2 px-1">
                    <span class="text-muted fw-semibold text-uppercase" style="font-size: 0.6rem; letter-spacing: 0.08rem;">{{ activeTab }}</span>
                </div>
                <div class="emoji-items-grid">
                    <button 
                        v-for="emoji in currentEmojis" 
                        :key="emoji"
                        @click="selectEmoji(emoji)"
                        class="btn btn-link p-0 text-decoration-none emoji-btn transition-all rounded d-flex align-items-center justify-content-center"
                    >
                        {{ emoji }}
                    </button>
                    <div v-if="activeTab === 'Recent' && recentEmojis.length === 0" class="col-span-full py-4 text-center text-muted small">
                        No recent emojis yet
                    </div>
                </div>
            </div>
        </div>
        
        <div class="p-2 border-top bg-light d-flex justify-content-between align-items-center px-3">
            <div class="d-flex align-items-center gap-2">
                <div class="emoji-preview-dot"></div>
                <span class="text-muted fw-semibold" style="font-size: 0.6rem;">Emoji Picker</span>
            </div>
            <button @click="$emit('close')" class="btn btn-sm btn-link p-0 text-primary text-decoration-none fw-semibold" style="font-size: 0.65rem;">Dismiss</button>
        </div>
    </div>
</template>

<style scoped>
.emoji-picker {
    position: absolute;
    bottom: 100%;
    left: 0;
    width: 310px;
    margin-bottom: 16px;
    user-select: none;
    z-index: 2000 !important;
    max-height: 400px;
    background: #ffffff;
    border-color: #dee2e6 !important;
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
    color: var(--bs-primary, #3e60d5) !important;
}

.active-tab::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 25%;
    right: 25%;
    height: 2px;
    background: var(--bs-primary, #3e60d5);
    border-radius: 2px;
}

.emoji-items-grid {
    display: grid;
    grid-template-columns: repeat(8, 1fr);
    grid-auto-rows: 34px;
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
    border-radius: 6px;
    font-size: 1.3rem !important;
    transition: all 0.1s ease-out;
}

.emoji-btn:hover {
    background-color: #f1f5f9;
    transform: scale(1.2);
    z-index: 5;
}

.emoji-preview-dot {
    width: 5px;
    height: 5px;
    background: var(--bs-primary, #3e60d5);
    border-radius: 50%;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #dee2e6;
    border-radius: 10px;
}

.transition-all {
    transition: all 0.2s ease;
}

/* Animations */
.animate-pop-in {
    animation: popIn 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes popIn {
    from { opacity: 0; transform: scale(0.9) translateY(16px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}

.hover-opacity-100:hover {
    opacity: 1 !important;
}

.opacity-60 {
    opacity: 0.6;
}
</style>
