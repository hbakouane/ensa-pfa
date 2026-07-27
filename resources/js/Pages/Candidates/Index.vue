<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import Pagination from '@/Components/UI/Pagination.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    candidates: Object, // paginated
    filters: Object,
});

const search = ref(props.filters?.search ?? '');
let searchTimeout = null;

watch(search, (val) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            route('candidates.index'),
            { search: val || undefined },
            { preserveState: true, replace: true },
        );
    }, 300);
});

function initials(candidate) {
    const first = (candidate.first_name ?? '')[0] ?? '';
    const last = (candidate.last_name ?? '')[0] ?? '';
    return (first + last).toUpperCase() || '?';
}

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
                <h1 class="text-2xl font-bold text-slate-900">Candidats</h1>
                <p class="mt-1 text-sm text-slate-500">
                    Consultez et gérez votre vivier de candidats.
                </p>
            </div>

            <Link :href="route('candidates.create')">
                <Button variant="primary">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Ajouter un candidat
                </Button>
            </Link>
        </div>

        <!-- Search -->
        <div class="mb-6">
            <div class="relative w-full max-w-sm">
                <svg
                    class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input
                    v-model="search"
                    type="text"
                    placeholder="Rechercher des candidats..."
                    class="block w-full rounded-lg border border-slate-300 bg-white py-2 pl-10 pr-4 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                />
            </div>
        </div>

        <!-- Candidates table -->
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Candidat</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">E-mail</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Titre</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Tags</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Candidatures</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr
                            v-for="candidate in candidates.data"
                            :key="candidate.id"
                            class="transition-colors hover:bg-slate-50"
                        >
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-700">
                                        {{ initials(candidate) }}
                                    </div>
                                    <Link
                                        :href="route('candidates.show', candidate.id)"
                                        class="text-sm font-medium text-slate-900 hover:text-indigo-600"
                                    >
                                        {{ candidate.first_name }} {{ candidate.last_name }}
                                    </Link>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                {{ candidate.email }}
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                <span class="line-clamp-1">{{ candidate.headline ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    <Badge
                                        v-for="tag in (candidate.tags ?? []).slice(0, 3)"
                                        :key="tag"
                                        :label="typeof tag === 'string' ? tag : tag.name"
                                        color="bg-slate-100 text-slate-600"
                                    />
                                    <span
                                        v-if="(candidate.tags ?? []).length > 3"
                                        class="text-xs text-slate-400"
                                    >
                                        +{{ candidate.tags.length - 3 }}
                                    </span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                {{ candidate.applications_count ?? 0 }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right">
                                <Link
                                    :href="route('candidates.show', candidate.id)"
                                    class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-700"
                                    title="Voir"
                                >
                                    <svg class="inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty state -->
            <div
                v-if="!candidates.data?.length"
                class="flex flex-col items-center justify-center py-16"
            >
                <svg class="h-12 w-12 text-slate-300" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <p class="mt-3 text-sm font-medium text-slate-900">Aucun candidat trouvé</p>
                <p class="mt-1 text-sm text-slate-500">Ajoutez des candidats manuellement ou attendez les candidatures.</p>
                <Link :href="route('candidates.create')" class="mt-4">
                    <Button variant="primary" size="sm">Ajouter un candidat</Button>
                </Link>
            </div>
        </div>

        <!-- Pagination -->
        <Pagination v-if="candidates.links" :links="candidates.links" />
    </AppLayout>
</template>
