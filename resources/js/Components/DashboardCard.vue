<template>
    <a
        :href="link"
        class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-xl p-6 shadow flex items-center justify-between transition"
    >
        <div>
            <h4 class="text-lg font-semibold">{{ title }}</h4>
            <p class="text-sm opacity-90">Klik untuk masuk</p>
        </div>
        <div class="text-3xl">➡️</div>
    </a>
</template>

<script setup>
defineProps({
    title: String,
    link: String,
});
</script>
