<template>
    <transition name="fade-scale">
        <div
            v-if="show"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
        >
            <div
                class="relative w-full max-w-md p-8 rounded-3xl shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border border-gray-200/40 dark:border-gray-700/40 text-center animate-pop"
            >
                <!-- Close Button -->
                <button
                    @click="$emit('close')"
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition"
                >
                    ✕
                </button>

                <!-- Animated Icon -->
                <div
                    class="mx-auto mb-5 w-16 h-16 flex items-center justify-center rounded-2xl bg-gradient-to-r from-red-500 to-pink-500 text-white shadow-lg animate-bounce-slow"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-8 w-8"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 9v2m0 4h.01M12 5a7 7 0 100 14 7 7 0 000-14z"
                        />
                    </svg>
                </div>

                <!-- Title -->
                <h2
                    class="text-2xl font-extrabold text-gray-800 dark:text-white mb-3"
                >
                    Gagal Menyimpan
                </h2>

                <!-- Message -->
                <p class="text-gray-600 dark:text-gray-300 mb-2 text-base">
                    Tidak dapat menambahkan di hari yang sama!
                </p>
                <p
                    v-if="message"
                    class="text-gray-500 dark:text-gray-400 mb-6 text-sm"
                >
                    {{ message }}
                </p>

                <!-- Buttons -->
                <div class="flex justify-center gap-3">
                    <button
                        @click="$emit('close')"
                        class="px-6 py-2.5 rounded-xl font-semibold bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md hover:shadow-lg hover:scale-105 transition-transform duration-200"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </transition>
</template>

<script setup>
defineProps({
    show: Boolean,
    message: String,
});
defineEmits(["close"]);
</script>

<style scoped>
.fade-scale-enter-active,
.fade-scale-leave-active {
    transition: all 0.3s ease;
}
.fade-scale-enter-from {
    opacity: 0;
    transform: scale(0.85);
}
.fade-scale-leave-to {
    opacity: 0;
    transform: scale(0.9);
}

@keyframes pop {
    0% {
        transform: scale(0.9);
        opacity: 0;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}
.animate-pop {
    animation: pop 0.25s ease-out;
}

@keyframes bounce-slow {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-6px);
    }
}
.animate-bounce-slow {
    animation: bounce-slow 2s infinite;
}
</style>
