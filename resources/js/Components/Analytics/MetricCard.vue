<script setup>
const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        required: true,
    },
    change: {
        type: Number,
        default: null,
    },
    subtitle: {
        type: String,
        default: null,
    },
});
</script>

<template>
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <p class="text-sm font-medium text-slate-500">{{ title }}</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ value }}</p>

                <div v-if="change !== null" class="mt-2 flex items-center gap-1">
                    <!-- Positive change -->
                    <span
                        v-if="change > 0"
                        class="inline-flex items-center gap-0.5 text-sm font-medium text-emerald-600"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25" />
                        </svg>
                        {{ Math.abs(change) }}%
                    </span>

                    <!-- Negative change -->
                    <span
                        v-else-if="change < 0"
                        class="inline-flex items-center gap-0.5 text-sm font-medium text-red-600"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 4.5l15 15m0 0V8.25m0 11.25H8.25" />
                        </svg>
                        {{ Math.abs(change) }}%
                    </span>

                    <!-- No change -->
                    <span v-else class="text-sm text-slate-400">0%</span>
                </div>

                <p v-if="subtitle" class="mt-1 text-xs text-slate-400">{{ subtitle }}</p>
            </div>

            <div v-if="$slots.icon" class="ml-3 flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-lg bg-slate-50">
                <slot name="icon" />
            </div>
        </div>
    </div>
</template>
