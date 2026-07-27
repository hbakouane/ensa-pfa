<script setup>
import { ref, watch, onMounted, onUnmounted, nextTick, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';

const isOpen = ref(false);
const query = ref('');
const results = ref({ jobs: [], candidates: [] });
const loading = ref(false);
const selectedIndex = ref(0);
const searchInput = ref(null);

const allResults = computed(() => {
    const items = [];

    if (results.value.jobs.length) {
        items.push({ type: 'header', label: 'Offres d\'emploi' });
        results.value.jobs.forEach(job => {
            items.push({ type: 'job', ...job });
        });
    }

    if (results.value.candidates.length) {
        items.push({ type: 'header', label: 'Candidats' });
        results.value.candidates.forEach(candidate => {
            items.push({ type: 'candidate', ...candidate });
        });
    }

    return items;
});

const selectableResults = computed(() => {
    return allResults.value.filter(item => item.type !== 'header');
});

const hasResults = computed(() => {
    return results.value.jobs.length > 0 || results.value.candidates.length > 0;
});

function open() {
    isOpen.value = true;
    query.value = '';
    results.value = { jobs: [], candidates: [] };
    selectedIndex.value = 0;

    nextTick(() => {
        searchInput.value?.focus();
    });
}

function close() {
    isOpen.value = false;
    query.value = '';
    results.value = { jobs: [], candidates: [] };
}

function navigateToResult(item) {
    if (item.url) {
        close();
        router.visit(item.url);
    }
}

function handleArrowDown() {
    if (selectedIndex.value < selectableResults.value.length - 1) {
        selectedIndex.value++;
    }
}

function handleArrowUp() {
    if (selectedIndex.value > 0) {
        selectedIndex.value--;
    }
}

function handleEnter() {
    const item = selectableResults.value[selectedIndex.value];
    if (item) {
        navigateToResult(item);
    }
}

function getSelectableIndex(item) {
    return selectableResults.value.indexOf(item);
}

const fetchResults = debounce(async (searchQuery) => {
    if (searchQuery.length < 2) {
        results.value = { jobs: [], candidates: [] };
        loading.value = false;
        return;
    }

    loading.value = true;

    try {
        const response = await fetch(`/search?q=${encodeURIComponent(searchQuery)}`);
        const data = await response.json();
        results.value = data;
        selectedIndex.value = 0;
    } catch (error) {
        console.error('Search failed:', error);
    } finally {
        loading.value = false;
    }
}, 300);

watch(query, (newQuery) => {
    fetchResults(newQuery);
});

function handleKeydown(event) {
    if ((event.metaKey || event.ctrlKey) && event.key === 'k') {
        event.preventDefault();
        if (isOpen.value) {
            close();
        } else {
            open();
        }
    }
}

onMounted(() => {
    document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <div>
        <!-- Search trigger button -->
        <button
            @click="open"
            class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-500 shadow-sm transition hover:border-slate-300 hover:text-slate-700"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <span>Rechercher...</span>
            <kbd class="ml-2 hidden rounded border border-slate-200 bg-slate-100 px-1.5 py-0.5 text-xs font-medium text-slate-400 sm:inline-block">
                {{ navigator?.platform?.includes('Mac') ? '&#8984;' : 'Ctrl' }}K
            </kbd>
        </button>

        <!-- Modal overlay -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="isOpen"
                    class="fixed inset-0 z-50 flex items-start justify-center bg-black/50 pt-[15vh]"
                    @click.self="close"
                    @keydown.escape="close"
                >
                    <div class="w-full max-w-lg overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-black/5">
                        <!-- Search input -->
                        <div class="flex items-center border-b border-slate-200 px-4">
                            <svg class="mr-3 h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input
                                ref="searchInput"
                                v-model="query"
                                type="text"
                                placeholder="Rechercher des offres, candidats..."
                                class="h-12 w-full border-0 bg-transparent text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-0"
                                @keydown.down.prevent="handleArrowDown"
                                @keydown.up.prevent="handleArrowUp"
                                @keydown.enter.prevent="handleEnter"
                            />
                            <button @click="close" class="ml-2 rounded p-1 text-slate-400 hover:text-slate-600">
                                <kbd class="rounded border border-slate-200 bg-slate-100 px-1.5 py-0.5 text-xs font-medium text-slate-400">Esc</kbd>
                            </button>
                        </div>

                        <!-- Results -->
                        <div class="max-h-80 overflow-y-auto">
                            <!-- Loading state -->
                            <div v-if="loading" class="flex items-center justify-center px-4 py-8">
                                <svg class="h-5 w-5 animate-spin text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                </svg>
                                <span class="ml-2 text-sm text-slate-500">Recherche en cours...</span>
                            </div>

                            <!-- Empty state -->
                            <div v-else-if="query.length >= 2 && !hasResults" class="px-4 py-8 text-center">
                                <p class="text-sm text-slate-500">Aucun résultat trouvé pour "{{ query }}"</p>
                            </div>

                            <!-- Results list -->
                            <div v-else-if="hasResults">
                                <template v-for="(item, index) in allResults" :key="`${item.type}-${item.id || item.label}`">
                                    <!-- Section header -->
                                    <div v-if="item.type === 'header'" class="px-4 py-2 text-xs font-semibold uppercase tracking-wider text-slate-400">
                                        {{ item.label }}
                                    </div>

                                    <!-- Job result -->
                                    <button
                                        v-else-if="item.type === 'job'"
                                        class="flex w-full items-center gap-3 px-4 py-3 text-left transition"
                                        :class="getSelectableIndex(item) === selectedIndex ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:bg-slate-50'"
                                        @click="navigateToResult(item)"
                                        @mouseenter="selectedIndex = getSelectableIndex(item)"
                                    >
                                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-medium">{{ item.title }}</p>
                                            <p class="truncate text-xs text-slate-500">{{ item.subtitle }}</p>
                                        </div>
                                        <span
                                            v-if="item.status"
                                            class="rounded-full px-2 py-0.5 text-xs font-medium"
                                            :class="{
                                                'bg-green-100 text-green-700': item.status === 'published',
                                                'bg-slate-100 text-slate-600': item.status === 'draft',
                                                'bg-red-100 text-red-700': item.status === 'closed',
                                            }"
                                        >
                                            {{ item.status }}
                                        </span>
                                    </button>

                                    <!-- Candidate result -->
                                    <button
                                        v-else-if="item.type === 'candidate'"
                                        class="flex w-full items-center gap-3 px-4 py-3 text-left transition"
                                        :class="getSelectableIndex(item) === selectedIndex ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:bg-slate-50'"
                                        @click="navigateToResult(item)"
                                        @mouseenter="selectedIndex = getSelectableIndex(item)"
                                    >
                                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-medium">{{ item.name }}</p>
                                            <p class="truncate text-xs text-slate-500">{{ item.subtitle }}</p>
                                        </div>
                                    </button>
                                </template>
                            </div>

                            <!-- Initial state -->
                            <div v-else-if="query.length < 2" class="px-4 py-8 text-center">
                                <p class="text-sm text-slate-500">Commencez à taper pour rechercher parmi les offres et les candidats...</p>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="flex items-center justify-between border-t border-slate-200 bg-slate-50 px-4 py-2">
                            <div class="flex items-center gap-2 text-xs text-slate-400">
                                <kbd class="rounded border border-slate-200 bg-white px-1.5 py-0.5 font-medium">&uarr;</kbd>
                                <kbd class="rounded border border-slate-200 bg-white px-1.5 py-0.5 font-medium">&darr;</kbd>
                                <span>pour naviguer</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-slate-400">
                                <kbd class="rounded border border-slate-200 bg-white px-1.5 py-0.5 font-medium">&crarr;</kbd>
                                <span>pour sélectionner</span>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
