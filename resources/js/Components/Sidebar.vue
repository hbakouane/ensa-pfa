<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    collapsed: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['toggle']);

const page = usePage();
const company = computed(() => page.props.auth?.company ?? {});

const navItems = [
    {
        label: 'Dashboard',
        routeName: 'dashboard',
        href: route('dashboard'),
        icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z',
    },
    {
        label: 'Jobs',
        routeName: 'jobs.*',
        href: route('dashboard'),
        icon: 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m8 0H8m8 0h2a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2h2',
    },
    {
        label: 'Candidates',
        routeName: 'candidates.*',
        href: route('dashboard'),
        icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
    },
    {
        label: 'Pipeline',
        routeName: 'pipeline.*',
        href: route('dashboard'),
        icon: 'M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2',
    },
    {
        label: 'Interviews',
        routeName: 'interviews.*',
        href: route('dashboard'),
        icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
    },
    {
        label: 'Offers',
        routeName: 'offers.*',
        href: route('dashboard'),
        icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    },
    {
        label: 'Analytics',
        routeName: 'analytics.*',
        href: route('dashboard'),
        icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    },
    {
        label: 'Settings',
        routeName: 'settings.*',
        href: route('dashboard'),
        icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
    },
];

function isActive(routeName) {
    return route().current(routeName);
}
</script>

<template>
    <aside
        :class="[
            'flex h-screen flex-col bg-slate-900 text-slate-300 transition-all duration-300',
            collapsed ? 'w-16' : 'w-64',
        ]"
    >
        <!-- Company branding -->
        <div class="flex h-16 items-center border-b border-slate-700/50 px-4">
            <div class="flex items-center gap-3 overflow-hidden">
                <div
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-600 text-sm font-bold text-white"
                >
                    {{ (company.name ?? 'R').charAt(0).toUpperCase() }}
                </div>
                <Transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <span
                        v-if="!collapsed"
                        class="truncate text-sm font-semibold text-white"
                    >
                        {{ company.name ?? 'RecruitAI' }}
                    </span>
                </Transition>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 space-y-1 overflow-y-auto px-2 py-4">
            <Link
                v-for="item in navItems"
                :key="item.label"
                :href="item.href"
                :class="[
                    'group flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-colors',
                    isActive(item.routeName)
                        ? 'bg-indigo-600/20 text-indigo-400'
                        : 'text-slate-400 hover:bg-slate-800 hover:text-white',
                ]"
                :title="collapsed ? item.label : undefined"
            >
                <svg
                    :class="[
                        'h-5 w-5 shrink-0',
                        isActive(item.routeName)
                            ? 'text-indigo-400'
                            : 'text-slate-500 group-hover:text-slate-300',
                    ]"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        :d="item.icon"
                    />
                </svg>

                <Transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <span v-if="!collapsed" class="ml-3 truncate">
                        {{ item.label }}
                    </span>
                </Transition>
            </Link>
        </nav>

        <!-- Collapse toggle -->
        <div class="border-t border-slate-700/50 p-2">
            <button
                class="flex w-full items-center justify-center rounded-lg p-2 text-slate-400 transition-colors hover:bg-slate-800 hover:text-white"
                @click="$emit('toggle')"
            >
                <svg
                    class="h-5 w-5 transition-transform"
                    :class="{ 'rotate-180': collapsed }"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M15.75 19.5L8.25 12l7.5-7.5"
                    />
                </svg>
            </button>
        </div>
    </aside>
</template>
