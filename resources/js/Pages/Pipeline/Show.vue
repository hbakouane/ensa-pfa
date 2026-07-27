<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import KanbanBoard from '@/Components/Pipeline/KanbanBoard.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    job: Object,
    stages: Array,
    applications: Object,
});
</script>

<template>
    <AppLayout>
        <Head :title="`Pipeline - ${job.title}`" />

        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 text-sm text-slate-500">
                        <Link :href="route('jobs.index')" class="hover:text-indigo-600">Offres d'emploi</Link>
                        <span>/</span>
                        <Link :href="route('jobs.show', job.id)" class="hover:text-indigo-600">{{ job.title }}</Link>
                        <span>/</span>
                        <span class="text-slate-700">Pipeline</span><!-- Pipeline is same in French -->
                    </div>
                    <h1 class="mt-1 text-2xl font-bold text-slate-900">Pipeline - {{ job.title }}</h1>
                </div>

                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700">
                        {{ job.employment_type?.replace('_', ' ') }}
                    </span>
                    <span v-if="job.department" class="text-sm text-slate-500">
                        {{ job.department.name }}
                    </span>
                </div>
            </div>
        </div>

        <KanbanBoard
            :job="job"
            :stages="stages"
            :applications="applications"
        />
    </AppLayout>
</template>
