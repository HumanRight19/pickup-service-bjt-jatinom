<template>
    <div class="bg-white shadow rounded-xl p-6 hover:shadow-lg transition">
        <h2 class="text-lg font-semibold mb-2">{{ title }}</h2>
        <router-link :to="link" class="text-blue-500 hover:underline">
            Lihat Detail →
        </router-link>
    </div>
</template>

<script setup>
defineProps({
    title: String,
    link: String,
});
</script>
