<template>
    <transition name="fade-scale">
        <div
            v-if="show"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50"
            @click.self="$emit('close')"
        >
            <div
                class="bg-white dark:bg-gray-900 p-6 sm:p-8 rounded-2xl shadow-2xl w-full max-w-md transform transition-all"
                @click.stop
            >
                <!-- Icon Header -->
                <div class="flex items-center justify-center mb-4">
                    <div
                        class="w-12 h-12 flex items-center justify-center rounded-full bg-red-100 dark:bg-red-900/40"
                    >
                        <svg
                            class="w-6 h-6 text-red-600 dark:text-red-400"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.5"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 9v3.75m0 3.75h.007M21 12a9 9 0 11-18 0 9 
                   9 0 0118 0z"
                            />
                        </svg>
                    </div>
                </div>

                <!-- Title -->
                <h3
                    class="text-xl font-semibold text-center mb-2 text-gray-900 dark:text-gray-100"
                >
                    Konfirmasi Pembatalan
                </h3>

                <!-- Body -->
                <p class="text-center text-gray-600 dark:text-gray-300 mb-6">
                    Apakah Anda yakin ingin
                    <span class="font-semibold">membatalkan</span>
                    jadwal ini? Aksi ini tidak dapat diurungkan.
                </p>

                <!-- Action Buttons -->
                <div class="flex justify-center gap-3">
                    <button
                        @click="$emit('close')"
                        class="px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700 transition font-medium"
                    >
                        Batal
                    </button>
                    <button
                        @click="$emit('confirm')"
                        class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-red-500 to-red-600 text-white font-medium shadow hover:from-red-600 hover:to-red-700 transition"
                    >
                        Ya, Batalkan
                    </button>
                </div>
            </div>
        </div>
    </transition>
</template>

<script setup>
defineProps({
    show: Boolean,
});
</script>

<style scoped>
.fade-scale-enter-active,
.fade-scale-leave-active {
    transition: all 0.3s ease;
}
.fade-scale-enter-from,
.fade-scale-leave-to {
    opacity: 0;
    transform: scale(0.95);
}
</style>
