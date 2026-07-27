<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    candidate: Object,
});

const activeTab = ref('overview');

const initials = computed(() => {
    const first = (props.candidate.first_name ?? '')[0] ?? '';
    const last = (props.candidate.last_name ?? '')[0] ?? '';
    return (first + last).toUpperCase() || '?';
});

const fullName = computed(() => {
    return `${props.candidate.first_name ?? ''} ${props.candidate.last_name ?? ''}`.trim();
});

const skills = computed(() => {
    return (props.candidate.skills ?? []).map((s) =>
        typeof s === 'string' ? s : s.name,
    );
});

function formatDate(dateStr) {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('fr-FR', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}

function formatDateShort(dateStr) {
    if (!dateStr) return 'Présent';
    return new Date(dateStr).toLocaleDateString('fr-FR', {
        month: 'short',
        year: 'numeric',
    });
}

// AI action placeholders
function parseResume() {
    router.post(route('candidates.parse-resume', props.candidate.id));
}

function scoreCandidate() {
    router.post(route('candidates.score', props.candidate.id));
}

function summarizeCandidate() {
    router.post(route('candidates.summarize', props.candidate.id));
}
</script>

<template>
    <AppLayout>
        <!-- Breadcrumb -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <Link :href="route('candidates.index')" class="hover:text-indigo-600">
                    Candidats
                </Link>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <span class="text-slate-700">{{ fullName }}</span>
            </div>
        </div>

        <!-- Profile Header -->
        <div class="mb-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex items-start gap-4">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xl font-bold text-indigo-700">
                        {{ initials }}
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">{{ fullName }}</h1>
                        <p v-if="candidate.headline" class="mt-0.5 text-sm text-slate-600">
                            {{ candidate.headline }}
                        </p>
                        <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-500">
                            <span v-if="candidate.email" class="flex items-center gap-1">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                </svg>
                                {{ candidate.email }}
                            </span>
                            <span v-if="candidate.phone" class="flex items-center gap-1">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                                </svg>
                                {{ candidate.phone }}
                            </span>
                            <span v-if="candidate.location" class="flex items-center gap-1">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                </svg>
                                {{ candidate.location }}
                            </span>
                        </div>
                        <div v-if="candidate.linkedin_url || candidate.portfolio_url" class="mt-2 flex items-center gap-3">
                            <a
                                v-if="candidate.linkedin_url"
                                :href="candidate.linkedin_url"
                                target="_blank"
                                class="text-sm text-indigo-600 hover:text-indigo-800"
                            >
                                LinkedIn
                            </a>
                            <a
                                v-if="candidate.portfolio_url"
                                :href="candidate.portfolio_url"
                                target="_blank"
                                class="text-sm text-indigo-600 hover:text-indigo-800"
                            >
                                Portfolio
                            </a>
                        </div>
                    </div>
                </div>

                <!-- AI Actions -->
                <div class="flex flex-wrap gap-2">
                    <Button variant="secondary" size="sm" @click="parseResume">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        Analyser le CV
                    </Button>
                    <Button variant="secondary" size="sm" @click="scoreCandidate">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                        Évaluer
                    </Button>
                    <Button variant="secondary" size="sm" @click="summarizeCandidate">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z" />
                        </svg>
                        Résumer
                    </Button>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="mb-6 border-b border-slate-200">
            <nav class="flex gap-6">
                <button
                    v-for="tab in ['overview', 'experience', 'education', 'applications']"
                    :key="tab"
                    :class="[
                        'border-b-2 pb-3 text-sm font-medium capitalize transition-colors',
                        activeTab === tab
                            ? 'border-indigo-600 text-indigo-600'
                            : 'border-transparent text-slate-500 hover:text-slate-700',
                    ]"
                    @click="activeTab = tab"
                >
                    {{ tab }}
                </button>
            </nav>
        </div>

        <!-- Overview Tab -->
        <div v-show="activeTab === 'overview'" class="space-y-6">
            <!-- Summary -->
            <div v-if="candidate.summary" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-3 text-lg font-semibold text-slate-900">Résumé</h2>
                <p class="text-sm leading-relaxed text-slate-700 whitespace-pre-line">{{ candidate.summary }}</p>
            </div>

            <!-- Skills -->
            <div v-if="skills.length" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-3 text-lg font-semibold text-slate-900">Compétences</h2>
                <div class="flex flex-wrap gap-2">
                    <span
                        v-for="skill in skills"
                        :key="skill"
                        class="inline-flex rounded-full bg-indigo-50 px-3 py-1 text-sm font-medium text-indigo-700"
                    >
                        {{ skill }}
                    </span>
                </div>
            </div>

            <!-- Contact Info -->
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-3 text-lg font-semibold text-slate-900">Coordonnées</h2>
                <dl class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium uppercase text-slate-400">E-mail</dt>
                        <dd class="mt-1 text-sm text-slate-700">{{ candidate.email ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-slate-400">Téléphone</dt>
                        <dd class="mt-1 text-sm text-slate-700">{{ candidate.phone ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-slate-400">Lieu</dt>
                        <dd class="mt-1 text-sm text-slate-700">{{ candidate.location ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-slate-400">LinkedIn</dt>
                        <dd class="mt-1 text-sm">
                            <a
                                v-if="candidate.linkedin_url"
                                :href="candidate.linkedin_url"
                                target="_blank"
                                class="text-indigo-600 hover:text-indigo-800"
                            >
                                {{ candidate.linkedin_url }}
                            </a>
                            <span v-else class="text-slate-700">-</span>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Experience Tab -->
        <div v-show="activeTab === 'experience'" class="space-y-4">
            <div
                v-for="exp in (candidate.experiences ?? [])"
                :key="exp.id ?? exp.company"
                class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">{{ exp.title }}</h3>
                        <p class="mt-0.5 text-sm text-slate-600">{{ exp.company }}</p>
                    </div>
                    <span class="shrink-0 text-xs text-slate-500">
                        {{ formatDateShort(exp.start_date) }} - {{ formatDateShort(exp.end_date) }}
                    </span>
                </div>
                <p v-if="exp.description" class="mt-3 text-sm leading-relaxed text-slate-600">
                    {{ exp.description }}
                </p>
            </div>

            <div
                v-if="!(candidate.experiences ?? []).length"
                class="flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-white py-12"
            >
                <p class="text-sm text-slate-500">Aucune expérience enregistrée.</p>
            </div>
        </div>

        <!-- Education Tab -->
        <div v-show="activeTab === 'education'" class="space-y-4">
            <div
                v-for="edu in (candidate.educations ?? [])"
                :key="edu.id ?? edu.institution"
                class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">
                            {{ edu.degree }}<span v-if="edu.field"> en {{ edu.field }}</span>
                        </h3>
                        <p class="mt-0.5 text-sm text-slate-600">{{ edu.institution }}</p>
                    </div>
                    <span class="shrink-0 text-xs text-slate-500">
                        {{ formatDateShort(edu.start_date) }} - {{ formatDateShort(edu.end_date) }}
                    </span>
                </div>
            </div>

            <div
                v-if="!(candidate.educations ?? []).length"
                class="flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-white py-12"
            >
                <p class="text-sm text-slate-500">Aucune formation enregistrée.</p>
            </div>
        </div>

        <!-- Applications Tab -->
        <div v-show="activeTab === 'applications'">
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Offre d'emploi</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Étape</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Score</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Candidaté le</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="app in (candidate.applications ?? [])"
                                :key="app.id"
                                class="transition-colors hover:bg-slate-50"
                            >
                                <td class="px-6 py-4">
                                    <Link
                                        v-if="app.job"
                                        :href="route('jobs.show', app.job.id)"
                                        class="text-sm font-medium text-slate-900 hover:text-indigo-600"
                                    >
                                        {{ app.job.title }}
                                    </Link>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <Badge
                                        :label="app.stage?.name ?? app.current_stage ?? '-'"
                                        color="bg-slate-100 text-slate-700"
                                    />
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                    <span v-if="app.score != null" :class="[
                                        'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium',
                                        app.score >= 80 ? 'bg-emerald-100 text-emerald-700' :
                                        app.score >= 50 ? 'bg-amber-100 text-amber-700' :
                                        'bg-red-100 text-red-700'
                                    ]">
                                        {{ app.score }}
                                    </span>
                                    <span v-else class="text-slate-400">-</span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">
                                    {{ formatDate(app.created_at) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    v-if="!(candidate.applications ?? []).length"
                    class="flex flex-col items-center justify-center py-12"
                >
                    <p class="text-sm text-slate-500">Aucune candidature pour le moment.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
