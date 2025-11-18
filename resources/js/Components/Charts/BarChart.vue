<template>
    <div class="chart-container">
        <canvas ref="chartCanvas"></canvas>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, onBeforeUnmount } from 'vue';
import {
    Chart,
    BarController,
    BarElement,
    LinearScale,
    CategoryScale,
    Title,
    Tooltip,
    Legend
} from 'chart.js';

// Register Chart.js components
Chart.register(
    BarController,
    BarElement,
    LinearScale,
    CategoryScale,
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
            cornerRadius: 8
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            grid: {
                color: 'rgba(0, 0, 0, 0.05)',
                drawBorder: false
            },
            ticks: {
                font: {
                    size: 11
                },
                color: '#6b7280'
            }
        },
        x: {
            grid: {
                display: false,
                drawBorder: false
            },
            ticks: {
                font: {
                    size: 11
                },
                color: '#6b7280'
            }
        }
    }
};

const createChart = () => {
    if (chartCanvas.value) {
        const ctx = chartCanvas.value.getContext('2d');

        chartInstance = new Chart(ctx, {
            type: 'bar',
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
