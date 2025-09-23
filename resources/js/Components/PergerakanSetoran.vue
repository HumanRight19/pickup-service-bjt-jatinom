<template>
    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-md mt-8">
        <h3 class="text-xl font-semibold text-gray-800 dark:text-white mb-6">
            📊 Pergerakan Setoran Mingguan per Blok
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            <!-- Filter Pergerakan Setoran -->
            <div class="mb-4">
                <label
                    class="text-sm font-medium text-gray-700 dark:text-white mb-1 block"
                >
                    Filter Pergerakan:
                </label>
                <select
                    v-model="filter"
                    class="w-48 text-sm rounded-md border-gray-300 shadow-sm dark:bg-gray-800 dark:text-white"
                >
                    <option value="all">Semua Blok</option>
                    <option value="naik">Hanya Blok Naik 📈</option>
                    <option value="turun">Hanya Blok Turun 📉</option>
                </select>
            </div>

            <p class="text-sm text-gray-500 mt-1 dark:text-gray-400">
                Menampilkan {{ dataTersaring.length }} dari
                {{ props.data.length }} blok
            </p>

            <div
                v-for="blok in dataTersaring"
                :key="blok.nama_blok"
                class="bg-gradient-to-br from-gray-50 to-white dark:from-gray-900 dark:to-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5 shadow hover:shadow-lg transition"
                :title="tooltip(blok)"
            >
                <div class="flex items-center justify-between">
                    <h4
                        class="text-base font-medium text-gray-700 dark:text-white"
                    >
                        {{ blok.nama_blok }}
                    </h4>

                    <div
                        class="flex items-center gap-1 text-sm font-semibold px-2 py-1 rounded-full"
                        :class="[
                            perubahan(blok) > 0
                                ? 'bg-green-100 text-green-700 dark:bg-green-800 dark:text-green-100'
                                : perubahan(blok) < 0
                                ? 'bg-red-100 text-red-700 dark:bg-red-800 dark:text-red-100'
                                : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-200',
                        ]"
                    >
                        <span v-if="perubahan(blok) > 0">▲</span>
                        <span v-else-if="perubahan(blok) < 0">▼</span>
                        <span v-else>–</span>
                        {{ persen(blok).toFixed(1) }}%
                    </div>
                </div>

                <div
                    class="mt-3 text-xs text-gray-600 dark:text-gray-300 space-y-1"
                >
                    <p>
                        Minggu ini:
                        <strong>{{ formatRupiah(blok.minggu_ini) }}</strong>
                    </p>
                    <p>
                        Minggu lalu:
                        <strong>{{ formatRupiah(blok.minggu_lalu) }}</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";
const props = defineProps<{
    data: Array<{
        nama_blok: string;
        minggu_ini: number;
        minggu_lalu: number;
    }>;
}>();

function perubahan(blok) {
    return blok.minggu_ini - blok.minggu_lalu;
}

function persen(blok) {
    if (blok.minggu_lalu === 0 && blok.minggu_ini === 0) return 0;
    if (blok.minggu_lalu === 0) return 100;
    return ((blok.minggu_ini - blok.minggu_lalu) / blok.minggu_lalu) * 100;
}

function tooltip(blok) {
    return `Minggu lalu: ${formatRupiah(
        blok.minggu_lalu
    )}\nMinggu ini: ${formatRupiah(blok.minggu_ini)}`;
}

function formatRupiah(number: number) {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(number);
}

const filter = ref<"all" | "naik" | "turun">("all");

const dataTersaring = computed(() => {
    let hasil = props.data;

    if (filter.value === "naik") {
        hasil = props.data
            .filter((blok) => perubahan(blok) > 0)
            .sort((a, b) => perubahan(b) - perubahan(a));
    } else if (filter.value === "turun") {
        hasil = props.data
            .filter((blok) => perubahan(blok) < 0)
            .sort((a, b) => perubahan(a) - perubahan(b)); // karena negatif
    } else {
        hasil = [...props.data].sort(
            (a, b) => Math.abs(perubahan(b)) - Math.abs(perubahan(a))
        );
    }

    return hasil;
});
</script>
