<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    offer: {
        type: Object,
        required: true,
    },
    company: {
        type: Object,
        default: () => ({}),
    },
});

const candidate = computed(() => props.offer.application?.candidate ?? {});
const job = computed(() => props.offer.application?.job ?? {});
const hasResponded = computed(() => !!props.offer.responded_at);
const isExpired = computed(() => {
    if (!props.offer.expiry_date) return false;
    return new Date(props.offer.expiry_date) < new Date();
});

const confirmAction = ref(null); // 'accept' or 'decline'

const form = useForm({
    response: '',
});

function promptAccept() {
    confirmAction.value = 'accept';
}

function promptDecline() {
    confirmAction.value = 'decline';
}

function cancelAction() {
    confirmAction.value = null;
}

function submitResponse(response) {
    form.response = response;
    form.post(route('public.offers.respond', props.offer.token), {
        preserveScroll: true,
        onSuccess: () => {
            confirmAction.value = null;
        },
    });
}

function formatCurrency(amount, currency) {
    if (!amount) return '-';
    const cur = currency ?? 'USD';
    try {
        return new Intl.NumberFormat('fr-FR', {
            style: 'currency',
            currency: cur,
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(amount);
    } catch {
        return `${cur} ${Number(amount).toLocaleString()}`;
    }
}

function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        weekday: 'long',
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    });
}
</script>

<template>
    <PublicLayout>
        <div class="mx-auto max-w-3xl">
            <!-- Company Branding -->
            <div class="mb-8 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-600 text-2xl font-bold text-white">
                    {{ (company.name ?? 'C').charAt(0).toUpperCase() }}
                </div>
                <h1 class="mt-4 text-2xl font-bold text-slate-900">
                    {{ company.name ?? 'Entreprise' }}
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    Offre d'emploi
                </p>
            </div>

            <!-- Status Banner -->
            <div v-if="hasResponded" class="mb-6 rounded-xl border p-4 text-center"
                :class="{
                    'border-emerald-200 bg-emerald-50': offer.status === 'accepted',
                    'border-red-200 bg-red-50': offer.status === 'declined',
                }"
            >
                <p class="text-sm font-medium"
                    :class="{
                        'text-emerald-700': offer.status === 'accepted',
                        'text-red-700': offer.status === 'declined',
                    }"
                >
                    <template v-if="offer.status === 'accepted'">
                        Vous avez accepté cette offre. Bienvenue dans l'équipe !
                    </template>
                    <template v-else>
                        Vous avez refusé cette offre.
                    </template>
                </p>
            </div>

            <div v-if="isExpired && !hasResponded" class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-center">
                <p class="text-sm font-medium text-amber-700">
                    Cette offre a expiré. Veuillez contacter l'équipe de recrutement pour plus d'informations.
                </p>
            </div>

            <div v-if="offer.status === 'withdrawn'" class="mb-6 rounded-xl border border-slate-200 bg-slate-50 p-4 text-center">
                <p class="text-sm font-medium text-slate-600">
                    Cette offre a été retirée par l'entreprise.
                </p>
            </div>

            <!-- Offer Summary -->
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="border-b border-slate-100 pb-4">
                    <h2 class="text-lg font-semibold text-slate-900">Résumé de l'offre</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Cher(e) {{ candidate.name }}, félicitations pour votre offre !
                    </p>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded-lg bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Poste</p>
                        <p class="mt-1 text-base font-semibold text-slate-900">{{ job.title }}</p>
                    </div>
                    <div class="rounded-lg bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Salaire</p>
                        <p class="mt-1 text-base font-semibold text-slate-900">
                            {{ formatCurrency(offer.salary, offer.salary_currency) }}
                            <span v-if="offer.salary_period" class="text-sm font-normal text-slate-500">
                                / {{ offer.salary_period }}
                            </span>
                        </p>
                    </div>
                    <div class="rounded-lg bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Date de début</p>
                        <p class="mt-1 text-base font-semibold text-slate-900">{{ formatDate(offer.start_date) }}</p>
                    </div>
                    <div class="rounded-lg bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Expiration de l'offre</p>
                        <p class="mt-1 text-base font-semibold text-slate-900">{{ formatDate(offer.expiry_date) }}</p>
                    </div>
                </div>
            </div>

            <!-- Offer Content -->
            <div v-if="offer.content" class="mt-6 rounded-xl border border-slate-200 bg-white p-8 shadow-sm">
                <div class="prose prose-slate max-w-none text-sm leading-relaxed" v-html="offer.content" />
            </div>

            <!-- Response Buttons -->
            <div v-if="!hasResponded && !isExpired && offer.status !== 'withdrawn'" class="mt-8">
                <!-- Confirmation dialog -->
                <div v-if="confirmAction" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm text-center">
                    <p class="text-base font-medium text-slate-900">
                        <template v-if="confirmAction === 'accept'">
                            Êtes-vous sûr(e) de vouloir accepter cette offre ?
                        </template>
                        <template v-else>
                            Êtes-vous sûr(e) de vouloir refuser cette offre ?
                        </template>
                    </p>
                    <p class="mt-1 text-sm text-slate-500">Cette action est irréversible.</p>
                    <div class="mt-6 flex items-center justify-center gap-4">
                        <button
                            class="rounded-lg border border-slate-300 bg-white px-6 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50"
                            @click="cancelAction"
                        >
                            Retour
                        </button>
                        <button
                            v-if="confirmAction === 'accept'"
                            class="rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-emerald-700"
                            :disabled="form.processing"
                            @click="submitResponse('accepted')"
                        >
                            <span v-if="form.processing">Traitement en cours...</span>
                            <span v-else>Confirmer l'acceptation</span>
                        </button>
                        <button
                            v-else
                            class="rounded-lg bg-red-600 px-6 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-red-700"
                            :disabled="form.processing"
                            @click="submitResponse('declined')"
                        >
                            <span v-if="form.processing">Traitement en cours...</span>
                            <span v-else>Confirmer le refus</span>
                        </button>
                    </div>
                </div>

                <!-- Default buttons -->
                <div v-else class="flex items-center justify-center gap-4">
                    <button
                        class="flex-1 rounded-xl bg-emerald-600 px-8 py-4 text-center text-lg font-semibold text-white shadow-sm transition-all hover:bg-emerald-700 hover:shadow-md sm:flex-none sm:px-16"
                        @click="promptAccept"
                    >
                        <svg class="mx-auto mb-1 h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Accepter l'offre
                    </button>
                    <button
                        class="flex-1 rounded-xl border-2 border-red-200 bg-white px-8 py-4 text-center text-lg font-semibold text-red-600 shadow-sm transition-all hover:bg-red-50 hover:shadow-md sm:flex-none sm:px-16"
                        @click="promptDecline"
                    >
                        <svg class="mx-auto mb-1 h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Refuser l'offre
                    </button>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
