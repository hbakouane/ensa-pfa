<script setup>
import { computed, onMounted, ref } from 'vue';

const props = defineProps({
    score: {
        type: Number,
        required: true,
        validator: (value) => value >= 0 && value <= 100,
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value),
    },
    showLabel: {
        type: Boolean,
        default: true,
    },
});

const isAnimated = ref(false);

const dimensions = computed(() => {
    const sizes = {
        sm: { width: 48, stroke: 4, fontSize: 14, labelSize: 8 },
        md: { width: 80, stroke: 6, fontSize: 22, labelSize: 11 },
        lg: { width: 120, stroke: 8, fontSize: 32, labelSize: 14 },
    };
    return sizes[props.size];
});

const radius = computed(() => (dimensions.value.width - dimensions.value.stroke) / 2);
const circumference = computed(() => 2 * Math.PI * radius.value);
const strokeDashoffset = computed(() => {
    if (!isAnimated.value) return circumference.value;
    return circumference.value - (props.score / 100) * circumference.value;
});

const scoreColor = computed(() => {
    if (props.score <= 30) return '#ef4444';     // red-500
    if (props.score <= 60) return '#eab308';     // yellow-500
    if (props.score <= 80) return '#22c55e';     // green-500
    return '#4ade80';                             // green-400 (bright green)
});

const trackColor = computed(() => '#e5e7eb'); // gray-200

const label = computed(() => {
    if (props.score <= 30) return 'Low';
    if (props.score <= 60) return 'Fair';
    if (props.score <= 80) return 'Good';
    return 'Excellent';
});

onMounted(() => {
    // Trigger animation on next frame
    requestAnimationFrame(() => {
        isAnimated.value = true;
    });
});
</script>

<template>
    <div class="inline-flex flex-col items-center gap-1">
        <svg
            :width="dimensions.width"
            :height="dimensions.width"
            :viewBox="`0 0 ${dimensions.width} ${dimensions.width}`"
        >
            <!-- Background track -->
            <circle
                :cx="dimensions.width / 2"
                :cy="dimensions.width / 2"
                :r="radius"
                fill="none"
                :stroke="trackColor"
                :stroke-width="dimensions.stroke"
            />
            <!-- Score arc -->
            <circle
                :cx="dimensions.width / 2"
                :cy="dimensions.width / 2"
                :r="radius"
                fill="none"
                :stroke="scoreColor"
                :stroke-width="dimensions.stroke"
                stroke-linecap="round"
                :stroke-dasharray="circumference"
                :stroke-dashoffset="strokeDashoffset"
                transform="rotate(-90)"
                :transform-origin="`${dimensions.width / 2} ${dimensions.width / 2}`"
                class="score-circle"
            />
            <!-- Score number -->
            <text
                :x="dimensions.width / 2"
                :y="dimensions.width / 2"
                text-anchor="middle"
                dominant-baseline="central"
                :font-size="dimensions.fontSize"
                font-weight="bold"
                :fill="scoreColor"
            >
                {{ score }}
            </text>
        </svg>
        <span
            v-if="showLabel"
            class="font-medium"
            :style="{ fontSize: dimensions.labelSize + 'px', color: scoreColor }"
        >
            {{ label }}
        </span>
    </div>
</template>

<style scoped>
.score-circle {
    transition: stroke-dashoffset 1s ease-in-out;
}
</style>
