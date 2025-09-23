<!-- resources/js/Components/StatCard.vue -->
<template>
    <div class="bg-white shadow rounded p-4">
        <p class="text-sm text-gray-500">{{ title }}</p>
        <p class="text-xl font-bold">{{ value }}</p>
    </div>
</template>

<script setup>
defineProps({
    title: String,
    value: [String, Number],
});
</script>
