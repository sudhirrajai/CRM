<script setup>
import { computed } from 'vue';
import Modal from './Modal.vue';
import DangerButton from './DangerButton.vue';
import SecondaryButton from './SecondaryButton.vue';

const props = defineProps({
    show: Boolean,
    title: String,
    message: String,
    confirmText: {
        type: String,
        default: 'Confirm'
    },
    cancelText: {
        type: String,
        default: 'Cancel'
    },
    processing: Boolean,
});

const emit = defineEmits(['close', 'confirm']);

const close = () => {
    emit('close');
};

const confirm = () => {
    emit('confirm');
};
</script>

<template>
    <Modal :show="show" @close="close" max-width="md">
        <div class="p-6 bg-white dark:bg-gray-900 rounded-2xl overflow-hidden shadow-2xl border border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-4 mb-4">
                <div class="p-3 rounded-full bg-red-50 dark:bg-red-900/20 text-red-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tabler-icon tabler-icon-alert-triangle"><path d="M12 9v4"></path><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z"></path><path d="M12 16h.01"></path></svg>
                </div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ title || 'Are you sure?' }}
                </h2>
            </div>

            <p class="text-gray-600 dark:text-gray-400 mb-8 leading-relaxed">
                {{ message || 'This action cannot be undone. Please confirm to proceed.' }}
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-end gap-3">
                <SecondaryButton 
                    @click="close" 
                    class="w-full sm:w-auto border-none !bg-gray-100 dark:!bg-gray-800 !text-gray-700 dark:!text-gray-300 hover:!bg-gray-200 dark:hover:!bg-gray-700 transition-all rounded-xl py-2.5 px-6"
                >
                    {{ cancelText }}
                </SecondaryButton>
                <DangerButton
                    @click="confirm"
                    :disabled="processing"
                    :class="{ 'opacity-25': processing }"
                    class="w-full sm:w-auto !bg-red-600 hover:!bg-red-700 !shadow-lg !shadow-red-500/20 transition-all rounded-xl py-2.5 px-6 border-none text-white font-semibold"
                >
                    <span v-if="processing" class="mr-2">
                        <svg class="animate-spin h-4 w-4 text-white inline shadow-none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                    {{ confirmText }}
                </DangerButton>
            </div>
        </div>
    </Modal>
</template>

<style scoped>
.rounded-2xl {
    border-radius: 1.25rem;
}
</style>
