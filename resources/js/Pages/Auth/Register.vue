<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <h4 class="fw-semibold mb-2">Create an account</h4>
        <p class="text-muted mb-4">Register in the CRM to manage your projects and clients.</p>

        <form @submit.prevent="submit" class="text-start">
            <div class="mb-3">
                <InputLabel for="name" value="Name" class="form-label" />
                <TextInput
                    id="name"
                    type="text"
                    class="form-control"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Enter your name"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mb-3">
                <InputLabel for="email" value="Email" class="form-label" />
                <TextInput
                    id="email"
                    type="email"
                    class="form-control"
                    v-model="form.email"
                    required
                    autocomplete="username"
                    placeholder="Enter your email"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mb-3">
                <InputLabel for="password" value="Password" class="form-label" />
                <TextInput
                    id="password"
                    type="password"
                    class="form-control"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                    placeholder="Create a password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mb-3">
                <InputLabel
                    for="password_confirmation"
                    value="Confirm Password"
                    class="form-label"
                />
                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="form-control"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="Confirm your password"
                />
                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="d-grid mt-4">
                <PrimaryButton
                    class="btn btn-primary w-100"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Register
                </PrimaryButton>
            </div>

            <p class="text-muted fs-14 mt-4">Already registered? <Link :href="route('login')" class="fw-semibold text-dark ms-1">Login</Link></p>
        </form>
    </GuestLayout>
</template>
