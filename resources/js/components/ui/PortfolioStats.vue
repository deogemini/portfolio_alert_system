<template>
    <section class="grid grid-cols-2 md:grid-cols-4 gap-4 px-6">
        <div class="border p-4 rounded">
            <div class="text-xs">Total Quantity</div>
            <div class="text-xl font-semibold">{{ stats.totalQty }}</div>
        </div>
        <div class="border p-4 rounded">
            <div class="text-xs">Invested Value</div>
            <div class="text-xl font-semibold">{{ format(stats.investedValue) }}</div>
        </div>
        <div class="border p-4 rounded">
            <div class="text-xs">Current Value</div>
            <div class="text-xl font-semibold">{{ format(stats.currentValue) }}</div>
        </div>
        <div class="border p-4 rounded">
            <div class="text-xs">P&L</div>
            <div class="text-xl font-semibold" :class="stats.pnlValue>=0?'text-green-600':'text-red-600'">{{ format(stats.pnlValue) }} ({{ stats.pnlPct.toFixed(2) }}%)</div>
        </div>
    </section>
</template>

<script setup>
defineProps({
    stats: { type: Object, required: true }
});
function format(n) {
    return new Intl.NumberFormat().format(Number(n||0));
}
</script>
