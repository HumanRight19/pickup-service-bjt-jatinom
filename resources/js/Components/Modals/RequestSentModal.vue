<template>
    <transition name="fade-scale">
        <div
            class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50"
        >
            <div
                class="bg-white/80 dark:bg-gray-900/80 backdrop-blur-md p-6 rounded-3xl w-72 text-center shadow-lg border border-white/20"
            >
                <!-- Icon Checkmark -->
                <div class="flex justify-center mb-4">
                    <div
                        class="h-12 w-12 flex items-center justify-center rounded-full bg-green-100 dark:bg-green-900"
                    >
                        <svg
                            class="h-6 w-6 text-green-600 dark:text-green-400"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M5 13l4 4L19 7"
                            />
                        </svg>
                    </div>
                </div>

                <!-- Title -->
                <h2
                    class="text-xl font-semibold mb-2 text-gray-800 dark:text-gray-100"
                >
                    Request Terkirim!
                </h2>

                <!-- Message -->
                <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
                    Tunggu konfirmasi dari supervisor.
                </p>

                <!-- Button -->
                <button
                    @click="$emit('close')"
                    class="px-5 py-2 bg-blue-600 text-white rounded-xl shadow hover:bg-blue-700 transition-colors duration-300"
                >
                    Tutup
                </button>
            </div>
        </div>
    </transition>
</template>

<script setup>
defineEmits(["close"]);
</script>

<style scoped>
/* Smooth fade + scale transition */
.fade-scale-enter-active,
.fade-scale-leave-active {
    transition: all 0.3s ease;
}
.fade-scale-enter-from,
.fade-scale-leave-to {
    opacity: 0;
    transform: scale(0.95);
}
.fade-scale-enter-to,
.fade-scale-leave-from {
    opacity: 1;
    transform: scale(1);
}
</style>
