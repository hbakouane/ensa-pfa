<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const company = computed(() => page.props.company ?? {});
</script>

<template>
    <div class="flex min-h-screen flex-col bg-white">
        <!-- Header -->
        <header class="border-b border-slate-200 bg-white">
            <div
                class="mx-auto flex h-16 max-w-5xl items-center justify-between px-4 sm:px-6 lg:px-8"
            >
                <Link href="/" class="flex items-center gap-3">
                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-600 text-sm font-bold text-white"
                    >
                        {{ (company.name ?? 'R').charAt(0).toUpperCase() }}
                    </div>
                    <span class="text-lg font-semibold text-slate-900">
                        {{ company.name ?? 'Careers' }}
                    </span>
                </Link>

                <nav class="flex items-center gap-4">
                    <Link
                        href="/"
                        class="text-sm font-medium text-slate-600 transition-colors hover:text-indigo-600"
                    >
                        Open Positions
                    </Link>
                </nav>
            </div>
        </header>

        <!-- Content -->
        <main class="flex-1">
            <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
                <slot />
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-slate-200 bg-slate-50">
            <div
                class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8"
            >
                <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                    <div class="flex items-center gap-2 text-sm text-slate-500">
                        <span>&copy; {{ new Date().getFullYear() }}</span>
                        <span>{{ company.name ?? 'Company' }}</span>
                        <span class="text-slate-300">|</span>
                        <span>Powered by RecruitAI</span>
                    </div>

                    <div class="flex items-center gap-6">
                        <a
                            href="#"
                            class="text-sm text-slate-500 transition-colors hover:text-slate-700"
                        >
                            Privacy Policy
                        </a>
                        <a
                            href="#"
                            class="text-sm text-slate-500 transition-colors hover:text-slate-700"
                        >
                            Terms of Service
                        </a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
