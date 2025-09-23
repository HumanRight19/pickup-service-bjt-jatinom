<template>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
            v-for="n in nasabahs"
            :key="n.id"
            class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 flex flex-col min-h-[260px] max-h-[280px] transition hover:shadow-md"
        >
            <!-- Header: Nama & Weekly Change -->
            <div class="flex justify-between items-center mb-3">
                <div class="cursor-pointer" @click="$emit('detail', n)">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                        {{ n.nama_umplung || "-" }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 -mt-1">
                        {{ n.nama || "-" }}
                    </p>
                </div>

                <!-- Weekly Change -->
                <div
                    v-if="n.weeklyChange !== undefined"
                    class="flex items-center gap-1 text-sm font-medium px-2 py-1 rounded-full min-w-[50px] justify-center"
                    :class="{
                        'bg-green-100 text-green-700 dark:bg-green-800 dark:text-green-100':
                            n.weeklyChange > 0,
                        'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-100':
                            n.weeklyChange < 0,
                        'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-200':
                            n.weeklyChange === 0,
                    }"
                >
                    <span v-if="n.weeklyChange > 0">▲</span>
                    <span v-else-if="n.weeklyChange < 0">▼</span>
                    <span v-else>–</span>
                    {{ Math.abs(n.weeklyChange) }}%
                </div>
            </div>

            <!-- Tombol Detail -->
            <div class="mb-4">
                <button
                    @click="lihatDetail(n)"
                    class="w-full flex items-center justify-center gap-2 py-2 rounded-lg bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium transition"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M15 12H3m12 0l-4-4m4 4l-4 4"
                        />
                    </svg>
                    Detail
                </button>
            </div>

            <div class="flex-1"></div>

            <!-- Footer: Tombol Hapus & Update -->
            <div class="flex gap-2">
                <!-- Hapus kiri -->
                <button
                    @click="$emit('delete', n.id)"
                    class="flex-1 flex items-center justify-center gap-2 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-medium transition"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                    Hapus
                </button>

                <!-- Update kanan -->
                <button
                    @click="$emit('edit', n)"
                    class="flex-1 flex items-center justify-center gap-2 py-2 rounded-lg bg-yellow-100 hover:bg-yellow-200 text-yellow-700 dark:text-yellow-600 text-sm font-medium transition"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M15.232 5.232l3.536 3.536M9 13l6-6 3.536 3.536L12.536 16.5H9v-3.5z"
                        />
                    </svg>
                    Update
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { router } from "@inertiajs/vue3";

defineProps(["nasabahs"]);
defineEmits(["edit", "delete", "detail"]);

function lihatDetail(nasabah) {
    router.post(route("supervisor.nasabah.setSession"), {
        nasabah_id: nasabah.id,
    });
}
</script>
