<template>
    <section class="max-w-4xl mx-auto px-6 py-12 space-y-12">
        <div class="space-y-6 text-center">
            <h1 class="text-4xl font-bold tracking-tight">About DSE Portfolio Alert System</h1>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Empowering investors on the Dar es Salaam Stock Exchange with real-time data and smart portfolio management tools.
            </p>
        </div>

        <div class="grid md:grid-cols-2 gap-12 items-center">
            <div class="space-y-6">
                <h2 class="text-2xl font-semibold">Our Mission</h2>
                <p class="text-gray-600 leading-relaxed">
                    We aim to simplify stock market investing in Tanzania by providing a comprehensive platform for tracking portfolio performance. 
                    Whether you are a seasoned investor or just starting, our system ensures you never miss a critical market movement.
                </p>
                <p class="text-gray-600 leading-relaxed">
                    Built by <strong>eportsolutions.co.tz</strong>, we are dedicated to bringing modern financial technology solutions to the local market.
                </p>
            </div>
            <div class="bg-gray-50 p-8 rounded-xl border border-gray-100">
                <h3 class="text-lg font-semibold mb-4">Key Features</h3>
                <ul class="space-y-3">
                    <li class="flex items-start gap-3">
                        <span class="text-green-600 text-xl">✓</span>
                        <span class="text-gray-700">Real-time DSE market data and price updates</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-green-600 text-xl">✓</span>
                        <span class="text-gray-700">Automated P&L tracking for your holdings</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-green-600 text-xl">✓</span>
                        <span class="text-gray-700">Smart email alerts for Take Profit & Buy More targets</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-green-600 text-xl">✓</span>
                        <span class="text-gray-700">Visual market overview and performance graphs</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="border-t pt-12 text-center">
            <h2 class="text-2xl font-semibold mb-6">Ready to take control of your portfolio?</h2>
            <div class="flex justify-center gap-4">
                <button @click="$emit('registerClick')" class="bg-black text-white px-6 py-3 rounded hover:bg-gray-800 transition">Get Started</button>
                <button @click="$emit('loginClick')" class="border border-gray-300 px-6 py-3 rounded hover:bg-gray-50 transition">Login</button>
            </div>
        </div>
    </section>
</template>

<script setup>
defineEmits(['registerClick', 'loginClick']);
</script>