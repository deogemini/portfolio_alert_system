<template>
    <header class="px-6 py-4 border-b flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div @click="$emit('home')" class="font-semibold text-lg cursor-pointer hover:opacity-80">DSE Portfolio Alert System</div>
            <nav class="hidden md:flex items-center gap-4 ml-6 text-sm font-medium text-gray-600">
                <button @click="$emit('home')" class="hover:text-black transition">Home</button>
                <button @click="$emit('about')" class="hover:text-black transition">About</button>
                <button @click="$emit('contact')" class="hover:text-black transition">Contact</button>
            </nav>
        </div>
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2 text-sm">
                <span :class="marketOpen ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" class="px-2 py-1 rounded">
                    {{ marketOpen ? 'Market Open' : 'Market Closed' }}
                </span>
                <span>{{ tzTime }}</span>
            </div>
            <template v-if="user">
                <span class="text-sm">{{ user.name }} ({{ user.email }})</span>
                <button @click="$emit('logout')" class="border px-3 py-1">Logout</button>
            </template>
            <template v-else>
                <button @click="$emit('login')" class="border px-3 py-1">Login</button>
                <button @click="$emit('register')" class="bg-black text-white px-3 py-1">Register</button>
            </template>
        </div>
    </header>
</template>

<script setup>
defineProps({
    user: Object,
    tzTime: String,
    marketOpen: Boolean
});
</script>
