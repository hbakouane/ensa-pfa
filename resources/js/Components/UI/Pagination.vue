<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <nav
        v-if="links.length > 3"
        class="flex items-center justify-between border-t border-slate-200 px-1 pt-4"
    >
        <div class="flex flex-1 justify-between sm:hidden">
            <Link
                v-if="links[0].url"
                :href="links[0].url"
                class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
            >
                Previous
            </Link>
            <span
                v-else
                class="inline-flex items-center rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-medium text-slate-400"
            >
                Previous
            </span>

            <Link
                v-if="links[links.length - 1].url"
                :href="links[links.length - 1].url"
                class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
            >
                Next
            </Link>
            <span
                v-else
                class="inline-flex items-center rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-medium text-slate-400"
            >
                Next
            </span>
        </div>

        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-center">
            <div class="flex items-center gap-1">
                <template v-for="(link, idx) in links" :key="idx">
                    <!-- Previous arrow -->
                    <Link
                        v-if="idx === 0 && link.url"
                        :href="link.url"
                        class="inline-flex items-center rounded-lg p-2 text-sm text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-700"
                        preserve-scroll
                    >
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
                                d="M15.75 19.5L8.25 12l7.5-7.5"
                            />
                        </svg>
                    </Link>
                    <span
                        v-else-if="idx === 0"
                        class="inline-flex items-center rounded-lg p-2 text-sm text-slate-300"
                    >
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
                                d="M15.75 19.5L8.25 12l7.5-7.5"
                            />
                        </svg>
                    </span>

                    <!-- Next arrow -->
                    <Link
                        v-else-if="idx === links.length - 1 && link.url"
                        :href="link.url"
                        class="inline-flex items-center rounded-lg p-2 text-sm text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-700"
                        preserve-scroll
                    >
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
                                d="M8.25 4.5l7.5 7.5-7.5 7.5"
                            />
                        </svg>
                    </Link>
                    <span
                        v-else-if="idx === links.length - 1"
                        class="inline-flex items-center rounded-lg p-2 text-sm text-slate-300"
                    >
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
                                d="M8.25 4.5l7.5 7.5-7.5 7.5"
                            />
                        </svg>
                    </span>

                    <!-- Page numbers -->
                    <Link
                        v-else-if="link.url"
                        :href="link.url"
                        class="inline-flex min-w-[2rem] items-center justify-center rounded-lg px-3 py-1.5 text-sm font-medium transition-colors"
                        :class="
                            link.active
                                ? 'bg-indigo-600 text-white'
                                : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
                        "
                        preserve-scroll
                        v-html="link.label"
                    />
                    <span
                        v-else
                        class="inline-flex min-w-[2rem] items-center justify-center rounded-lg px-3 py-1.5 text-sm font-medium"
                        :class="
                            link.active
                                ? 'bg-indigo-600 text-white'
                                : 'text-slate-400'
                        "
                        v-html="link.label"
                    />
                </template>
            </div>
        </div>
    </nav>
</template>
