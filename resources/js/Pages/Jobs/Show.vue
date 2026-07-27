<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    job: Object,
});

const activeTab = ref('details');

const statusBadge = {
    draft: 'bg-slate-100 text-slate-700',
    published: 'bg-emerald-100 text-emerald-700',
    closed: 'bg-amber-100 text-amber-700',
    archived: 'bg-red-100 text-red-700',
};

const employmentLabels = {
    full_time: 'Full Time',
    part_time: 'Part Time',
    contract: 'Contract',
    internship: 'Internship',
    temporary: 'Temporary',
};

const experienceLabels = {
    entry: 'Entry Level',
    mid: 'Mid Level',
    senior: 'Senior Level',
    lead: 'Lead',
    executive: 'Executive',
};

const remotePolicyLabels = {
    onsite: 'On-site',
    remote: 'Remote',
    hybrid: 'Hybrid',
};

const skills = computed(() => {
    return (props.job.skills ?? []).map((s) =>
        typeof s === 'string' ? s : s.name,
    );
});

const stageStats = computed(() => {
    if (!props.job.stage_stats) return [];
    return props.job.stage_stats;
});

const salaryDisplay = computed(() => {
    if (!props.job.salary_min && !props.job.salary_max) return null;
    const currency = props.job.salary_currency ?? 'USD';
    const formatter = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency,
        minimumFractionDigits: 0,
    });
    const min = props.job.salary_min ? formatter.format(props.job.salary_min) : null;
    const max = props.job.salary_max ? formatter.format(props.job.salary_max) : null;
    if (min && max) return `${min} - ${max}`;
    if (min) return `From ${min}`;
    return `Up to ${max}`;
});

function publishJob() {
    router.patch(route('jobs.publish', props.job.id));
}

function closeJob() {
    router.patch(route('jobs.close', props.job.id));
}

