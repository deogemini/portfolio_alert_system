<template>
    <section class="space-y-3 px-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-medium">Market Overview</h2>
            <div class="flex gap-2">
                <button @click="$emit('refresh')" class="border px-3 py-2">Refresh Market Data</button>
            </div>
        </div>
        <div class="overflow-x-auto border rounded">
            <table class="min-w-full text-base">
                <thead>
                    <tr class="border-b">
                        <th class="text-left p-2">Symbol</th>
                        <th class="text-right p-2">Price</th>
                        <th class="text-right p-2">Change</th>
                        <th class="text-right p-2">Change %</th>
                        <th class="text-left p-2">Graph</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="eq in equities" :key="eq.symbol" class="border-t">
                        <td class="p-2">{{ eq.symbol }}</td>
                        <td class="p-2 text-right">{{ eq.price !== null ? format(eq.price) : '-' }}</td>
                        <td class="p-2 text-right" :class="(eq.change ?? 0) >= 0 ? 'text-green-600' : 'text-red-600'">
                            {{ eq.change !== null ? format(eq.change) : '-' }}
                        </td>
                        <td class="p-2 text-right" :class="(eq.change_pct ?? 0) >= 0 ? 'text-green-600' : 'text-red-600'">
                            {{ eq.change_pct !== null ? eq.change_pct.toFixed(2)+'%' : '-' }}
                        </td>
                        <td class="p-2">
                            <div class="w-64 h-3 bg-gray-200 relative rounded">
                                <div v-if="eq.change_pct !== null"
                                     :style="{ width: Math.min(100, Math.abs(eq.change_pct)) + '%'}"
                                     :class="(eq.change_pct >= 0) ? 'bg-green-500' : 'bg-red-500'"
                                     class="h-3 rounded">
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>

<script setup>
const props = defineProps({
    equities: { type: Array, default: () => [] }
});
function format(n) {
    return new Intl.NumberFormat().format(Number(n||0));
}
</script>
