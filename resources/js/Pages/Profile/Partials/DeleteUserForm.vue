<script setup>
import { useForm } from '@inertiajs/vue3';import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section>
        <header class="mb-4">
            <h4 class="header-title text-danger mb-1">Delete Account</h4>
            <p class="text-muted small mb-0">
                Once your account is deleted, all of its resources and data will be permanently deleted.
            </p>
        </header>

        <button class="btn btn-danger" @click="confirmUserDeletion">Delete Account</button>

        <!-- Delete Account Modal -->
        <div v-if="confirmingUserDeletion" class="modal fade show d-block" style="background: rgba(0,0,0,0.5); z-index: 1050;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete Account</h5>
                        <button type="button" class="btn-close" @click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">
                            Are you sure you want to delete your account? This action cannot be undone.
                            Please enter your password to confirm.
                        </p>

                        <div class="mb-0">
                            <label for="password" class="form-label">Password</label>
                            <input
                                id="password"
                                ref="passwordInput"
                                v-model="form.password"
                                type="password"
                                class="form-control"
                                :class="{ 'is-invalid': form.errors.password }"
                                placeholder="Enter password to confirm"
                                @keyup.enter="deleteUser"
                            />
                            <div v-if="form.errors.password" class="invalid-feedback">{{ form.errors.password }}</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" @click="closeModal">Cancel</button>
                        <button 
                            type="button" 
                            class="btn btn-danger" 
                            :disabled="form.processing"
                            @click="deleteUser"
                        >
                            Confirm Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
