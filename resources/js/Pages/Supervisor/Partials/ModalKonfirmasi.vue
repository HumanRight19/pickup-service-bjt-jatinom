<template>
    <transition name="fade">
        <div
            v-if="show"
            class="fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm flex items-center justify-center z-50"
        >
            <div
                class="bg-white/90 dark:bg-gray-900/90 rounded-2xl shadow-2xl p-6 max-w-sm w-full border border-gray-100 dark:border-gray-700 transition"
            >
                <!-- Gambar / Icon -->
                <div class="flex justify-center mb-4">
                    <svg
                        class="w-16 h-16 text-red-600 dark:text-red-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 9v2m0 4h.01M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z"
                        ></path>
                    </svg>
                </div>

                <h2
                    class="text-xl font-semibold text-gray-900 dark:text-white mb-3 text-center"
                >
                    {{ title || "Konfirmasi" }}
                </h2>
                <p
                    class="mb-5 text-gray-700 dark:text-gray-300 leading-relaxed text-center"
                >
                    {{
                        message ||
                        "Apakah kamu yakin ingin melakukan tindakan ini?"
                    }}
                </p>

                <div class="flex justify-center gap-4">
                    <button
                        @click="$emit('cancel')"
                        class="px-5 py-2 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200"
                    >
                        Batal
                    </button>
                    <button
                        @click="$emit('confirm')"
                        class="px-5 py-2 rounded-xl bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold shadow-md hover:shadow-lg transition-all duration-200"
                    >
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    </transition>
</template>

<script setup>
defineProps({
    show: Boolean,
    title: String,
    message: String,
});
defineEmits(["cancel", "confirm"]);
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.25s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
