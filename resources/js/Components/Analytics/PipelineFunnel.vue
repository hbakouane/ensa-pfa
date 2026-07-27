<script setup>
import { computed } from 'vue';

const props = defineProps({
    stages: {
        type: Array,
        required: true,
        default: () => [],
    },
});

const maxCount = computed(() => {
    if (props.stages.length === 0) return 1;
    return Math.max(...props.stages.map((s) => s.count), 1);
});

const colors = [
    'bg-indigo-500',
    'bg-blue-500',
    'bg-cyan-500',
    'bg-teal-500',
    'bg-emerald-500',
    'bg-green-500',
    'bg-lime-500',
    'bg-amber-500',
];

function getColor(index) {
    return colors[index % colors.length];
}

function getBarWidth(count) {
    return Math.max((count / maxCount.value) * 100, 8);
}
</script>

<template>
    <div v-if="stages.length === 0" class="flex h-48 items-center justify-center text-sm text-slate-400">
        No pipeline data available yet.
    </div>

    <div v-else class="space-y-3">
        <div
            v-for="(stage, index) in stages"
            :key="stage.name"
            class="group"
        >
            <div class="mb-1 flex items-center justify-between text-sm">
                <span class="font-medium text-slate-700">{{ stage.name }}</span>
                <div class="flex items-center gap-3">
                    <span class="text-slate-500">{{ stage.count }} candidates</span>
                    <span
                        v-if="index > 0"
                        class="text-xs font-medium"
                        :class="stage.conversion_rate >= 50 ? 'text-emerald-600' : 'text-amber-600'"
                    >
                        {{ stage.conversion_rate }}% conversion
                    </span>
                </div>
            </div>

            <div class="h-8 w-full overflow-hidden rounded-lg bg-slate-100">
                <div
                    :class="getColor(index)"
                    class="flex h-full items-center rounded-lg px-3 transition-all duration-500"
                    :style="{ width: getBarWidth(stage.count) + '%' }"
                >
                    <span
                        v-if="getBarWidth(stage.count) > 20"
                        class="text-xs font-semibold text-white"
                    >
                        {{ stage.count }}
                    </span>
                </div>
            </div>

            <!-- Drop-off indicator -->
            <div
                v-if="index > 0 && stage.drop_off_rate > 0"
                class="mt-0.5 text-right text-xs text-red-400"
            >
                {{ stage.drop_off_rate }}% drop-off
            </div>
        </div>
    </div>
</template>
