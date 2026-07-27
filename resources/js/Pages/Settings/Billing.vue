<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import UsageIndicator from '@/Components/Billing/UsageIndicator.vue';
import PricingTable from '@/Components/Billing/PricingTable.vue';
import SubscriptionManager from '@/Components/Billing/SubscriptionManager.vue';
import { ref, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    company: Object,
    plans: Array,
    currentPlan: Object,
    usage: Object,
    subscription: Object,
    intent: Object,
});

const invoices = ref([]);
const loadingInvoices = ref(false);

onMounted(async () => {
    loadingInvoices.value = true;
    try {
        const response = await axios.get(route('billing.invoices'));
        invoices.value = response.data.invoices ?? [];
    } catch (error) {
        console.error('Failed to load invoices:', error);
    } finally {
        loadingInvoices.value = false;
    }
});

const paymentMethodForm = useForm({
    payment_method: '',
});

function updatePaymentMethod() {
    paymentMethodForm.patch(route('billing.payment-method'), {
        preserveScroll: true,
    });
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}
</script>

<template>
    <AppLayout>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-900">Billing & Subscription</h1>
            <p class="mt-1 text-sm text-slate-500">
                Manage your subscription plan, usage, and payment details.
            </p>
        </div>

        <div class="space-y-8">
            <!-- Current Plan & Usage -->
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold text-slate-900">Current Plan</h2>
                <div class="mb-6 flex items-center gap-3">
                    <span class="inline-flex items-center rounded-full bg-indigo-100 px-3 py-1 text-sm font-medium text-indigo-700">
                        {{ currentPlan?.name ?? 'Free' }}
                    </span>
                    <span v-if="subscription && !subscription.ends_at" class="text-sm text-emerald-600 font-medium">Active</span>
                    <span v-else-if="subscription && subscription.ends_at" class="text-sm text-amber-600 font-medium">Cancels at period end</span>
                    <span v-else class="text-sm text-slate-500">No active subscription</span>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <UsageIndicator
                        label="Active Jobs"
                        :current="usage.jobs.current"
                        :max="usage.jobs.max"
                    />
                    <UsageIndicator
                        label="Candidates"
                        :current="usage.candidates.current"
                        :max="usage.candidates.max"
                    />
                    <UsageIndicator
                        label="Team Members"
                        :current="usage.users.current"
                        :max="usage.users.max"
                    />
                    <UsageIndicator
                        label="AI Parses (this month)"
                        :current="usage.ai_parses.current"
                        :max="usage.ai_parses.max"
                    />
                </div>
            </div>

            <!-- Subscription Manager -->
            <SubscriptionManager
                :subscription="subscription"
                :current-plan="currentPlan"
            />

            <!-- Pricing Table -->
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold text-slate-900">Available Plans</h2>
                <PricingTable
                    :plans="plans"
                    :current-plan-slug="currentPlan?.slug ?? 'free'"
                />
            </div>

            <!-- Payment Method -->
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold text-slate-900">Payment Method</h2>
                <div v-if="company.pm_type" class="mb-4 flex items-center gap-3">
                    <div class="flex h-10 w-16 items-center justify-center rounded-md border border-slate-200 bg-slate-50 text-xs font-bold uppercase text-slate-600">
                        {{ company.pm_type }}
                    </div>
                    <span class="text-sm text-slate-700">Ending in {{ company.pm_last_four }}</span>
                </div>
                <p v-else class="mb-4 text-sm text-slate-500">No payment method on file.</p>

                <div class="flex items-center gap-3">
                    <input
                        v-model="paymentMethodForm.payment_method"
                        type="text"
                        placeholder="New payment method ID"
                        class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    />
                    <button
                        @click="updatePaymentMethod"
                        :disabled="paymentMethodForm.processing"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-indigo-700 disabled:opacity-50"
                    >
                        Update
                    </button>
                </div>
            </div>

            <!-- Invoice History -->
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold text-slate-900">Invoice History</h2>

                <div v-if="loadingInvoices" class="flex items-center justify-center py-8">
                    <div class="h-6 w-6 animate-spin rounded-full border-4 border-indigo-200 border-t-indigo-600"></div>
                </div>

                <div v-else-if="invoices.length === 0" class="py-8 text-center text-sm text-slate-500">
                    No invoices yet.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Amount</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">PDF</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="invoice in invoices" :key="invoice.id">
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-700">{{ invoice.date }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-700">{{ invoice.total }}</td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    <span
                                        class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                                        :class="{
                                            'bg-emerald-100 text-emerald-700': invoice.status === 'paid',
                                            'bg-amber-100 text-amber-700': invoice.status === 'open',
                                            'bg-red-100 text-red-700': invoice.status === 'uncollectible',
                                        }"
                                    >
                                        {{ invoice.status }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    <a
                                        v-if="invoice.pdf_url"
                                        :href="invoice.pdf_url"
                                        target="_blank"
                                        class="text-sm text-indigo-600 hover:text-indigo-800"
                                    >
                                        Download
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
