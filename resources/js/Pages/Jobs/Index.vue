<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import Pagination from '@/Components/UI/Pagination.vue';
import Select from '@/Components/UI/Select.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    jobs: Object, // paginated
    filters: Object,
});

const statusFilter = ref(props.filters?.status ?? '');
const departmentFilter = ref(props.filters?.department ?? '');

const statusOptions = [
    { value: '', label: 'Tous les statuts' },
    { value: 'draft', label: 'Brouillon' },
    { value: 'published', label: 'Publié' },
    { value: 'closed', label: 'Clôturé' },
    { value: 'archived', label: 'Archivé' },
];

const departmentOptions = [
    { value: '', label: 'Tous les départements' },
    ...(props.filters?.departments ?? []).map((d) => ({
        value: d.id,
        label: d.name,
    })),
];

const statusBadge = {
    draft: 'bg-slate-100 text-slate-700',
    published: 'bg-emerald-100 text-emerald-700',
    closed: 'bg-amber-100 text-amber-700',
    archived: 'bg-red-100 text-red-700',
};

watch([statusFilter, departmentFilter], () => {
    router.get(
        route('jobs.index'),
        {
            status: statusFilter.value || undefined,
            department: departmentFilter.value || undefined,
        },
        { preserveState: true, replace: true },
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
</script>

<template>
    <AppLayout>
        <!-- Header -->
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Offres d'emploi</h1>
                <p class="mt-1 text-sm text-slate-500">
                    Gérez vos offres d'emploi et suivez les candidatures.
                </p>
            </div>

            <Link :href="route('jobs.create')">
                <Button variant="primary">
                    <svg
                        class="h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 4.5v15m7.5-7.5h-15"
                        />
                    </svg>
                    Créer une offre
                </Button>
            </Link>
        </div>

        <!-- Filters -->
        <div class="mb-6 flex flex-col gap-3 sm:flex-row">
            <div class="w-full sm:w-48">
                <Select
                    v-model="statusFilter"
                    :options="statusOptions"
                    placeholder="Tous les statuts"
                />
            </div>
            <div class="w-full sm:w-48">
                <Select
                    v-model="departmentFilter"
                    :options="departmentOptions"
                    placeholder="Tous les départements"
                />
            </div>
        </div>

        <!-- Jobs list -->
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Titre
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Département
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Lieu
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Statut
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Candidatures
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Créé le
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr
                            v-for="job in jobs.data"
                            :key="job.id"
                            class="transition-colors hover:bg-slate-50"
                        >
                            <td class="px-6 py-4">
                                <Link
                                    :href="route('jobs.show', job.id)"
                                    class="text-sm font-medium text-slate-900 hover:text-indigo-600"
                                >
                                    {{ job.title }}
                                </Link>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                {{ job.department?.name ?? '-' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                {{ job.location?.name ?? '-' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <Badge
                                    :label="job.status"
                                    :color="statusBadge[job.status] ?? 'bg-slate-100 text-slate-700'"
                                />
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                {{ job.applications_count ?? 0 }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">
                                {{ formatDate(job.created_at) }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Link
                                        :href="route('jobs.show', job.id)"
                                        class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-700"
                                        title="Voir"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </Link>
                                    <Link
                                        :href="route('jobs.edit', job.id)"
                                        class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-700"
                                        title="Modifier"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                        </svg>
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty state -->
            <div
                v-if="!jobs.data?.length"
                class="flex flex-col items-center justify-center py-16"
            >
                <svg class="h-12 w-12 text-slate-300" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m8 0H8m8 0h2a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2h2" />
                </svg>
                <p class="mt-3 text-sm font-medium text-slate-900">Aucune offre trouvée</p>
                <p class="mt-1 text-sm text-slate-500">Commencez par créer votre première offre d'emploi.</p>
                <Link :href="route('jobs.create')" class="mt-4">
                    <Button variant="primary" size="sm">Créer une offre</Button>
                </Link>
            </div>
        </div>

        <!-- Pagination -->
        <Pagination v-if="jobs.links" :links="jobs.links" />
    </AppLayout>
</template>
