<template>
    <transition name="fade">
        <div
            v-if="show"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
            @click.self="$emit('close')"
        >
            <div
                class="bg-white/90 dark:bg-gray-900/90 backdrop-blur-md p-8 rounded-2xl shadow-2xl w-full max-w-md border border-gray-200/30 dark:border-gray-700/30 animate-fadeIn"
            >
                <!-- Header -->
                <h2
                    class="text-xl font-semibold mb-6 text-gray-800 dark:text-gray-100 tracking-wide"
                >
                    Edit Blok Pasar
                </h2>

                <!-- Form -->
                <form @submit.prevent="$emit('submit')" class="space-y-5">
                    <div>
                        <label
                            for="nama_blok"
                            class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300"
                        >
                            Nama Blok
                        </label>
                        <input
                            v-model="form.nama_blok"
                            type="text"
                            id="nama_blok"
                            placeholder="Masukkan nama blok..."
                            required
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50/70 dark:bg-gray-800/70 text-gray-800 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent transition-all duration-200"
                        />
                    </div>

                    <!-- Buttons -->
                    <div class="flex justify-end gap-3 pt-4">
                        <button
                            type="button"
                            @click="$emit('close')"
                            class="px-5 py-2.5 rounded-full bg-gray-200/70 dark:bg-gray-700/70 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 transition-all duration-200 shadow-sm"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-gradient-to-r from-yellow-500 to-yellow-400 hover:from-yellow-600 hover:to-yellow-500 text-white font-semibold text-sm transition-all duration-200 shadow-md"
                        >
                            <PencilIcon class="w-4 h-4" />
                            Update Blok
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </transition>
</template>

<script setup>
import { PencilIcon } from "@heroicons/vue/24/solid";

defineProps({
    show: Boolean,
    form: Object,
});
</script>

<style scoped>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}
.animate-fadeIn {
    animation: fadeIn 0.25s ease-out;
}
</style>
