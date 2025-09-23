<template>
    <div
        :class="[
            'bg-white dark:bg-gray-800 rounded-lg shadow border p-4',
            height,
        ]"
    >
        <h3
            class="text-sm font-bold text-gray-700 dark:text-white mb-3 uppercase tracking-wide"
        >
            {{ title }}
        </h3>

        <!-- cek apakah chartData valid -->
        <div
            v-if="!hasData"
            class="text-center text-sm text-gray-400 dark:text-gray-300 h-full flex items-center justify-center"
        >
            Tidak ada data grafik.
        </div>

        <Line v-else :data="processedData" :options="chartOptions" />
    </div>
</template>

<script setup>
import { Line } from "vue-chartjs";
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    LineElement,
    PointElement,
    CategoryScale,
    LinearScale,
    Filler,
} from "chart.js";
import { computed } from "vue";

ChartJS.register(
    Title,
    Tooltip,
    Legend,
    LineElement,
    PointElement,
    CategoryScale,
    LinearScale,
    Filler
);

const props = defineProps({
    title: String,
    chartData: {
        type: [Array, Object],
        required: true,
    },
    height: {
        type: String,
        default: "h-64",
    },
    useShortNumber: {
        type: Boolean,
        default: false,
    },
});

// formatter
function formatRupiah(value) {
    return (
        "Rp " +
        value.toLocaleString("id-ID", {
            minimumFractionDigits: 0,
        })
    );
}

function formatShortNumber(num) {
    if (num >= 1_000_000) return (num / 1_000_000).toFixed(1) + "M";
    if (num >= 1_000) return (num / 1_000).toFixed(1) + "K";
    return num;
}

// cek apakah ada data
const hasData = computed(() => {
    if (Array.isArray(props.chartData)) {
        return props.chartData.length > 0;
    }
    if (props.chartData && props.chartData.labels) {
        return props.chartData.labels.length > 0;
    }
    return false;
});

// olah chartData jadi format chart.js
const processedData = computed(() => {
    if (Array.isArray(props.chartData)) {
        return {
            labels: props.chartData.map((d) => d.tanggal || d.bulan),
            datasets: [
                {
                    label: "Jumlah Setoran",
                    data: props.chartData.map((d) => d.jumlah),
                    borderColor: "#3b82f6", // blue-500
                    backgroundColor: "rgba(59, 130, 246, 0.15)",
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                },
            ],
        };
    }
    return props.chartData;
});

const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        tooltip: {
            enabled: true,
            callbacks: {
                label: function (context) {
                    const value = context.parsed.y;
                    return props.useShortNumber
                        ? "Rp " + formatShortNumber(value)
                        : formatRupiah(value);
                },
            },
        },
        legend: {
            display: false,
        },
    },
    scales: {
        x: {
            ticks: {
                color: "#666",
            },
        },
        y: {
            ticks: {
                color: "#666",
                callback: function (value) {
                    return props.useShortNumber
                        ? formatShortNumber(value)
                        : value.toLocaleString("id-ID");
                },
            },
        },
    },
}));
</script>
