<script setup>
import { computed } from 'vue';
import { Doughnut } from 'vue-chartjs';
import {
    Chart as ChartJS,
    ArcElement,
    Tooltip,
    Legend,
} from 'chart.js';

ChartJS.register(ArcElement, Tooltip, Legend);

const props = defineProps({
    data: {
        type: Array,
        required: true,
        default: () => [],
    },
});

const colorPalette = [
    '#6366f1', // indigo
    '#06b6d4', // cyan
    '#f59e0b', // amber
    '#10b981', // emerald
    '#ef4444', // red
    '#8b5cf6', // violet
    '#f97316', // orange
    '#14b8a6', // teal
    '#ec4899', // pink
    '#64748b', // slate
];

const chartData = computed(() => ({
    labels: props.data.map((d) => d.source),
    datasets: [
        {
            data: props.data.map((d) => d.count),
            backgroundColor: props.data.map((_, i) => colorPalette[i % colorPalette.length]),
            borderColor: '#ffffff',
            borderWidth: 3,
            hoverOffset: 8,
        },
    ],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '60%',
    plugins: {
        legend: {
            position: 'right',
            labels: {
                padding: 16,
                usePointStyle: true,
                pointStyle: 'circle',
                font: { size: 12 },
                color: '#475569',
            },
        },
        tooltip: {
            backgroundColor: '#1e293b',
            titleColor: '#f8fafc',
            bodyColor: '#f8fafc',
            padding: 12,
            cornerRadius: 8,
            callbacks: {
                label: (context) => {
                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                    return ` ${context.label}: ${context.parsed} (${percentage}%)`;
                },
            },
        },
    },
};

const total = computed(() => props.data.reduce((sum, d) => sum + d.count, 0));
</script>

<template>
    <div v-if="data.length === 0" class="flex h-64 items-center justify-center text-sm text-slate-400">
        Aucune donnée de suivi des sources disponible pour le moment.
    </div>
    <div v-else class="flex items-center gap-8">
        <div class="relative h-72 w-full max-w-md">
            <Doughnut :data="chartData" :options="chartOptions" />
        </div>
        <div class="hidden flex-shrink-0 space-y-2 lg:block">
            <p class="text-sm font-medium text-slate-500">Total suivi</p>
            <p class="text-3xl font-bold text-slate-900">{{ total }}</p>
        </div>
    </div>
</template>
