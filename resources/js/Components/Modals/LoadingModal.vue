<template>
    <div
        class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50"
    >
        <div
            class="bg-white/80 dark:bg-gray-900/80 backdrop-blur-md p-6 rounded-3xl w-72 text-center shadow-lg border border-white/20"
        >
            <!-- Spinner -->
            <div class="flex justify-center mb-4">
                <div
                    class="animate-spin rounded-full h-12 w-12 border-t-4 border-b-4 border-gray-200 dark:border-gray-700"
                ></div>
            </div>

            <!-- Title -->
            <h2
                class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-100"
            >
                Memproses...
            </h2>

            <!-- Progress Bar -->
            <div
                class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 overflow-hidden mb-2"
            >
                <div
                    :style="{ width: progress + '%' }"
                    class="bg-green-500 h-4 transition-all duration-500 ease-in-out"
                ></div>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-300 tracking-wide">
                {{ progress }}%
            </p>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    progress: {
        type: Number,
        default: 0,
    },
});
</script>

<style scoped>
/* Optional: subtle pulse animation for the modal container */
div[role="modal"] {
    animation: pulse-bg 2s infinite;
}

@keyframes pulse-bg {
    0%,
    100% {
        background-color: rgba(0, 0, 0, 0.4);
    }
    50% {
        background-color: rgba(0, 0, 0, 0.5);
    }
}
</style>
