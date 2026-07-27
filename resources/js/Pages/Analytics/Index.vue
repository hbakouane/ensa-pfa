<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import MetricCard from '@/Components/Analytics/MetricCard.vue';
import PipelineFunnel from '@/Components/Analytics/PipelineFunnel.vue';
import TimeToHireChart from '@/Components/Analytics/TimeToHireChart.vue';
import SourceBreakdownChart from '@/Components/Analytics/SourceBreakdownChart.vue';
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    overview: Object,
    company: Object,
});

const pipelineData = ref({ stages: [], overall_conversion_rate: 0 });
const timeToHireData = ref([]);
const sourceData = ref([]);
const loading = ref(true);

onMounted(async () => {
    try {
        const [pipelineRes, timeToHireRes, sourceRes] = await Promise.all([
            axios.get(route('analytics.pipeline-conversion')),
            axios.get(route('analytics.time-to-hire')),
            axios.get(route('analytics.sources')),
        ]);

        pipelineData.value = pipelineRes.data;
        timeToHireData.value = timeToHireRes.data.trend ?? [];
        sourceData.value = sourceRes.data.sources ?? [];
    } catch (error) {
        console.error('Failed to load analytics data:', error);
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <AppLayout>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-900">Analytique</h1>
            <p class="mt-1 text-sm text-slate-500">
                Suivez vos performances de recrutement et vos indicateurs d'embauche.
            </p>
        </div>

        <!-- Metric Cards -->
        <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <MetricCard
                title="Offres ouvertes"
                :value="overview.active_jobs"
            >
                <template #icon>
                    <svg class="h-6 w-6 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg>
                </template>
            </MetricCard>

            <MetricCard
                title="Candidats actifs"
                :value="overview.total_candidates"
            >
                <template #icon>
                    <svg class="h-6 w-6 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                </template>
            </MetricCard>

            <MetricCard
                title="Candidatures"
                :value="overview.applications_this_period"
                :subtitle="`${overview.total_applications} au total`"
            >
                <template #icon>
                    <svg class="h-6 w-6 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                </template>
            </MetricCard>

            <MetricCard
                title="Délai moyen d'embauche"
                :value="overview.average_score ? `${overview.conversion_rate}%` : 'N/D'"
                :subtitle="`${overview.hires_this_period} embauches cette période`"
            >
                <template #icon>
                    <svg class="h-6 w-6 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </template>
            </MetricCard>
        </div>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Pipeline Funnel -->
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold text-slate-900">Entonnoir du pipeline</h2>
                <div v-if="loading" class="flex h-64 items-center justify-center">
                    <div class="h-8 w-8 animate-spin rounded-full border-4 border-indigo-200 border-t-indigo-600"></div>
                </div>
                <PipelineFunnel
                    v-else
                    :stages="pipelineData.stages"
                />
            </div>

            <!-- Time to Hire Trend -->
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold text-slate-900">Tendance du délai d'embauche</h2>
                <div v-if="loading" class="flex h-64 items-center justify-center">
                    <div class="h-8 w-8 animate-spin rounded-full border-4 border-indigo-200 border-t-indigo-600"></div>
                </div>
                <TimeToHireChart
                    v-else
                    :data="timeToHireData"
                />
            </div>

            <!-- Source Breakdown -->
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
                <h2 class="mb-4 text-lg font-semibold text-slate-900">Sources des candidats</h2>
                <div v-if="loading" class="flex h-64 items-center justify-center">
                    <div class="h-8 w-8 animate-spin rounded-full border-4 border-indigo-200 border-t-indigo-600"></div>
                </div>
                <SourceBreakdownChart
                    v-else
                    :data="sourceData"
                />
            </div>
        </div>
    </AppLayout>
</template>
