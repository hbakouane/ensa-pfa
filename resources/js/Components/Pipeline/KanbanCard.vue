<script setup>
import { Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    application: Object,
});

const emit = defineEmits(['reject']);

const showMenu = ref(false);

const candidateName = computed(() => {
    const c = props.application.candidate;
    if (!c) return 'Inconnu';
    return `${c.first_name ?? ''} ${c.last_name ?? ''}`.trim() || 'Inconnu';
});

const candidateInitials = computed(() => {
    const c = props.application.candidate;
    if (!c) return '?';
    const first = (c.first_name ?? '')[0] ?? '';
    const last = (c.last_name ?? '')[0] ?? '';
    return (first + last).toUpperCase() || '?';
});

const scoreColor = computed(() => {
    const score = props.application.score;
    if (score == null) return '';
    if (score >= 80) return 'bg-emerald-100 text-emerald-700';
    if (score >= 50) return 'bg-amber-100 text-amber-700';
    return 'bg-red-100 text-red-700';
});

function formatDate(dateStr) {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('fr-FR', {
        month: 'short',
        day: 'numeric',
    });
}

function handleReject() {
    showMenu.value = false;
    emit('reject', props.application.id);
}

function closeMenu() {
    setTimeout(() => {
        showMenu.value = false;
    }, 150);
}
</script>

<template>
    <div class="group cursor-grab rounded-lg border border-slate-200 bg-white p-3 shadow-sm transition-shadow hover:shadow-md active:cursor-grabbing">
        <div class="flex items-start justify-between">
            <div class="flex items-center gap-2.5">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-semibold text-indigo-700">
                    {{ candidateInitials }}
                </div>
                <div class="min-w-0">
                    <Link
                        v-if="application.candidate"
                        :href="route('candidates.show', application.candidate.id)"
                        class="block truncate text-sm font-medium text-slate-900 hover:text-indigo-600"
                        @click.stop
                    >
                        {{ candidateName }}
                    </Link>
                    <p v-else class="truncate text-sm font-medium text-slate-900">
                        {{ candidateName }}
                    </p>
                </div>
            </div>

            <!-- Action menu -->
            <div class="relative">
                <button
                    class="rounded p-1 text-slate-400 opacity-0 transition-all hover:bg-slate-100 hover:text-slate-600 group-hover:opacity-100"
                    @click.stop="showMenu = !showMenu"
                    @blur="closeMenu"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 12.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 18.75a.75.75 0 110-1.5.75.75 0 010 1.5z" />
                    </svg>
                </button>

                <Transition
                    enter-active-class="transition duration-100 ease-out"
                    enter-from-class="scale-95 opacity-0"
                    enter-to-class="scale-100 opacity-100"
                    leave-active-class="transition duration-75 ease-in"
                    leave-from-class="scale-100 opacity-100"
                    leave-to-class="scale-95 opacity-0"
                >
                    <div
                        v-if="showMenu"
                        class="absolute right-0 z-10 mt-1 w-36 rounded-lg border border-slate-200 bg-white py-1 shadow-lg"
                    >
                        <Link
                            v-if="application.candidate"
                            :href="route('candidates.show', application.candidate.id)"
                            class="flex w-full items-center gap-2 px-3 py-1.5 text-left text-xs text-slate-700 hover:bg-slate-50"
                            @click.stop
                        >
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                            Voir le profil
                        </Link>
                        <button
                            class="flex w-full items-center gap-2 px-3 py-1.5 text-left text-xs text-red-600 hover:bg-red-50"
                            @click.stop="handleReject"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                            Rejeter
                        </button>
                    </div>
                </Transition>
            </div>
        </div>

        <!-- Meta row -->
        <div class="mt-2.5 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span
                    v-if="application.score != null"
                    :class="[
                        'inline-flex items-center rounded-full px-1.5 py-0.5 text-[10px] font-semibold',
                        scoreColor,
                    ]"
                >
                    {{ application.score }}
                </span>
                <span
                    v-if="application.source"
                    class="text-[10px] font-medium uppercase tracking-wide text-slate-400"
                >
                    {{ application.source }}
                </span>
            </div>
            <span class="text-[10px] text-slate-400">
                {{ formatDate(application.created_at) }}
            </span>
        </div>
    </div>
</template>
