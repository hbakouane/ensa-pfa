<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    subjectType: {
        type: String,
        required: true,
    },
    subjectId: {
        type: [String, Number],
        required: true,
    },
});

const activities = ref([]);
const loading = ref(true);
const error = ref(null);

const activityIcons = {
    created: {
        path: 'M12 4v16m8-8H4',
        bg: 'bg-emerald-100',
        color: 'text-emerald-600',
    },
    updated: {
        path: 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
        bg: 'bg-blue-100',
        color: 'text-blue-600',
    },
    deleted: {
        path: 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
        bg: 'bg-red-100',
        color: 'text-red-600',
    },
    stage_changed: {
        path: 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
        bg: 'bg-indigo-100',
        color: 'text-indigo-600',
    },
    comment_added: {
        path: 'M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z',
        bg: 'bg-violet-100',
        color: 'text-violet-600',
    },
    interview_scheduled: {
        path: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        bg: 'bg-amber-100',
        color: 'text-amber-600',
    },
    offer_sent: {
        path: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        bg: 'bg-sky-100',
        color: 'text-sky-600',
    },
    default: {
        path: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        bg: 'bg-slate-100',
        color: 'text-slate-600',
    },
};

function getIcon(type) {
    return activityIcons[type] ?? activityIcons.default;
}

function formatTimeAgo(dateString) {
    if (!dateString) return '';
    const now = new Date();
    const date = new Date(dateString);
    const diffMs = now - date;
    const diffSecs = Math.floor(diffMs / 1000);
    const diffMins = Math.floor(diffSecs / 60);
    const diffHours = Math.floor(diffMins / 60);
    const diffDays = Math.floor(diffHours / 24);

    if (diffSecs < 60) return 'à l\'instant';
    if (diffMins < 60) return `il y a ${diffMins}m`;
    if (diffHours < 24) return `il y a ${diffHours}h`;
    if (diffDays < 7) return `il y a ${diffDays}j`;

    return date.toLocaleDateString('fr-FR', {
        month: 'short',
        day: 'numeric',
        year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined,
    });
}

async function fetchActivities() {
    loading.value = true;
    error.value = null;

    try {
        const response = await axios.get(route('activities.index'), {
            params: {
                subject_type: props.subjectType,
                subject_id: props.subjectId,
            },
        });
        activities.value = response.data.data ?? response.data ?? [];
    } catch (err) {
        error.value = 'Impossible de charger les activités.';
        console.error('ActivityFeed: Error fetching activities', err);
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    fetchActivities();
});
</script>

<template>
    <div>
        <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">
            Activité
        </h3>

        <!-- Loading -->
        <div v-if="loading" class="mt-4 flex items-center justify-center py-8">
            <svg class="h-5 w-5 animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
            <span class="ml-2 text-sm text-slate-500">Chargement des activités...</span>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-center">
            <p class="text-sm text-red-600">{{ error }}</p>
            <button
                class="mt-2 text-xs font-medium text-red-700 underline hover:no-underline"
                @click="fetchActivities"
            >
                Réessayer
            </button>
        </div>

        <!-- Timeline -->
        <div v-else-if="activities.length > 0" class="mt-4">
            <div class="relative">
                <!-- Vertical line -->
                <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-slate-100" />

                <div class="space-y-4">
                    <div
                        v-for="activity in activities"
                        :key="activity.id"
                        class="relative flex items-start gap-4 pl-0"
                    >
                        <!-- Icon -->
                        <div
                            class="relative z-10 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full"
                            :class="getIcon(activity.type).bg"
                        >
                            <svg
                                class="h-4 w-4"
                                :class="getIcon(activity.type).color"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.5"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    :d="getIcon(activity.type).path"
                                />
                            </svg>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0 pb-4">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-slate-900">
                                    {{ activity.user?.name ?? 'Système' }}
                                </span>
                                <span class="text-xs text-slate-400">
                                    {{ formatTimeAgo(activity.created_at) }}
                                </span>
                            </div>
                            <p class="mt-0.5 text-sm text-slate-600">
                                {{ activity.description }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty state -->
        <div v-else class="mt-4 flex flex-col items-center py-6 text-center">
            <svg class="h-8 w-8 text-slate-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="mt-2 text-sm text-slate-400">Aucune activité enregistrée pour le moment.</p>
        </div>
    </div>
</template>
