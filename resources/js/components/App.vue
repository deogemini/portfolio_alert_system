<template>
    <div class="min-h-screen">
        <HeaderBar
            :user="user"
            :tzTime="tzTime"
            :marketOpen="marketOpen"
            @logout="logout"
            @login="showAuth='login'; currentPage='home'"
            @register="showAuth='register'; currentPage='home'"
            @home="currentPage='home'; showAuth=null"
            @about="currentPage='about'; showAuth=null"
            @contact="currentPage='contact'; showAuth=null"
        />
        <main class="p-6 space-y-10">
            <template v-if="currentPage === 'home'">
                <MarketOverview :equities="equities" @refresh="refreshMarket" />

                <Hero
                    v-if="!user && !showAuth"
                    @register="showAuth='register'"
                    @login="showAuth='login'"
                />

                <AuthForms
                    v-if="!user && showAuth"
                    :mode="showAuth"
                    :registerErrors="registerErrors"
                    :registerMessage="registerMessage"
                    :loginMessage="loginMessage"
                    @register="registerWith"
                    @login="loginWith"
                    @close="showAuth=null"
                    @switch="(m) => showAuth=m"
                />

                <section v-else-if="user" class="space-y-8">
                    <PortfolioStats :stats="stats" />
                    <div class="max-w-4xl px-2">
                        <div class="border rounded-md p-4 bg-white space-y-2">
                            <h2 class="text-xl font-semibold">How to use this page</h2>
                            <ol class="list-decimal list-inside space-y-1 text-base">
                                <li>First add each stock you follow under <span class="font-semibold">Add Stock</span> using its DSE symbol, for example <span class="font-mono">CRDB</span> or <span class="font-mono">NMB</span>.</li>
                                <li>For every purchase you make, create a lot under <span class="font-semibold">Add Lot</span> with the symbol, quantity you bought and your buy price.</li>
                                <li>Set <span class="font-semibold">Take Profit %</span> to the gain where you want to sell (for example 40 means sell when price is 40% above buy price).</li>
                                <li>Optionally set <span class="font-semibold">Buy More %</span> to the drop where you want to buy more (for example 20 means buy more if price falls 20% below buy price).</li>
                                <li>Use the <span class="font-semibold">Check Alerts</span> button to see which lots are above your take‑profit or below your buy‑more level based on the latest market price.</li>
                            </ol>
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
                    <LotsTable :lots="lots" @update-price="updatePrice" @check-alerts="checkAlerts" />
                </section>
                <section v-if="messages.length" class="space-y-1">
                    <div v-for="(m,i) in messages" :key="i" class="text-sm">{{ m }}</div>
                </section>
            </template>
            <AboutSection
                v-else-if="currentPage === 'about'"
                @registerClick="showAuth='register'; currentPage='home'"
                @loginClick="showAuth='login'; currentPage='home'"
            />
            <div v-else-if="currentPage === 'contact'" class="max-w-4xl mx-auto py-12">
                 <ContactSection
                    :user="user"
                    :errors="contactErrors"
                    :message="contactMessage"
                    @submit="sendContact"
                    @registerClick="showAuth='register'; currentPage='home'"
                    @loginClick="showAuth='login'; currentPage='home'"
                />
            </div>
        </main>
        <div class="bg-blue-900 text-white text-sm md:text-base px-6 py-2 flex flex-col md:flex-row items-center justify-between gap-2">
            <div class="flex flex-wrap items-center gap-3">
                <span class="font-medium">Phone:</span>
                <span>+255 765 597 134</span>
                <span class="hidden md:inline">|</span>
                <span>+255 676 994 832</span>
                <span class="hidden md:inline">|</span>
                <span class="font-medium">Email:</span>
                <a href="mailto:info@eportsolutions.co.tz" class="underline-offset-2 hover:underline">
                    info@eportsolutions.co.tz
                </a>
            </div>
        </div>
        <footer class="px-6 py-4 border-t text-center text-base">
            <span>© {{ currentYear }} eportsolutions.co.tz — All rights under company</span>
        </footer>
    </div>
</template>

    <script setup>
    import axios from 'axios';
    import { onMounted, reactive, ref, computed } from 'vue';
    import HeaderBar from './ui/HeaderBar.vue';
    import Hero from './ui/Hero.vue';
    import MarketOverview from './ui/MarketOverview.vue';
    import AuthForms from './ui/AuthForms.vue';
    import PortfolioStats from './ui/PortfolioStats.vue';
    import LotsTable from './ui/LotsTable.vue';
    import ContactSection from './ui/ContactSection.vue';
    import AboutSection from './ui/AboutSection.vue';

    const showAuth = ref(null);
    const currentPage = ref('home');
    const registerForm = reactive({ name: '', email: '', password: '', password_confirmation: '' });
    const loginForm = reactive({ email: '', password: '' });
    const stockForm = reactive({ symbol: '', name: '' });
    const lotForm = reactive({ symbol: '', quantity: null, buy_price: null, take_profit_pct: 40, buy_more_pct: null });
    const priceForm = reactive({ symbol: '', price: null });
    const lots = ref([]);
    const equities = ref([]);
    const contactForm = reactive({ name: '', email: '', message: '' });
    const contactErrors = ref([]);
    const contactMessage = ref('');
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

    async function registerWith(payload) {
        try {
            const res = await axios.post('/api/register', payload);
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

    async function loginWith(payload) {
        try {
            const res = await axios.post('/api/login', payload);
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
        currentPage.value = 'home';
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

    async function updatePrice(payload) {
        const symbol = payload?.symbol ?? priceForm.symbol;
        const price = payload?.price ?? priceForm.price;
        const res = await axios.post('/api/stocks/'+symbol+'/price', { price });
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

    async function sendContact(payload) {
        contactErrors.value = [];
        contactMessage.value = '';
        try {
            const data = payload ?? contactForm;
            await axios.post('/api/contact', data);
            contactMessage.value = 'Message sent. We will get back to you soon.';
            contactForm.name = '';
            contactForm.email = '';
            contactForm.message = '';
        } catch (e) {
            if (e?.response?.data?.errors) {
                const errs = e.response.data.errors;
                Object.keys(errs).forEach(k => {
                    errs[k].forEach(msg => contactErrors.value.push(msg));
                });
            } else if (e?.response?.data?.message) {
                contactMessage.value = e.response.data.message;
            } else {
                contactMessage.value = 'Failed to send message';
            }
        }
    }
    async function checkAlerts() {
        await axios.post('/api/market/snapshot');
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
