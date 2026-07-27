<script setup>
import { computed } from 'vue';

const props = defineProps({
    label: {
        type: String,
        required: true,
    },
    current: {
        type: Number,
        required: true,
    },
    max: {
        type: Number,
        required: true,
    },
});

const isUnlimited = computed(() => props.max === -1);

const percentage = computed(() => {
    if (isUnlimited.value) return 0;
    if (props.max === 0) return 100;
    return Math.min((props.current / props.max) * 100, 100);
});

const barColor = computed(() => {
    if (isUnlimited.value) return 'bg-indigo-500';
    if (percentage.value >= 80) return 'bg-red-500';
    if (percentage.value >= 50) return 'bg-amber-500';
    return 'bg-emerald-500';
});

const maxLabel = computed(() => {
    return isUnlimited.value ? 'Unlimited' : props.max.toLocaleString();
});
</script>

<template>
    <div>
        <div class="mb-1.5 flex items-center justify-between">
            <span class="text-sm font-medium text-slate-700">{{ label }}</span>
            <span class="text-sm text-slate-500">
                {{ current.toLocaleString() }} / {{ maxLabel }}
            </span>
        </div>

        <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-100">
            <div
                v-if="isUnlimited"
                class="h-full w-full rounded-full bg-indigo-200"
            />
            <div
                v-else
                :class="barColor"
                class="h-full rounded-full transition-all duration-500"
                :style="{ width: percentage + '%' }"
            />
        </div>

        <p
            v-if="!isUnlimited && percentage >= 80"
            class="mt-1 text-xs text-red-500"
        >
            {{ percentage >= 100 ? 'Limit reached' : 'Approaching limit' }} - consider upgrading your plan.
        </p>
    </div>
</template>
