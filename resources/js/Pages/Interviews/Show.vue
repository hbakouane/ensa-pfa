<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import ScorecardForm from '@/Components/Interviews/ScorecardForm.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    interview: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const currentUser = computed(() => page.props.auth?.user ?? {});

const candidate = computed(() => props.interview.application?.candidate ?? {});
const job = computed(() => props.interview.application?.job ?? {});
const interviewers = computed(() => props.interview.interviewers ?? []);
const scorecards = computed(() => props.interview.scorecards ?? []);

const hasSubmittedScorecard = computed(() => {
    return scorecards.value.some((sc) => sc.user_id === currentUser.value.id);
});

const isInterviewer = computed(() => {
    return interviewers.value.some((i) => i.id === currentUser.value.id);
});

const typeBadgeColors = {
    phone: 'bg-sky-100 text-sky-700',
    video: 'bg-violet-100 text-violet-700',
    onsite: 'bg-amber-100 text-amber-700',
    technical: 'bg-indigo-100 text-indigo-700',
    panel: 'bg-emerald-100 text-emerald-700',
};

const statusBadgeColors = {
    scheduled: 'bg-blue-100 text-blue-700',
    in_progress: 'bg-amber-100 text-amber-700',
    completed: 'bg-emerald-100 text-emerald-700',
    cancelled: 'bg-red-100 text-red-700',
};

const responseStatusColors = {
    accepted: 'text-emerald-600',
    declined: 'text-red-600',
    pending: 'text-amber-600',
    tentative: 'text-sky-600',
};

const recommendationLabels = {
    strong_yes: 'Oui fortement',
    yes: 'Oui',
    maybe: 'Peut-être',
    no: 'Non',
    strong_no: 'Non fortement',
};

const recommendationColors = {
    strong_yes: 'bg-emerald-100 text-emerald-700',
    yes: 'bg-green-100 text-green-700',
    maybe: 'bg-amber-100 text-amber-700',
    no: 'bg-orange-100 text-orange-700',
    strong_no: 'bg-red-100 text-red-700',
};

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

