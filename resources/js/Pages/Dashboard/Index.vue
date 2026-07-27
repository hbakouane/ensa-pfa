<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user ?? {});
const company = computed(() => page.props.auth?.company ?? {});

const stats = [
    {
        label: 'Open Jobs',
        value: '12',
        change: '+3 this week',
        changeType: 'positive',
        icon: 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m8 0H8m8 0h2a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2h2',
        color: 'indigo',
    },
    {
        label: 'Active Candidates',
        value: '284',
        change: '+18 this week',
        changeType: 'positive',
        icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
        color: 'emerald',
    },
    {
        label: 'Interviews Today',
        value: '5',
        change: '2 remaining',
        changeType: 'neutral',
        icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        color: 'amber',
    },
    {
        label: 'Avg. Time to Hire',
        value: '24d',
        change: '-3 days vs last month',
        changeType: 'positive',
        icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        color: 'violet',
    },
];

const colorClasses = {
    indigo: {
        bg: 'bg-indigo-50',
        icon: 'text-indigo-600',
        badge: 'text-indigo-700',
    },
    emerald: {
        bg: 'bg-emerald-50',
        icon: 'text-emerald-600',
        badge: 'text-emerald-700',
    },
    amber: {
        bg: 'bg-amber-50',
        icon: 'text-amber-600',
        badge: 'text-amber-700',
    },
    violet: {
        bg: 'bg-violet-50',
        icon: 'text-violet-600',
        badge: 'text-violet-700',
    },
};
</script>

<template>
    <AppLayout>
        <!-- Welcome header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-slate-900">
                Welcome back, {{ user.name?.split(' ')[0] ?? 'there' }}
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Here's what's happening at
                <span class="font-medium text-slate-700">
                    {{ company.name ?? 'your company' }}
                </span>
                today.
            </p>
        </div>

        <!-- Stat cards -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <div
                v-for="stat in stats"
                :key="stat.label"
                class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition-shadow hover:shadow-md"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">
                            {{ stat.label }}
                        </p>
                        <p class="mt-2 text-3xl font-bold text-slate-900">
                            {{ stat.value }}
                        </p>
                    </div>
                    <div
                        :class="[
                            'flex h-10 w-10 items-center justify-center rounded-lg',
                            colorClasses[stat.color].bg,
                        ]"
                    >
                        <svg
                            :class="[
                                'h-5 w-5',
                                colorClasses[stat.color].icon,
                            ]"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                :d="stat.icon"
                            />
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-xs text-slate-500">
                    <span
                        v-if="stat.changeType === 'positive'"
                        class="font-medium text-emerald-600"
                    >
                        {{ stat.change }}
                    </span>
                    <span
                        v-else-if="stat.changeType === 'negative'"
                        class="font-medium text-red-600"
                    >
                        {{ stat.change }}
                    </span>
                    <span v-else class="font-medium text-slate-600">
                        {{ stat.change }}
                    </span>
                </p>
            </div>
        </div>

        <!-- Placeholder sections -->
        <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Recent activity -->
            <div
                class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
            >
                <h2 class="text-lg font-semibold text-slate-900">
                    Recent Activity
                </h2>
                <div
                    class="mt-4 flex h-48 items-center justify-center rounded-lg border-2 border-dashed border-slate-200"
                >
                    <p class="text-sm text-slate-400">
                        Activity feed coming soon
                    </p>
                </div>
            </div>

            <!-- Upcoming interviews -->
            <div
                class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
            >
                <h2 class="text-lg font-semibold text-slate-900">
                    Upcoming Interviews
                </h2>
                <div
                    class="mt-4 flex h-48 items-center justify-center rounded-lg border-2 border-dashed border-slate-200"
                >
                    <p class="text-sm text-slate-400">
                        Interview schedule coming soon
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