function archiveJob() {
    router.patch(route('jobs.archive', props.job.id));
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
        <!-- Breadcrumb -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <Link :href="route('jobs.index')" class="hover:text-indigo-600">
                    Jobs
                </Link>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                <span class="text-slate-700">{{ job.title }}</span>
            </div>
        </div>

        <!-- Header -->
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-900">
                        {{ job.title }}
                    </h1>
                    <Badge
                        :label="job.status"
                        :color="statusBadge[job.status] ?? 'bg-slate-100 text-slate-700'"
                    />
                </div>
                <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-500">
                    <span v-if="job.department?.name" class="flex items-center gap-1">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                        </svg>
                        {{ job.department.name }}
                    </span>
                    <span v-if="job.location?.name" class="flex items-center gap-1">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                        </svg>
                        {{ job.location.name }}
                    </span>
                    <span v-if="job.employment_type" class="flex items-center gap-1">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ employmentLabels[job.employment_type] ?? job.employment_type }}
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                        Posted {{ formatDate(job.created_at) }}
                    </span>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <Button
                    v-if="job.status === 'draft'"
                    variant="primary"
                    size="sm"
                    @click="publishJob"
                >
                    Publish
                </Button>
                <Button
                    v-if="job.status === 'published'"
                    variant="secondary"
                    size="sm"
                    @click="closeJob"
                >
                    Close
                </Button>
                <Button
                    v-if="job.status === 'closed'"
                    variant="ghost"
                    size="sm"
                    @click="archiveJob"
                >
                    Archive
                </Button>
                <Link :href="route('jobs.edit', job.id)">
                    <Button variant="secondary" size="sm">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                        </svg>
                        Edit
                    </Button>
                </Link>
                <Link :href="route('pipeline.show', job.id)">
                    <Button variant="secondary" size="sm">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7" />
                        </svg>
                        View Pipeline
                    </Button>
                </Link>
            </div>
        </div>

        <!-- Stage Stats -->
        <div v-if="stageStats.length" class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-6">
            <div
                v-for="stage in stageStats"
                :key="stage.name"
                class="rounded-lg border border-slate-200 bg-white p-3 text-center shadow-sm"
            >
                <p class="text-2xl font-bold text-slate-900">{{ stage.count }}</p>
                <p class="mt-0.5 text-xs text-slate-500">{{ stage.name }}</p>
            </div>
        </div>

        <!-- Tabs -->
        <div class="mb-6 border-b border-slate-200">
            <nav class="flex gap-6">
                <button
                    :class="[
                        'border-b-2 pb-3 text-sm font-medium transition-colors',
                        activeTab === 'details'
                            ? 'border-indigo-600 text-indigo-600'
                            : 'border-transparent text-slate-500 hover:text-slate-700',
                    ]"
                    @click="activeTab = 'details'"
                >
                    Details
                </button>
                <button
                    :class="[
                        'border-b-2 pb-3 text-sm font-medium transition-colors',
                        activeTab === 'applications'
                            ? 'border-indigo-600 text-indigo-600'
                            : 'border-transparent text-slate-500 hover:text-slate-700',
                    ]"
                    @click="activeTab = 'applications'"
                >
                    Applications
                    <span class="ml-1.5 rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">
                        {{ job.applications_count ?? 0 }}
                    </span>
                </button>
            </nav>
        </div>

        <!-- Details tab -->
        <div v-show="activeTab === 'details'" class="space-y-6">
            <!-- Overview card -->
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold text-slate-900">Overview</h2>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <p class="text-xs font-medium uppercase text-slate-400">Experience Level</p>
                        <p class="mt-1 text-sm text-slate-700">
                            {{ experienceLabels[job.experience_level] ?? job.experience_level ?? '-' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase text-slate-400">Remote Policy</p>
                        <p class="mt-1 text-sm text-slate-700">
                            {{ remotePolicyLabels[job.remote_policy] ?? job.remote_policy ?? '-' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase text-slate-400">Positions</p>
                        <p class="mt-1 text-sm text-slate-700">{{ job.positions_count ?? 1 }}</p>
                    </div>
                    <div v-if="salaryDisplay">
                        <p class="text-xs font-medium uppercase text-slate-400">Salary Range</p>
                        <p class="mt-1 text-sm text-slate-700">{{ salaryDisplay }}</p>
                    </div>
                    <div v-if="job.category?.name">
                        <p class="text-xs font-medium uppercase text-slate-400">Category</p>
                        <p class="mt-1 text-sm text-slate-700">{{ job.category.name }}</p>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div v-if="job.description" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-3 text-lg font-semibold text-slate-900">Description</h2>
                <div class="prose prose-sm max-w-none text-slate-700 whitespace-pre-line">
                    {{ job.description }}
                </div>
            </div>

            <!-- Requirements -->
            <div v-if="job.requirements" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-3 text-lg font-semibold text-slate-900">Requirements</h2>
                <div class="prose prose-sm max-w-none text-slate-700 whitespace-pre-line">
                    {{ job.requirements }}
                </div>
            </div>

            <!-- Responsibilities -->
            <div v-if="job.responsibilities" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-3 text-lg font-semibold text-slate-900">Responsibilities</h2>
                <div class="prose prose-sm max-w-none text-slate-700 whitespace-pre-line">
                    {{ job.responsibilities }}
                </div>
            </div>

            <!-- Benefits -->
            <div v-if="job.benefits" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-3 text-lg font-semibold text-slate-900">Benefits</h2>
                <div class="prose prose-sm max-w-none text-slate-700 whitespace-pre-line">
                    {{ job.benefits }}
                </div>
            </div>

            <!-- Skills -->
            <div v-if="skills.length" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-3 text-lg font-semibold text-slate-900">Skills</h2>
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
        </div>

        <!-- Applications tab -->
        <div v-show="activeTab === 'applications'">
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Candidate</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Stage</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Score</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Applied</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="app in (job.applications ?? [])"
                                :key="app.id"
                                class="transition-colors hover:bg-slate-50"
                            >
                                <td class="px-6 py-4">
                                    <Link
                                        v-if="app.candidate"
                                        :href="route('candidates.show', app.candidate.id)"
                                        class="text-sm font-medium text-slate-900 hover:text-indigo-600"
                                    >
                                        {{ app.candidate.first_name }} {{ app.candidate.last_name }}
                                    </Link>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <Badge
                                        :label="app.stage?.name ?? '-'"
                                        color="bg-slate-100 text-slate-700"
                                    />
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                    {{ app.score ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">
                                    {{ formatDate(app.created_at) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    v-if="!(job.applications ?? []).length"
                    class="flex flex-col items-center justify-center py-12"
                >
                    <svg class="h-12 w-12 text-slate-300" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <p class="mt-3 text-sm text-slate-500">No applications yet.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
