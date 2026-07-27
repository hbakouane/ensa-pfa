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
    return new Date(dateStr).toLocaleDateString('fr-FR', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}
</script>

<template>
    <AppLayout>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-900">Facturation & Abonnement</h1>
            <p class="mt-1 text-sm text-slate-500">
                Gérez votre forfait, votre utilisation et vos détails de paiement.
            </p>
        </div>

        <div class="space-y-8">
            <!-- Current Plan & Usage -->
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold text-slate-900">Forfait actuel</h2>
                <div class="mb-6 flex items-center gap-3">
                    <span class="inline-flex items-center rounded-full bg-indigo-100 px-3 py-1 text-sm font-medium text-indigo-700">
                        {{ currentPlan?.name ?? 'Gratuit' }}
                    </span>
                    <span v-if="subscription && !subscription.ends_at" class="text-sm text-emerald-600 font-medium">Actif</span>
                    <span v-else-if="subscription && subscription.ends_at" class="text-sm text-amber-600 font-medium">Annulé en fin de période</span>
                    <span v-else class="text-sm text-slate-500">Aucun abonnement actif</span>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <UsageIndicator
                        label="Offres actives"
                        :current="usage.jobs.current"
                        :max="usage.jobs.max"
                    />
                    <UsageIndicator
                        label="Candidats"
                        :current="usage.candidates.current"
                        :max="usage.candidates.max"
                    />
                    <UsageIndicator
                        label="Membres de l'équipe"
                        :current="usage.users.current"
                        :max="usage.users.max"
                    />
                    <UsageIndicator
                        label="Analyses IA (ce mois)"
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
                <h2 class="mb-4 text-lg font-semibold text-slate-900">Forfaits disponibles</h2>
                <PricingTable
                    :plans="plans"
                    :current-plan-slug="currentPlan?.slug ?? 'free'"
                />
            </div>

            <!-- Payment Method -->
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold text-slate-900">Moyen de paiement</h2>
                <div v-if="company.pm_type" class="mb-4 flex items-center gap-3">
                    <div class="flex h-10 w-16 items-center justify-center rounded-md border border-slate-200 bg-slate-50 text-xs font-bold uppercase text-slate-600">
                        {{ company.pm_type }}
                    </div>
                    <span class="text-sm text-slate-700">Se terminant par {{ company.pm_last_four }}</span>
                </div>
                <p v-else class="mb-4 text-sm text-slate-500">Aucun moyen de paiement enregistré.</p>

                <div class="flex items-center gap-3">
                    <input
                        v-model="paymentMethodForm.payment_method"
                        type="text"
                        placeholder="ID du nouveau moyen de paiement"
                        class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    />
                    <button
                        @click="updatePaymentMethod"
                        :disabled="paymentMethodForm.processing"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-indigo-700 disabled:opacity-50"
                    >
                        Mettre à jour
                    </button>
                </div>
            </div>

            <!-- Invoice History -->
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold text-slate-900">Historique des factures</h2>

                <div v-if="loadingInvoices" class="flex items-center justify-center py-8">
                    <div class="h-6 w-6 animate-spin rounded-full border-4 border-indigo-200 border-t-indigo-600"></div>
                </div>

                <div v-else-if="invoices.length === 0" class="py-8 text-center text-sm text-slate-500">
                    Aucune facture pour le moment.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Montant</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Statut</th>
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
                                        Télécharger
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
