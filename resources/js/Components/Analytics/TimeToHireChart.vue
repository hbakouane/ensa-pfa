<script setup>
import { computed } from 'vue';
import { Line } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler,
} from 'chart.js';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler,
);

const props = defineProps({
    data: {
        type: Array,
        required: true,
        default: () => [],
    },
});

const chartData = computed(() => ({
    labels: props.data.map((d) => d.period),
    datasets: [
        {
            label: 'Days to Hire',
            data: props.data.map((d) => d.days),
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99, 102, 241, 0.1)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#6366f1',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7,
        },
    ],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false,
        },
        tooltip: {
            backgroundColor: '#1e293b',
            titleColor: '#f8fafc',
            bodyColor: '#f8fafc',
            padding: 12,
            cornerRadius: 8,
            callbacks: {
                label: (context) => `${context.parsed.y} days`,
            },
        },
    },
    scales: {
        x: {
            grid: {
                display: false,
            },
            ticks: {
                color: '#94a3b8',
                font: { size: 12 },
            },
        },
        y: {
            beginAtZero: true,
            grid: {
                color: '#f1f5f9',
            },
            ticks: {
                color: '#94a3b8',
                font: { size: 12 },
                callback: (value) => `${value}d`,
            },
        },
    },
};
</script>

<template>
    <div v-if="data.length === 0" class="flex h-64 items-center justify-center text-sm text-slate-400">
        No time-to-hire data available yet.
    </div>
    <div v-else class="h-64">
        <Line :data="chartData" :options="chartOptions" />
    </div>
</template>
