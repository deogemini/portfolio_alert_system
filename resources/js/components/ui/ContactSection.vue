<template>
    <section class="grid md:grid-cols-2 gap-8 items-start px-6">
        <div class="space-y-4">
            <h2 class="text-2xl font-semibold">Contact Us</h2>
            <p class="text-sm">Reach eportsolutions.co.tz for support, onboarding, or inquiries.</p>
            <div class="grid grid-cols-1 gap-2">
                <input v-model="form.name" placeholder="Your Name" class="border px-2 py-2">
                <input v-model="form.email" type="email" placeholder="Your Email" class="border px-2 py-2">
                <textarea v-model="form.message" placeholder="Message" rows="4" class="border px-2 py-2"></textarea>
                <button @click="$emit('submit', { ...form })" class="bg-black text-white px-3 py-2">Send Message</button>
            </div>
            <div v-if="errors.length" class="text-sm text-red-700">
                <div v-for="(err,i) in errors" :key="i">{{ err }}</div>
            </div>
            <div v-if="message" class="text-sm">{{ message }}</div>
        </div>
        <div v-if="!user" class="space-y-4">
            <h2 class="text-2xl font-semibold">Get Started</h2>
            <p class="text-sm">Register an account to manage your DSE portfolio alerts.</p>
            <div class="flex gap-3">
                <button @click="$emit('registerClick')" class="bg-black text-white px-4 py-2">Register</button>
                <button @click="$emit('loginClick')" class="border px-4 py-2">Login</button>
            </div>
        </div>
    </section>
</template>

<script setup>
import { reactive } from 'vue';
defineProps({
    user: Object,
    errors: { type: Array, default: () => [] },
    message: { type: String, default: '' }
});
const form = reactive({ name: '', email: '', message: '' });
</script>
