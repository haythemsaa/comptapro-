<template>
    <div class="chart-container">
        <canvas ref="chartCanvas"></canvas>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, onBeforeUnmount } from 'vue';
import {
    Chart,
    DoughnutController,
    ArcElement,
    Title,
    Tooltip,
    Legend
} from 'chart.js';

// Register Chart.js components
Chart.register(
    DoughnutController,
    ArcElement,
    Title,
    Tooltip,
    Legend
);

const props = defineProps({
    data: {
        type: Object,
        required: true
    },
    options: {
        type: Object,
        default: () => ({})
    }
});

const chartCanvas = ref(null);
let chartInstance = null;

const defaultOptions = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '70%',
    plugins: {
        legend: {
            display: true,
            position: 'bottom',
            labels: {
                usePointStyle: true,
                padding: 15,
                font: {
                    size: 12,
                    family: "'Inter', sans-serif"
                }
            }
        },
        tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            padding: 12,
            titleFont: {
                size: 14,
                weight: 'bold'
            },
            bodyFont: {
                size: 13
            },
            borderColor: 'rgba(255, 255, 255, 0.1)',
            borderWidth: 1,
            cornerRadius: 8,
            callbacks: {
                label: function(context) {
                    const label = context.label || '';
                    const value = context.parsed || 0;
                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                    const percentage = ((value / total) * 100).toFixed(1);
                    return `${label}: ${value} (${percentage}%)`;
                }
            }
        }
    }
};

const createChart = () => {
    if (chartCanvas.value) {
        const ctx = chartCanvas.value.getContext('2d');

        chartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: props.data,
            options: {
                ...defaultOptions,
                ...props.options
            }
        });
    }
};

const updateChart = () => {
    if (chartInstance) {
        chartInstance.data = props.data;
        chartInstance.options = {
            ...defaultOptions,
            ...props.options
        };
        chartInstance.update('active');
    }
};

onMounted(() => {
    createChart();
});

watch(() => props.data, () => {
    updateChart();
}, { deep: true });

watch(() => props.options, () => {
    updateChart();
}, { deep: true });

onBeforeUnmount(() => {
    if (chartInstance) {
        chartInstance.destroy();
    }
});

// Expose canvas and chart for external use (export, etc.)
const getCanvas = () => chartCanvas.value;
const getChart = () => chartInstance;

defineExpose({
    getCanvas,
    getChart
});
</script>

<style scoped>
.chart-container {
    position: relative;
    width: 100%;
    height: 100%;
}
</style>
