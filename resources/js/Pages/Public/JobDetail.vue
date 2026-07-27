<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    company: Object,
    job: Object,
});

function formatSalary() {
    if (!props.job.show_salary || !props.job.salary_min) return null;
    const fmt = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: props.job.salary_currency || 'EUR', maximumFractionDigits: 0 });
    if (props.job.salary_max && props.job.salary_max !== props.job.salary_min) {
        return `${fmt.format(props.job.salary_min)} - ${fmt.format(props.job.salary_max)}`;
    }
    return fmt.format(props.job.salary_min);
}

function formatType(type) {
    if (!type) return '';
    const labels = {
        full_time: 'Temps plein',
        part_time: 'Temps partiel',
        contract: 'CDD',
        internship: 'Stage',
        freelance: 'Freelance',
        remote: 'Télétravail',
        on_site: 'Sur site',
        hybrid: 'Hybride',
        entry: 'Débutant',
        mid: 'Intermédiaire',
        senior: 'Senior',
        executive: 'Cadre dirigeant',
    };
    return labels[type] || type.replace(/_/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase());
}
</script>

<template>
    <PublicLayout>
        <Head :title="`${job.title} - ${company.name}`" />

        <!-- Breadcrumb -->
        <div class="mb-6">
            <Link
                :href="route('careers.index', company.slug)"
                class="inline-flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-800"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
                Tous les postes
            </Link>
        </div>

        <div class="grid gap-8 lg:grid-cols-3">
            <!-- Main content -->
            <div class="lg:col-span-2">
                <h1 class="text-3xl font-bold text-slate-900">{{ job.title }}</h1>

                <div class="mt-3 flex flex-wrap items-center gap-3">
                    <span v-if="job.department" class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">
                        {{ job.department.name }}
                    </span>
                    <span v-if="job.location" class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">
                        {{ job.location.name }}
                    </span>
                    <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700">
                        {{ formatType(job.employment_type) }}
                    </span>
                    <span v-if="job.experience_level" class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700">
                        {{ formatType(job.experience_level) }}
                    </span>
                </div>

                <!-- Description -->
                <div class="mt-8">
                    <h2 class="text-lg font-semibold text-slate-900">Description du poste</h2>
                    <div class="prose prose-slate mt-3 max-w-none" v-html="job.description" />
                </div>

                <!-- Requirements -->
                <div v-if="job.requirements" class="mt-8">
                    <h2 class="text-lg font-semibold text-slate-900">Exigences</h2>
                    <div class="prose prose-slate mt-3 max-w-none" v-html="job.requirements" />
                </div>

                <!-- Benefits -->
                <div v-if="job.benefits" class="mt-8">
                    <h2 class="text-lg font-semibold text-slate-900">Avantages</h2>
                    <div class="prose prose-slate mt-3 max-w-none" v-html="job.benefits" />
                </div>

                <!-- Skills -->
                <div v-if="job.skills?.length" class="mt-8">
                    <h2 class="text-lg font-semibold text-slate-900">Compétences</h2>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <span
                            v-for="skill in job.skills"
                            :key="skill.id"
                            :class="[
                                'inline-flex items-center rounded-full px-3 py-1 text-xs font-medium',
                                skill.is_required ? 'bg-red-50 text-red-700' : 'bg-slate-100 text-slate-700',
                            ]"
                        >
                            {{ skill.name }}
                            <span v-if="skill.is_required" class="ml-1 text-[10px]">(Requis)</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div>
                <div class="sticky top-8 rounded-lg border border-slate-200 bg-white p-6">
                    <div v-if="formatSalary()" class="mb-4">
                        <p class="text-sm text-slate-500">Salaire</p>
                        <p class="text-lg font-semibold text-slate-900">{{ formatSalary() }}</p>
                    </div>

                    <div v-if="job.location" class="mb-4">
                        <p class="text-sm text-slate-500">Lieu</p>
                        <p class="font-medium text-slate-900">{{ job.location.name }}</p>
                    </div>

                    <div class="mb-4">
                        <p class="text-sm text-slate-500">Type de contrat</p>
                        <p class="font-medium text-slate-900">{{ formatType(job.employment_type) }}</p>
                    </div>

                    <div v-if="job.experience_level" class="mb-6">
                        <p class="text-sm text-slate-500">Niveau d'expérience</p>
                        <p class="font-medium text-slate-900">{{ formatType(job.experience_level) }}</p>
                    </div>

                    <Link
                        :href="route('careers.apply', [company.slug, job.slug])"
                        class="block w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-center text-sm font-semibold text-white transition-colors hover:bg-indigo-700"
                    >
                        Postuler maintenant
                    </Link>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
