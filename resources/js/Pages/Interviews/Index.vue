<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import DataTable from '@/Components/UI/DataTable.vue';
import Pagination from '@/Components/UI/Pagination.vue';
import SchedulerModal from '@/Components/Interviews/SchedulerModal.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    interviews: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const showScheduler = ref(false);
const statusFilter = ref(props.filters.status ?? '');
const dateFrom = ref(props.filters.date_from ?? '');
const dateTo = ref(props.filters.date_to ?? '');

const columns = [
    { key: 'candidate', label: 'Candidat' },
    { key: 'job', label: 'Offre d\'emploi', sortable: true },
    { key: 'type', label: 'Type' },
    { key: 'scheduled_at', label: 'Planifié', sortable: true },
    { key: 'status', label: 'Statut' },
    { key: 'interviewers', label: 'Intervieweurs' },
    { key: 'actions', label: '' },
];

const statusOptions = [
    { value: '', label: 'Tous les statuts' },
    { value: 'scheduled', label: 'Planifié' },
    { value: 'in_progress', label: 'En cours' },
    { value: 'completed', label: 'Terminé' },
    { value: 'cancelled', label: 'Annulé' },
];

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

function applyFilters() {
    router.get(
        route('interviews.index'),
        {
            status: statusFilter.value || undefined,
            date_from: dateFrom.value || undefined,
            date_to: dateTo.value || undefined,
        },
        { preserveState: true, replace: true },
    );
}

function clearFilters() {
    statusFilter.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    applyFilters();
}

function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
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

const hasActiveFilters = computed(
    () => statusFilter.value || dateFrom.value || dateTo.value,
);
</script>

<template>
    <AppLayout>
        <!-- Header -->
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Entretiens</h1>
                <p class="mt-1 text-sm text-slate-500">
                    Gérez et suivez tous les entretiens planifiés.
                </p>
            </div>
            <Button variant="primary" @click="showScheduler = true">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Planifier un entretien
            </Button>
        </div>

        <!-- Filters -->
        <div class="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                <div class="flex-1">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Statut</label>
                    <select
                        v-model="statusFilter"
                        class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                        @change="applyFilters"
                    >
                        <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                </div>
                <div class="flex-1">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Date de début</label>
                    <input
                        v-model="dateFrom"
                        type="date"
                        class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                        @change="applyFilters"
                    />
                </div>
                <div class="flex-1">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Date de fin</label>
                    <input
                        v-model="dateTo"
                        type="date"
                        class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                        @change="applyFilters"
                    />
                </div>
                <div class="flex items-end">
                    <button
                        v-if="hasActiveFilters"
                        class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-900"
                        @click="clearFilters"
                    >
                        Effacer
                    </button>
                </div>
            </div>
        </div>

        <!-- Table -->
        <DataTable :columns="columns" :rows="interviews.data" empty-message="Aucun entretien planifié pour le moment.">
            <template #cell-candidate="{ row }">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-xs font-semibold text-indigo-700">
                        {{ getInitials(row.application?.candidate?.name) }}
                    </div>
                    <div>
                        <p class="font-medium text-slate-900">
                            {{ row.application?.candidate?.name ?? '-' }}
                        </p>
                        <p class="text-xs text-slate-500">
                            {{ row.application?.candidate?.email ?? '' }}
                        </p>
                    </div>
                </div>
            </template>

            <template #cell-job="{ row }">
                <span class="text-sm text-slate-700">
                    {{ row.application?.job?.title ?? '-' }}
                </span>
            </template>

            <template #cell-type="{ row }">
                <Badge
                    :label="row.type ? row.type.charAt(0).toUpperCase() + row.type.slice(1) : '-'"
                    :color="typeBadgeColors[row.type] ?? 'bg-slate-100 text-slate-700'"
                />
            </template>

            <template #cell-scheduled_at="{ row }">
                <span class="text-sm text-slate-700">
                    {{ formatDate(row.scheduled_at) }}
                </span>
            </template>

            <template #cell-status="{ row }">
                <Badge
                    :label="row.status ? row.status.replace('_', ' ').replace(/\b\w/g, (c) => c.toUpperCase()) : '-'"
                    :color="statusBadgeColors[row.status] ?? 'bg-slate-100 text-slate-700'"
                />
            </template>

            <template #cell-interviewers="{ row }">
                <div class="flex items-center">
                    <div class="flex -space-x-2">
                        <div
                            v-for="(interviewer, idx) in (row.interviewers ?? []).slice(0, 3)"
                            :key="interviewer.id ?? idx"
                            class="flex h-7 w-7 items-center justify-center rounded-full border-2 border-white bg-slate-200 text-[10px] font-semibold text-slate-600"
                            :title="interviewer.name"
                        >
                            {{ getInitials(interviewer.name) }}
                        </div>
                        <div
                            v-if="(row.interviewers ?? []).length > 3"
                            class="flex h-7 w-7 items-center justify-center rounded-full border-2 border-white bg-slate-100 text-[10px] font-medium text-slate-500"
                        >
                            +{{ row.interviewers.length - 3 }}
                        </div>
                    </div>
                </div>
            </template>

            <template #cell-actions="{ row }">
                <Link
                    :href="route('interviews.show', row.id)"
                    class="text-sm font-medium text-indigo-600 transition-colors hover:text-indigo-800"
                >
                    Voir
                </Link>
            </template>
        </DataTable>

        <!-- Pagination -->
        <div class="mt-4">
            <Pagination :links="interviews.links" />
        </div>

        <!-- Schedule Modal -->
        <SchedulerModal :show="showScheduler" @close="showScheduler = false" />
    </AppLayout>
</template>
