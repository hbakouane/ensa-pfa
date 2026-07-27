<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import OfferPreview from '@/Components/Offers/OfferPreview.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    offer: {
        type: Object,
        required: true,
    },
});

const candidate = computed(() => props.offer.application?.candidate ?? {});
const job = computed(() => props.offer.application?.job ?? {});
const approvals = computed(() => props.offer.approvals ?? []);
const template = computed(() => props.offer.template ?? null);

const processing = ref(false);

const statusBadgeColors = {
    draft: 'bg-slate-100 text-slate-700',
    pending_approval: 'bg-amber-100 text-amber-700',
    approved: 'bg-blue-100 text-blue-700',
    sent: 'bg-indigo-100 text-indigo-700',
    accepted: 'bg-emerald-100 text-emerald-700',
    declined: 'bg-red-100 text-red-700',
    withdrawn: 'bg-slate-100 text-slate-600',
    expired: 'bg-orange-100 text-orange-700',
};

const approvalStatusColors = {
    approved: 'text-emerald-600',
    rejected: 'text-red-600',
    pending: 'text-amber-600',
};

const approvalStatusIcons = {
    approved: 'M5 13l4 4L19 7',
    rejected: 'M6 18L18 6M6 6l12 12',
    pending: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
};

function statusLabel(status) {
    if (!status) return '-';
    return status.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

function formatCurrency(amount, currency) {
    if (!amount) return '-';
    const cur = currency ?? 'USD';
    try {
        return new Intl.NumberFormat('en-US', {
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
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}

function getInitials(name) {
    if (!name) return '?';
    return name
        .split(' ')
        .map((n) => n.charAt(0))
        .join('')
        .toUpperCase()
        .slice(0, 2);
}

function sendOffer() {
    if (!confirm('Êtes-vous sûr de vouloir envoyer cette offre au candidat ?')) return;
    processing.value = true;
    router.post(route('offers.send', props.offer.id), {}, {
        preserveScroll: true,
        onFinish: () => (processing.value = false),
    });
}

function withdrawOffer() {
    if (!confirm('Êtes-vous sûr de vouloir retirer cette offre ?')) return;
    processing.value = true;
    router.post(route('offers.withdraw', props.offer.id), {}, {
        preserveScroll: true,
        onFinish: () => (processing.value = false),
    });
}

function downloadPdf() {
    window.open(route('offers.download', props.offer.id), '_blank');
}
</script>

<template>
    <AppLayout>
        <!-- Breadcrumb -->
        <nav class="mb-6 flex items-center gap-2 text-sm text-slate-500">
            <Link :href="route('offers.index')" class="transition-colors hover:text-indigo-600">
                Offres
            </Link>
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-slate-900">Détails de l'offre</span>
        </nav>

        <!-- Header -->
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-900">
                        Offre pour {{ candidate.name }}
                    </h1>
                    <Badge
                        :label="statusLabel(offer.status)"
                        :color="statusBadgeColors[offer.status] ?? 'bg-slate-100 text-slate-700'"
                    />
                </div>
                <p class="mt-1 text-sm text-slate-500">
                    {{ job.title }}
                </p>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-3">
                <Button
                    v-if="offer.status === 'approved' || offer.status === 'draft'"
                    variant="primary"
                    :loading="processing"
                    @click="sendOffer"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Envoyer l'offre
                </Button>
                <Button variant="secondary" @click="downloadPdf">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Télécharger le PDF
                </Button>
                <Button
                    v-if="offer.status === 'sent'"
                    variant="danger"
                    :loading="processing"
                    @click="withdrawOffer"
                >
                    Retirer
                </Button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Main Content -->
            <div class="space-y-6 lg:col-span-2">
                <!-- Offer Details -->
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">Détails de l'offre</h2>
                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-slate-500">Salaire</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">
                                {{ formatCurrency(offer.salary, offer.salary_currency) }}
                                <span v-if="offer.salary_period" class="font-normal text-slate-500">
                                    / {{ offer.salary_period }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-slate-500">Devise</dt>
                            <dd class="mt-1 text-sm text-slate-900">
                                {{ offer.salary_currency ?? '-' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-slate-500">Date de début</dt>
                            <dd class="mt-1 text-sm text-slate-900">
                                {{ formatDate(offer.start_date) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-slate-500">Date d'expiration</dt>
                            <dd class="mt-1 text-sm text-slate-900">
                                {{ formatDate(offer.expiry_date) }}
                            </dd>
                        </div>
                        <div v-if="offer.sent_at">
                            <dt class="text-sm font-medium text-slate-500">Envoyé le</dt>
                            <dd class="mt-1 text-sm text-slate-900">
                                {{ formatDate(offer.sent_at) }}
                            </dd>
                        </div>
                        <div v-if="offer.responded_at">
                            <dt class="text-sm font-medium text-slate-500">Répondu le</dt>
                            <dd class="mt-1 text-sm text-slate-900">
                                {{ formatDate(offer.responded_at) }}
                            </dd>
                        </div>
                    </div>
                </div>

                <!-- Offer Content Preview -->
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">Lettre d'offre</h2>
                    <div class="mt-4">
                        <OfferPreview :content="offer.content ?? ''" />
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Candidate Card -->
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Candidat</h3>
                    <div class="mt-3 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-700">
                            {{ getInitials(candidate.name) }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-900">{{ candidate.name }}</p>
                            <p class="text-xs text-slate-500">{{ candidate.email }}</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-slate-600">
                            <span class="font-medium">Poste :</span> {{ job.title }}
                        </p>
                    </div>
                </div>

                <!-- Template Info -->
                <div v-if="template" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Modèle utilisé</h3>
                    <p class="mt-2 text-sm font-medium text-slate-900">{{ template.name }}</p>
                </div>

                <!-- Approval Status -->
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Approbations</h3>

                    <div v-if="approvals.length === 0" class="mt-3">
                        <p class="text-sm text-slate-500">Aucune approbation requise.</p>
                    </div>

                    <ul v-else class="mt-3 space-y-3">
                        <li v-for="approval in approvals" :key="approval.id" class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-6 w-6 items-center justify-center rounded-full"
                                    :class="{
                                        'bg-emerald-100': approval.status === 'approved',
                                        'bg-red-100': approval.status === 'rejected',
                                        'bg-amber-100': approval.status === 'pending' || !approval.status,
                                    }"
                                >
                                    <svg
                                        class="h-3.5 w-3.5"
                                        :class="approvalStatusColors[approval.status ?? 'pending']"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            :d="approvalStatusIcons[approval.status ?? 'pending']"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-slate-900">{{ approval.user?.name ?? 'Inconnu' }}</p>
                                    <p v-if="approval.responded_at" class="text-xs text-slate-500">
                                        {{ formatDate(approval.responded_at) }}
                                    </p>
                                </div>
                            </div>
                            <span
                                class="text-xs font-medium capitalize"
                                :class="approvalStatusColors[approval.status ?? 'pending']"
                            >
                                {{ approval.status ?? 'pending' }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
