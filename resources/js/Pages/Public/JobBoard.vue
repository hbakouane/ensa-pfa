<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    company: Object,
    jobs: Object,
});

const search = ref('');
const employmentType = ref('');

function applyFilters() {
    router.get(
        route('careers.index', props.company.slug),
        {
            search: search.value || undefined,
            employment_type: employmentType.value || undefined,
        },
        { preserveState: true, preserveScroll: true },
    );
}

function formatSalary(job) {
    if (!job.show_salary || !job.salary_min) return null;
    const fmt = new Intl.NumberFormat('en-US', { style: 'currency', currency: job.salary_currency || 'USD', maximumFractionDigits: 0 });
    if (job.salary_max && job.salary_max !== job.salary_min) {
        return `${fmt.format(job.salary_min)} - ${fmt.format(job.salary_max)}`;
    }
    return fmt.format(job.salary_min);
}

function formatType(type) {
    if (!type) return '';
    return type.replace(/_/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase());
}
</script>

<template>
    <PublicLayout>
        <Head :title="`Careers at ${company.name}`" />

        <!-- Hero -->
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-slate-900">Join {{ company.name }}</h1>
            <p class="mt-2 text-lg text-slate-600">Explore our open positions and find your next opportunity.</p>
        </div>

        <!-- Filters -->
        <div class="mb-6 flex flex-col gap-3 sm:flex-row">
            <div class="flex-1">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search positions..."
                    class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    @keyup.enter="applyFilters"
                />
            </div>
            <select
                v-model="employmentType"
                class="rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                @change="applyFilters"
            >
                <option value="">All Types</option>
                <option value="full_time">Full Time</option>
                <option value="part_time">Part Time</option>
                <option value="contract">Contract</option>
                <option value="internship">Internship</option>
                <option value="freelance">Freelance</option>
            </select>
            <button
                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                @click="applyFilters"
            >
                Search
            </button>
        </div>

        <!-- Job List -->
        <div v-if="jobs.data.length" class="space-y-4">
            <Link
                v-for="job in jobs.data"
                :key="job.id"
                :href="route('careers.show', [company.slug, job.slug])"
                class="block rounded-lg border border-slate-200 bg-white p-5 transition-shadow hover:shadow-md"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">{{ job.title }}</h2>
                        <div class="mt-1 flex flex-wrap items-center gap-3 text-sm text-slate-500">
                            <span v-if="job.department">{{ job.department.name }}</span>
                            <span v-if="job.location">{{ job.location.name }}</span>
                            <span>{{ formatType(job.employment_type) }}</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <span v-if="formatSalary(job)" class="text-sm font-medium text-slate-700">
                            {{ formatSalary(job) }}
                        </span>
                    </div>
                </div>
            </Link>
        </div>

        <div v-else class="rounded-lg border border-slate-200 bg-white py-12 text-center">
            <p class="text-slate-500">No open positions at the moment. Check back soon!</p>
        </div>

        <!-- Pagination -->
        <div v-if="jobs.links && jobs.last_page > 1" class="mt-6 flex justify-center gap-1">
            <Link
                v-for="link in jobs.links"
                :key="link.label"
                :href="link.url ?? '#'"
                class="rounded-lg px-3 py-2 text-sm"
                :class="[
                    link.active ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-100',
                    !link.url ? 'pointer-events-none opacity-50' : '',
                ]"
                v-html="link.label"
            />
        </div>
    </PublicLayout>
</template>
