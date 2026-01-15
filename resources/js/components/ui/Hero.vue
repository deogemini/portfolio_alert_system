<template>
    <section class="grid md:grid-cols-2 gap-8 items-center px-6 py-10">
        <div class="space-y-4">
            <h1 class="text-4xl font-semibold">Track DSE holdings and receive smart alerts</h1>
            <p class="text-base">Enter purchase lots for each DSE stock, set take-profit and buy-more thresholds, and get email alerts based on market price movements per lot.</p>
            <div class="flex gap-3">
                <button @click="$emit('register')" class="bg-black text-white px-4 py-2">Get Started</button>
                <button @click="$emit('login')" class="border px-4 py-2">Login</button>
            </div>
        </div>
        <div class="flex items-center justify-center">
            <svg width="360" height="200" viewBox="0 0 360 200" xmlns="http://www.w3.org/2000/svg">
                <rect x="10" y="20" width="340" height="160" rx="12" fill="#f3f4f6" />
                <line x1="40" y1="160" x2="320" y2="40" stroke="#10b981" stroke-width="4"/>
                <circle cx="40" cy="160" r="6" fill="#10b981"/>
                <circle cx="180" cy="100" r="6" fill="#10b981"/>
                <circle cx="320" cy="40" r="6" fill="#10b981"/>
                <rect x="40" y="70" width="22" height="90" fill="#ef4444"/>
                <rect x="80" y="90" width="22" height="70" fill="#f59e0b"/>
                <rect x="120" y="60" width="22" height="100" fill="#10b981"/>
                <rect x="160" y="80" width="22" height="80" fill="#3b82f6"/>
                <rect x="200" y="50" width="22" height="110" fill="#64748b"/>
            </svg>
        </div>
    </section>
</template>

<script setup>
</script>
