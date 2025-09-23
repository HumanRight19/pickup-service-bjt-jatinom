<template>
    <transition name="fade">
        <div
            v-if="show"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
        >
            <div
                class="bg-white dark:bg-gray-900 p-6 rounded-2xl shadow-lg w-full max-w-md"
            >
                <h2
                    class="text-lg font-bold mb-4 text-gray-800 dark:text-white"
                >
                    Edit Blok Pasar
                </h2>
                <form @submit.prevent="$emit('submit')" class="space-y-4">
                    <div>
                        <label
                            for="nama_blok"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                        >
                            Nama Blok
                        </label>
                        <input
                            v-model="form.nama_blok"
                            type="text"
                            id="nama_blok"
                            class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-800 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 px-4 py-3 transition"
                            required
                        />
                    </div>

                    <div class="flex justify-end gap-3">
                        <button
                            type="button"
                            @click="$emit('close')"
                            class="px-4 py-2 rounded-xl bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 transition"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 bg-yellow-500 hover:bg-yellow-600 text-white px-5 py-2.5 rounded-xl font-semibold text-sm transition"
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
