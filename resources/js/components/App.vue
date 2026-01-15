<template>
    <div class="min-h-screen">
        <header class="px-6 py-4 border-b flex items-center justify-between">
            <div class="font-semibold text-lg">DSE Portfolio Alert System</div>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 text-sm">
                    <span :class="marketOpen ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" class="px-2 py-1 rounded">
                        {{ marketOpen ? 'Market Open' : 'Market Closed' }}
                    </span>
                    <span>{{ tzTime }}</span>
                </div>
                <template v-if="user">
                    <span class="text-sm">{{ user.name }} ({{ user.email }})</span>
                    <button @click="logout" class="border px-3 py-1">Logout</button>
                </template>
                <template v-else>
                    <button @click="showAuth='login'" class="border px-3 py-1">Login</button>
                    <button @click="showAuth='register'" class="bg-black text-white px-3 py-1">Register</button>
                </template>
            </div>
        </header>

        <main class="p-6 space-y-10">
            <section class="space-y-8">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-medium">Market Overview</h2>
                        <div class="flex gap-2">
                            <button @click="refreshMarket" class="border px-3 py-2">Refresh Market Data</button>
                        </div>
                    </div>
                    <div class="overflow-x-auto border rounded">
                        <table class="min-w-full text-sm">
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
                </div>
            </section>
            <section v-if="!user" class="grid md:grid-cols-2 gap-8 items-center">
                <div class="space-y-4">
                    <h1 class="text-3xl font-semibold">Track DSE holdings and receive smart alerts</h1>
                    <p class="text-sm">Enter purchase lots for each DSE stock, set take-profit and buy-more thresholds, and get email alerts based on market price movements per lot.</p>
                    <div class="flex gap-3">
                        <button @click="showAuth='register'" class="bg-black text-white px-4 py-2">Get Started</button>
                        <button @click="showAuth='login'" class="border px-4 py-2">Login</button>
                    </div>
                </div>
                <div class="space-y-6">
                    <div v-if="showAuth==='register'" class="space-y-3">
                        <h2 class="text-xl font-medium">Create account</h2>
                        <div class="grid grid-cols-1 gap-2">
                            <input v-model="registerForm.name" placeholder="Name" class="border px-2 py-2">
                            <input v-model="registerForm.email" type="email" placeholder="Email" class="border px-2 py-2">
                            <div class="relative">
                                <input :type="showRegisterPassword?'text':'password'" v-model="registerForm.password" placeholder="Password" class="border px-2 py-2 w-full pr-10">
                                <button type="button" @click="showRegisterPassword=!showRegisterPassword" class="absolute right-2 top-1/2 -translate-y-1/2 text-sm">👁</button>
                            </div>
                            <div class="relative">
                                <input :type="showRegisterPasswordConfirm?'text':'password'" v-model="registerForm.password_confirmation" placeholder="Confirm Password" class="border px-2 py-2 w-full pr-10">
                                <button type="button" @click="showRegisterPasswordConfirm=!showRegisterPasswordConfirm" class="absolute right-2 top-1/2 -translate-y-1/2 text-sm">👁</button>
                            </div>
                            <button @click="register" class="bg-black text-white px-3 py-2">Register</button>
                        </div>
                        <div v-if="registerErrors.length" class="text-sm text-red-700">
                            <div v-for="(err,i) in registerErrors" :key="i">{{ err }}</div>
                        </div>
                        <div v-if="registerMessage" class="text-sm">{{ registerMessage }}</div>
                    </div>
                    <div v-else class="space-y-3">
                        <h2 class="text-xl font-medium">Login</h2>
                        <div class="grid grid-cols-1 gap-2">
                            <input v-model="loginForm.email" type="email" placeholder="Email" class="border px-2 py-2">
                            <div class="relative">
                                <input :type="showLoginPassword?'text':'password'" v-model="loginForm.password" placeholder="Password" class="border px-2 py-2 w-full pr-10">
                                <button type="button" @click="showLoginPassword=!showLoginPassword" class="absolute right-2 top-1/2 -translate-y-1/2 text-sm">👁</button>
                            </div>
                            <button @click="login" class="bg-black text-white px-3 py-2">Login</button>
                        </div>
                        <div v-if="loginMessage" class="text-sm">{{ loginMessage }}</div>
                    </div>
                </div>
            </section>

            <section v-else class="space-y-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
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
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <h2 class="text-xl font-medium">Add Stock</h2>
                        <div class="flex gap-2">
                            <input v-model="stockForm.symbol" placeholder="Symbol e.g. CRDB" class="border px-2 py-2">
                            <input v-model="stockForm.name" placeholder="Name (optional)" class="border px-2 py-2">
                            <button @click="addStock" class="bg-black text-white px-3 py-2">Save</button>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <h2 class="text-xl font-medium">Add Lot</h2>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                            <input v-model="lotForm.symbol" placeholder="Symbol" class="border px-2 py-2">
                            <input v-model.number="lotForm.quantity" type="number" placeholder="Quantity" class="border px-2 py-2">
                            <input v-model.number="lotForm.buy_price" type="number" placeholder="Buy Price" class="border px-2 py-2">
                            <input v-model.number="lotForm.take_profit_pct" type="number" placeholder="Take Profit %" class="border px-2 py-2">
                            <input v-model.number="lotForm.buy_more_pct" type="number" placeholder="Buy More %" class="border px-2 py-2">
                            <button @click="addLot" class="bg-black text-white px-3 py-2">Save</button>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-medium">Lots</h2>
                        <div class="flex gap-2">
                            <input v-model="priceForm.symbol" placeholder="Symbol" class="border px-2 py-2">
                            <input v-model.number="priceForm.price" type="number" placeholder="Price" class="border px-2 py-2">
                            <button @click="updatePrice" class="border px-3 py-2">Update Price</button>
                            <button @click="checkAlerts" class="bg-black text-white px-3 py-2">Check Alerts</button>
                        </div>
                    </div>
                    <div class="overflow-x-auto border rounded">
                        <table class="min-w-full text-sm">
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
                </div>
            </section>

            <section v-if="messages.length" class="space-y-1">
                <div v-for="(m,i) in messages" :key="i" class="text-sm">{{ m }}</div>
            </section>
        </main>
        <footer class="px-6 py-4 border-t text-center text-sm">
            <span>© {{ currentYear }} eportsolutions.co.tz — All rights under company</span>
        </footer>
    </div>
    </template>

    <script setup>
    import axios from 'axios';
    import { onMounted, reactive, ref, computed } from 'vue';

    const showAuth = ref('login');
    const registerForm = reactive({ name: '', email: '', password: '', password_confirmation: '' });
    const showRegisterPassword = ref(false);
    const showRegisterPasswordConfirm = ref(false);
    const loginForm = reactive({ email: '', password: '' });
    const showLoginPassword = ref(false);
    const stockForm = reactive({ symbol: '', name: '' });
    const lotForm = reactive({ symbol: '', quantity: null, buy_price: null, take_profit_pct: 40, buy_more_pct: null });
    const priceForm = reactive({ symbol: '', price: null });
    const lots = ref([]);
    const equities = ref([]);
    const messages = ref([]);
    const user = ref(null);
    const registerErrors = ref([]);
    const registerMessage = ref('');
    const loginMessage = ref('');
    const currentYear = new Date().getFullYear();
    const tzTime = ref('');
    const marketOpen = ref(false);

    async function updateMarketStatus() {
        try {
            const res = await axios.get('/api/market/status');
            tzTime.value = `${res.data.time_eat} EAT`;
            marketOpen.value = !!res.data.open;
        } catch {
            // fallback to local clock if endpoint fails
            const d = new Date();
            const hh = String(d.getHours()).padStart(2, '0');
            const mm = String(d.getMinutes()).padStart(2, '0');
            const ss = String(d.getSeconds()).padStart(2, '0');
            tzTime.value = `${hh}:${mm}:${ss} EAT`;
        }
    }

    const stats = computed(() => {
        const totalQty = lots.value.reduce((a,b)=>a + Number(b.quantity||0), 0);
        const investedValue = lots.value.reduce((a,b)=>a + Number(b.quantity||0) * Number(b.buy_price||0), 0);
        const currentValue = lots.value.reduce((a,b)=>a + Number(b.quantity||0) * Number(b.stock?.last_price||0), 0);
        const pnlValue = currentValue - investedValue;
        const pnlPct = investedValue ? (pnlValue / investedValue) * 100 : 0;
        return { totalQty, investedValue, currentValue, pnlValue, pnlPct };
    });

    function format(n) {
        return new Intl.NumberFormat().format(Number(n||0));
    }
    function sellTarget(lot) {
        return Number(lot.buy_price) * (1 + Number(lot.take_profit_pct)/100);
    }
    function buyTarget(lot) {
        return Number(lot.buy_price) * (1 - Number(lot.buy_more_pct)/100);
    }

    async function register() {
        try {
            const res = await axios.post('/api/register', registerForm);
            user.value = res.data.user;
            registerErrors.value = [];
            registerMessage.value = 'Registered and logged in';
            await fetchLots();
        } catch (e) {
            registerErrors.value = [];
            registerMessage.value = '';
            if (e?.response?.status === 419) {
                registerMessage.value = 'Session expired or CSRF mismatch. Please refresh the page.';
            } else if (e?.response?.data?.errors) {
                const errs = e.response.data.errors;
                Object.keys(errs).forEach(k => {
                    errs[k].forEach(msg => registerErrors.value.push(msg));
                });
                if (!registerErrors.value.length) registerMessage.value = 'Register failed';
            } else if (e?.response?.data?.message) {
                registerMessage.value = e.response.data.message;
            } else {
                registerMessage.value = 'Register failed';
            }
        }
    }

    async function login() {
        try {
            const res = await axios.post('/api/login', loginForm);
            user.value = res.data.user;
            loginMessage.value = 'Logged in';
            await fetchLots();
        } catch (e) {
            if (e?.response?.data?.message) {
                loginMessage.value = e.response.data.message;
            } else {
                loginMessage.value = 'Login failed';
            }
        }
    }

    async function logout() {
        await axios.post('/api/logout');
        user.value = null;
        lots.value = [];
        messages.value.push('Logged out');
    }

    async function addStock() {
        const res = await axios.post('/api/stocks', stockForm);
        messages.value.push('Stock saved '+res.data.symbol);
    }

    async function addLot() {
        await axios.post('/api/lots', lotForm);
        messages.value.push('Lot saved');
        await fetchLots();
    }

    async function updatePrice() {
        const res = await axios.post('/api/stocks/'+priceForm.symbol+'/price', { price: priceForm.price });
        messages.value.push('Price updated '+res.data.symbol+' '+res.data.last_price);
    }

    async function fetchLots() {
        const res = await axios.get('/api/lots');
        lots.value = res.data;
    }

    async function fetchEquities() {
        const res = await axios.get('/api/market/equities');
        equities.value = res.data.equities || [];
    }

    async function refreshMarket() {
        await axios.post('/api/market/snapshot');
        await fetchEquities();
    }
    async function checkAlerts() {
        const res = await axios.post('/api/alerts/check');
        messages.value.push('Alerts '+res.data.notified.length);
        await fetchLots();
    }

    async function fetchMe() {
        try {
            const res = await axios.get('/api/me');
            user.value = res.data.user;
            await fetchLots();
        } catch {
            user.value = null;
        }
    }

    onMounted(() => {
        fetchMe();
        updateMarketStatus();
        setInterval(updateMarketStatus, 30000);
        fetchEquities();
    });
    </script>
