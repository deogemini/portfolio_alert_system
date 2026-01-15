<template>
    <section class="space-y-3 px-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-medium">Lots</h2>
            <div class="flex gap-2">
                <input v-model="priceForm.symbol" placeholder="Symbol" class="border px-2 py-2">
                <input v-model.number="priceForm.price" type="number" placeholder="Price" class="border px-2 py-2">
                <button
                    @click="$emit('update-price', { ...priceForm })"
                    :disabled="updatingPrice"
                    class="border px-3 py-2 disabled:opacity-60"
                >
                    <span v-if="updatingPrice">Updating...</span>
                    <span v-else>Update Price</span>
                </button>
                <button
                    @click="$emit('check-alerts')"
                    :disabled="checkingAlerts"
                    class="bg-black text-white px-3 py-2 disabled:opacity-60"
                >
                    <span v-if="checkingAlerts">Checking...</span>
                    <span v-else>Check Alerts</span>
                </button>
            </div>
        </div>
        <div class="overflow-x-auto border rounded">
            <table class="min-w-full text-base">
                <thead>
                    <tr class="border-b">
                        <th class="text-left p-2">Symbol</th>
                        <th class="text-right p-2">Qty</th>
                        <th class="text-right p-2">Buy</th>
                        <th class="text-right p-2">TP%</th>
                        <th class="text-right p-2">BM%</th>
                        <th class="text-right p-2">Market</th>
                        <th class="text-right p-2">TP Target</th>
                        <th class="text-right p-2">BM Target</th>
                        <th class="text-right p-2">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="lot in lots" :key="lot.id" class="border-t">
                        <td class="p-2">{{ lot.stock.symbol }}</td>
                        <td class="p-2 text-right">{{ lot.quantity }}</td>
                        <td class="p-2 text-right">{{ format(lot.buy_price) }}</td>
                        <td class="p-2 text-right">{{ lot.take_profit_pct }}%</td>
                        <td class="p-2 text-right">{{ lot.buy_more_pct ?? '-' }}%</td>
                        <td class="p-2 text-right">{{ lot.stock.last_price ? format(lot.stock.last_price) : '-' }}</td>
                        <td class="p-2 text-right">{{ format(sellTarget(lot)) }}</td>
                        <td class="p-2 text-right">{{ lot.buy_more_pct ? format(buyTarget(lot)) : '-' }}</td>
                        <td class="p-2 text-right">
                            <span v-if="lot.stock.last_price && lot.stock.last_price >= sellTarget(lot)" class="text-green-600">Above TP</span>
                            <span v-else-if="lot.stock.last_price && lot.buy_more_pct && lot.stock.last_price <= buyTarget(lot)" class="text-blue-600">Below BM</span>
                            <span v-else class="text-gray-600">Waiting</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>

<script setup>
import { reactive } from 'vue';
defineProps({
    lots: { type: Array, default: () => [] },
    updatingPrice: { type: Boolean, default: false },
    checkingAlerts: { type: Boolean, default: false }
});
const priceForm = reactive({ symbol: '', price: null });
function format(n) {
    return new Intl.NumberFormat().format(Number(n||0));
}
function sellTarget(lot) {
    return Number(lot.buy_price) * (1 + Number(lot.take_profit_pct)/100);
}
function buyTarget(lot) {
    return Number(lot.buy_price) * (1 - Number(lot.buy_more_pct)/100);
}
</script>