function formatTime(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleTimeString('fr-FR', {
        hour: 'numeric',
        minute: '2-digit',
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

function renderStars(rating) {
    return Array.from({ length: 5 }, (_, i) => i < rating);
}
</script>

<template>
    <AppLayout>
        <!-- Breadcrumb -->
        <nav class="mb-6 flex items-center gap-2 text-sm text-slate-500">
            <Link :href="route('interviews.index')" class="transition-colors hover:text-indigo-600">
                Entretiens
            </Link>
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-slate-900">{{ interview.title ?? 'Détails de l\'entretien' }}</span>
        </nav>

        <!-- Header -->
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-900">
                        {{ interview.title ?? 'Entretien' }}
                    </h1>
                    <Badge
                        :label="interview.type ? interview.type.charAt(0).toUpperCase() + interview.type.slice(1) : '-'"
                        :color="typeBadgeColors[interview.type] ?? 'bg-slate-100 text-slate-700'"
                    />
                    <Badge
                        :label="interview.status ? interview.status.replace('_', ' ').replace(/\b\w/g, (c) => c.toUpperCase()) : '-'"
                        :color="statusBadgeColors[interview.status] ?? 'bg-slate-100 text-slate-700'"
                    />
                </div>
                <p class="mt-1 text-sm text-slate-500">
                    {{ candidate.name }} pour {{ job.title }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Main Content -->
            <div class="space-y-6 lg:col-span-2">
                <!-- Interview Details -->
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">Détails de l'entretien</h2>
                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-slate-500">Date</dt><!-- Date is same in French -->
                            <dd class="mt-1 text-sm text-slate-900">
                                {{ formatDate(interview.scheduled_at) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-slate-500">Heure</dt>
                            <dd class="mt-1 text-sm text-slate-900">
                                {{ formatTime(interview.scheduled_at) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-slate-500">Durée</dt>
                            <dd class="mt-1 text-sm text-slate-900">
                                {{ interview.duration_minutes ?? '-' }} minutes
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-slate-500">Type</dt><!-- Type is same in French -->
                            <dd class="mt-1 text-sm text-slate-900 capitalize">
                                {{ interview.type ?? '-' }}
                            </dd>
                        </div>
                        <div v-if="interview.location">
                            <dt class="text-sm font-medium text-slate-500">Lieu</dt>
                            <dd class="mt-1 text-sm text-slate-900">
                                {{ interview.location }}
                            </dd>
                        </div>
                        <div v-if="interview.meeting_url">
                            <dt class="text-sm font-medium text-slate-500">Lien de la réunion</dt>
                            <dd class="mt-1">
                                <a
                                    :href="interview.meeting_url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-sm font-medium text-indigo-600 transition-colors hover:text-indigo-800"
                                >
                                    Rejoindre la réunion
                                    <svg class="ml-1 inline h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>
                            </dd>
                        </div>
                    </div>
                    <div v-if="interview.notes" class="mt-6 border-t border-slate-100 pt-4">
                        <dt class="text-sm font-medium text-slate-500">Notes</dt><!-- Notes is same in French -->
                        <dd class="mt-1 text-sm leading-relaxed text-slate-700">
                            {{ interview.notes }}
                        </dd>
                    </div>
                </div>

                <!-- Scorecards -->
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">Fiches d'évaluation</h2>

                    <div v-if="scorecards.length === 0 && !isInterviewer" class="mt-4 flex flex-col items-center justify-center py-8">
                        <svg class="h-12 w-12 text-slate-300" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="mt-3 text-sm text-slate-500">Aucune fiche d'évaluation soumise pour le moment.</p>
                    </div>

                    <!-- Submitted scorecards -->
                    <div v-for="scorecard in scorecards" :key="scorecard.id" class="mt-4 rounded-lg border border-slate-100 bg-slate-50 p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-xs font-semibold text-indigo-700">
                                    {{ getInitials(scorecard.user?.name) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-slate-900">
                                        {{ scorecard.user?.name ?? 'Inconnu' }}
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        {{ formatDate(scorecard.created_at) }}
                                    </p>
                                </div>
                            </div>
                            <Badge
                                :label="recommendationLabels[scorecard.recommendation] ?? scorecard.recommendation"
                                :color="recommendationColors[scorecard.recommendation] ?? 'bg-slate-100 text-slate-700'"
                            />
                        </div>

                        <!-- Overall rating stars -->
                        <div class="mt-3 flex items-center gap-1">
                            <span class="mr-2 text-sm font-medium text-slate-600">Global :</span>
                            <svg
                                v-for="(filled, idx) in renderStars(scorecard.overall_rating)"
                                :key="idx"
                                class="h-5 w-5"
                                :class="filled ? 'text-amber-400' : 'text-slate-200'"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </div>

                        <!-- Details -->
                        <div v-if="scorecard.strengths" class="mt-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Points forts</p>
                            <p class="mt-1 text-sm text-slate-700">{{ scorecard.strengths }}</p>
                        </div>
                        <div v-if="scorecard.concerns" class="mt-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Points d'attention</p>
                            <p class="mt-1 text-sm text-slate-700">{{ scorecard.concerns }}</p>
                        </div>
                        <div v-if="scorecard.notes" class="mt-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Notes</p><!-- Notes same in French -->
                            <p class="mt-1 text-sm text-slate-700">{{ scorecard.notes }}</p>
                        </div>

                        <!-- Criteria -->
                        <div v-if="scorecard.criteria && scorecard.criteria.length > 0" class="mt-3 border-t border-slate-200 pt-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Critères</p>
                            <div class="mt-2 space-y-1.5">
                                <div v-for="(criterion, idx) in scorecard.criteria" :key="idx" class="flex items-center justify-between">
                                    <span class="text-sm text-slate-700">{{ criterion.name }}</span>
                                    <div class="flex items-center gap-0.5">
                                        <svg
                                            v-for="(filled, sIdx) in renderStars(criterion.rating)"
                                            :key="sIdx"
                                            class="h-4 w-4"
                                            :class="filled ? 'text-amber-400' : 'text-slate-200'"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Scorecard form for current user -->
                    <div v-if="isInterviewer && !hasSubmittedScorecard && interview.status !== 'cancelled'" class="mt-6 border-t border-slate-200 pt-6">
                        <h3 class="text-base font-semibold text-slate-900">Soumettre votre fiche d'évaluation</h3>
                        <ScorecardForm :interview="interview" class="mt-4" />
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

                <!-- Interviewers -->
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Intervieweurs</h3>
                    <ul class="mt-3 space-y-3">
                        <li v-for="interviewer in interviewers" :key="interviewer.id" class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-200 text-xs font-semibold text-slate-600">
                                    {{ getInitials(interviewer.name) }}
                                </div>
                                <span class="text-sm text-slate-900">{{ interviewer.name }}</span>
                            </div>
                            <span
                                class="text-xs font-medium capitalize"
                                :class="responseStatusColors[interviewer.pivot?.response_status ?? 'pending'] ?? 'text-slate-500'"
                            >
                                {{ interviewer.pivot?.response_status ?? 'pending' }}
                            </span>
                        </li>
                    </ul>
                    <div v-if="interviewers.length === 0" class="mt-3">
                        <p class="text-sm text-slate-500">Aucun intervieweur assigné.</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
