<template>
    <canvas ref="canvas"></canvas>
</template>

<script setup>
import { onMounted, ref, watch } from "vue";
import { Chart } from "chart.js/auto";

const props = defineProps({ data: Object });
const canvas = ref(null);
let chart = null;

onMounted(() => {
    chart = new Chart(canvas.value, {
        type: "line",
        data: props.data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
        },
    });
});

watch(
    () => props.data,
    (newData) => {
        if (chart) {
            chart.data = newData;
            chart.update();
        }
    }
);
</script>

<style scoped>
canvas {
    width: 100% !important;
    height: 300px !important;
}
</style>
