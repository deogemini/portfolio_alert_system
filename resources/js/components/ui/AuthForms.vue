<template>
    <section class="max-w-md mx-auto px-6 py-10 border rounded shadow-sm bg-white relative">
        <button @click="$emit('close')" class="absolute top-4 right-4 text-gray-500 hover:text-black">
            ✕
        </button>

        <div v-if="mode === 'register'" class="space-y-4">
            <h2 class="text-2xl font-semibold text-center">Create account</h2>
            <div class="grid grid-cols-1 gap-3">
                <input v-model="registerForm.name" placeholder="Name" class="border px-3 py-2 rounded">
                <input v-model="registerForm.email" type="email" placeholder="Email" class="border px-3 py-2 rounded">
                <div class="relative">
                    <input :type="showRegisterPassword?'text':'password'" v-model="registerForm.password" placeholder="Password" class="border px-3 py-2 w-full pr-10 rounded">
                    <button type="button" @click="showRegisterPassword=!showRegisterPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-gray-500">👁</button>
                </div>
                <div class="relative">
                    <input :type="showRegisterPasswordConfirm?'text':'password'" v-model="registerForm.password_confirmation" placeholder="Confirm Password" class="border px-3 py-2 w-full pr-10 rounded">
                    <button type="button" @click="showRegisterPasswordConfirm=!showRegisterPasswordConfirm" class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-gray-500">👁</button>
                </div>
                <button @click="$emit('register', { ...registerForm })" class="bg-black text-white px-3 py-2 rounded font-medium">Register</button>
            </div>
            <div v-if="registerErrors.length" class="text-base text-red-600 bg-red-50 p-2 rounded">
                <div v-for="(err,i) in registerErrors" :key="i">{{ err }}</div>
            </div>
            <div v-if="registerMessage" class="text-base text-center">{{ registerMessage }}</div>
            <div class="text-center text-base">
                Already have an account? <button @click="$emit('switch', 'login')" class="underline font-medium">Login</button>
            </div>
        </div>

        <div v-if="mode === 'login'" class="space-y-4">
            <h2 class="text-2xl font-semibold text-center">Login</h2>
            <div class="grid grid-cols-1 gap-3">
                <input v-model="loginForm.email" type="email" placeholder="Email" class="border px-3 py-2 rounded">
                <div class="relative">
                    <input :type="showLoginPassword?'text':'password'" v-model="loginForm.password" placeholder="Password" class="border px-3 py-2 w-full pr-10 rounded">
                    <button type="button" @click="showLoginPassword=!showLoginPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-gray-500">👁</button>
                </div>
                <button @click="$emit('login', { ...loginForm })" class="bg-black text-white px-3 py-2 rounded font-medium">Login</button>
            </div>
            <div v-if="loginMessage" class="text-base text-center text-red-600 bg-red-50 p-2 rounded">{{ loginMessage }}</div>
            <div class="text-center text-base">
                Don't have an account? <button @click="$emit('switch', 'register')" class="underline font-medium">Register</button>
            </div>
        </div>
    </section>
</template>

<script setup>
import { reactive, ref } from 'vue';
defineProps({
    mode: { type: String, default: 'login' },
    registerErrors: { type: Array, default: () => [] },
    registerMessage: { type: String, default: '' },
    loginMessage: { type: String, default: '' }
});
const registerForm = reactive({ name: '', email: '', password: '', password_confirmation: '' });
const loginForm = reactive({ email: '', password: '' });
const showRegisterPassword = ref(false);
const showRegisterPasswordConfirm = ref(false);
const showLoginPassword = ref(false);
</script>
