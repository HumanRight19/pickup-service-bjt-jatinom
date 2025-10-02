<template>
    <Head title="Detail Nasabah" />

    <SupervisorLayout :user="authUser" activePage="nasabah">
        <div class="min-h-screen py-8 px-4 md:px-8">
            <div
                class="bg-gray-50 dark:bg-gray-950 rounded-3xl shadow-inner p-6 md:p-10 space-y-8"
            >
                <!-- Breadcrumb & Header -->
                <nav class="mb-6 text-sm text-gray-500 dark:text-gray-400">
                    <ol class="flex space-x-2">
                        <li>
                            <a
                                href="/supervisor"
                                class="hover:text-indigo-600 transition font-medium"
                                >Supervisor</a
                            >
                        </li>
                        <li>/</li>
                        <li>
                            <a
                                href="/supervisor/nasabah"
                                class="hover:text-indigo-600 transition font-medium"
                                >Nasabah</a
                            >
                        </li>
                        <li>/</li>
                        <li
                            class="text-gray-700 dark:text-gray-200 font-semibold"
                        >
                            Detail Nasabah
                        </li>
                    </ol>
                </nav>

                <header class="mb-10">
                    <h1
                        class="text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight"
                    >
                        Detail Nasabah
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">
                        Informasi lengkap nasabah, grafik setoran, dan riwayat
                        setoran
                    </p>
                </header>

                <!-- Info Nasabah -->
                <div
                    class="bg-white dark:bg-gray-900 rounded-3xl shadow-lg p-6 md:p-10"
                >
                    <h2
                        class="text-2xl font-semibold text-gray-900 dark:text-white mb-6"
                    >
                        Informasi Nasabah
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Nama Nasabah -->
                        <div class="flex items-start gap-2">
                            <span class="text-indigo-500 mt-1">🧑</span>
                            <div>
                                <p
                                    class="text-sm text-gray-500 dark:text-gray-300"
                                >
                                    Nama Nasabah
                                </p>
                                <p
                                    class="text-gray-700 dark:text-white font-medium"
                                >
                                    {{ nasabah.nama || "-" }}
                                </p>
                            </div>
                        </div>

                        <!-- Nama Umplung -->
                        <div class="flex items-start gap-2">
                            <span class="text-pink-500 mt-1">🏠</span>
                            <div>
                                <p
                                    class="text-sm text-gray-500 dark:text-gray-300"
                                >
                                    Nama Umplung
                                </p>
                                <p
                                    class="text-gray-700 dark:text-white font-medium"
                                >
                                    {{ nasabah.nama_umplung || "-" }}
                                </p>
                            </div>
                        </div>

                        <!-- Alamat -->
                        <div class="flex items-start gap-2">
                            <span class="text-blue-500 mt-1">📍</span>
                            <div>
                                <p
                                    class="text-sm text-gray-500 dark:text-gray-300"
                                >
                                    Alamat
                                </p>
                                <p
                                    class="text-gray-700 dark:text-white font-medium"
                                >
                                    {{ nasabah.alamat || "-" }}
                                </p>
                            </div>
                        </div>

                        <!-- Blok -->
                        <div
                            class="flex items-start gap-2"
                            v-if="nasabah.blok_pasar"
                        >
                            <span class="text-green-500 mt-1">🔖</span>
                            <div>
                                <p
                                    class="text-sm text-gray-500 dark:text-gray-300"
                                >
                                    Blok
                                </p>
                                <span
                                    class="inline-block bg-gradient-to-r from-green-200 to-green-400 text-green-900 text-xs font-semibold px-2 py-0.5 rounded-full"
                                >
                                    {{ nasabah.blok_pasar.nama_blok }}
                                </span>
                            </div>
                        </div>

                        <!-- Nomor Rekening -->
                        <div class="flex items-start gap-2">
                            <span class="text-yellow-500 mt-1">🏦</span>
                            <div class="flex items-center gap-2">
                                <div>
                                    <p
                                        class="text-sm text-gray-500 dark:text-gray-300"
                                    >
                                        No. Rekening
                                    </p>
                                    <transition name="fade" mode="out-in">
                                        <p
                                            :key="showFullRekening"
                                            class="text-gray-700 dark:text-white font-medium"
                                        >
                                            {{
                                                showFullRekening
                                                    ? formatRekening(
                                                          nasabah.nomor_rekening,
                                                          false
                                                      )
                                                    : formatRekening(
                                                          nasabah.nomor_rekening,
                                                          true
                                                      )
                                            }}
                                        </p>
                                    </transition>
                                </div>
                                <button
                                    @click="toggleShowRekening"
                                    class="ml-1 p-1 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition"
                                    title="Lihat / Sembunyikan Rekening"
                                >
                                    <Eye
                                        v-if="!showFullRekening"
                                        class="w-5 h-5 text-gray-600 dark:text-gray-300"
                                    />
                                    <EyeOff
                                        v-else
                                        class="w-5 h-5 text-gray-600 dark:text-gray-300"
                                    />
                                </button>
                            </div>
                        </div>

                        <!-- Nomor HP -->
                        <div class="flex items-start gap-2">
                            <span class="text-purple-500 mt-1">📞</span>
                            <div class="flex items-center gap-2">
                                <div>
                                    <p
                                        class="text-sm text-gray-500 dark:text-gray-300"
                                    >
                                        No. HP
                                    </p>
                                    <transition name="fade" mode="out-in">
                                        <p
                                            :key="showFullHp"
                                            class="text-gray-700 dark:text-white font-medium"
                                        >
                                            {{
                                                showFullHp
                                                    ? formatHp(
                                                          nasabah.nomor_hp,
                                                          false
                                                      )
                                                    : formatHp(
                                                          nasabah.nomor_hp,
                                                          true
                                                      )
                                            }}
                                        </p>
                                    </transition>
                                </div>
                                <button
                                    @click="toggleShowHp"
                                    class="ml-1 p-1 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition"
                                    title="Lihat / Sembunyikan HP"
                                >
                                    <Eye
                                        v-if="!showFullHp"
                                        class="w-5 h-5 text-gray-600 dark:text-gray-300"
                                    />
                                    <EyeOff
                                        v-else
                                        class="w-5 h-5 text-gray-600 dark:text-gray-300"
                                    />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grafik Setoran -->
                <div
                    class="bg-white dark:bg-gray-900 rounded-3xl shadow-lg p-6 md:p-10"
                >
                    <div
                        class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-4"
                    >
                        <h2
                            class="text-2xl font-semibold text-gray-900 dark:text-white"
                        >
                            Grafik Setoran
                        </h2>
                        <select
                            v-model="filterWaktu"
                            class="border rounded px-3 py-1 dark:bg-gray-700 dark:text-white"
                        >
                            <option value="harian">Harian</option>
                            <option value="mingguan">Mingguan</option>
                            <option value="bulanan">Bulanan</option>
                            <option value="tahunan">Tahunan</option>
                        </select>
                    </div>

                    <div
                        class="mb-4 p-4 bg-blue-100 dark:bg-blue-900 rounded-lg font-semibold text-blue-800 dark:text-blue-200"
                    >
                        Total Setoran: Rp
                        {{ totalSetoran.toLocaleString("id-ID") }}
                    </div>

                    <ChartCardDetail
                        :title="`Grafik Setoran (${filterWaktu})`"
                        :chart-data="chartDataForCard"
                    />
                </div>

                <!-- Tabel Riwayat Setoran -->
                <div
                    class="bg-white dark:bg-gray-900 rounded-3xl shadow-lg p-6 md:p-10"
                >
                    <h2
                        class="text-2xl font-semibold text-gray-900 dark:text-white mb-4"
                    >
                        Riwayat Setoran
                    </h2>
                    <div
                        class="overflow-x-auto overflow-y-auto rounded-lg max-h-[500px]"
                    >
                        <table
                            class="w-full text-sm text-left text-gray-700 dark:text-gray-300 min-w-[800px]"
                        >
                            <thead
                                class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 text-center"
                            >
                                <tr>
                                    <th class="px-4 py-2">Tanggal</th>
                                    <th class="px-4 py-2">Nama Nasabah</th>
                                    <th class="px-4 py-2">Nama Umplung</th>
                                    <th class="px-4 py-2">Petugas</th>
                                    <th class="px-4 py-2">Jenis Setoran</th>
                                    <th class="px-4 py-2">Nominal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="item in combinedSetorans"
                                    :key="item.key"
                                    class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-center"
                                >
                                    <td class="px-4 py-2">
                                        <div>
                                            {{ item.created_at.split(" ")[0] }}
                                        </div>
                                        <!-- Tanggal -->
                                        <div class="text-xs text-gray-500">
                                            {{ item.created_at.split(" ")[1] }}
                                        </div>
                                        <!-- Waktu -->
                                    </td>
                                    <td class="px-4 py-2">
                                        {{ item.nasabah }}
                                    </td>
                                    <td class="px-4 py-2">
                                        {{ nasabah?.nama_umplung || "-" }}
                                    </td>
                                    <td class="px-4 py-2">
                                        {{ item.petugas }}
                                    </td>
                                    <td class="px-4 py-2">
                                        {{ item.jenis_setoran }}
                                    </td>
                                    <td class="px-4 py-2">
                                        {{ formatRupiah(item.jumlah) }}
                                    </td>
                                </tr>
                                <tr v-if="combinedSetorans.length === 0">
                                    <td
                                        colspan="6"
                                        class="text-center py-4 text-gray-500 dark:text-gray-400"
                                    >
                                        Belum ada data setoran
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </SupervisorLayout>
</template>

<script setup>
import { Head } from "@inertiajs/vue3";
import { Eye, EyeOff } from "lucide-vue-next";
import { ref, computed } from "vue";
import SupervisorLayout from "@/Layouts/SupervisorLayout.vue";
import ChartCardDetail from "@/Components/ChartCardDetail.vue";

const props = defineProps({
    auth: Object,
    nasabah: Object,
    grafik: Object,
    tabel: Array, // <- gunakan ini
});

// ====== Toggle rekening show/hide ======
const showFullRekening = ref(false);
const showFullHp = ref(false);

function toggleShowRekening() {
    showFullRekening.value = !showFullRekening.value;
}

function toggleShowHp() {
    showFullHp.value = !showFullHp.value;
}

/**
 * Format rekening sesuai pola:
 * - Grouping: 1 digit, 3 digit, sisa, 1 digit
 * - Jika masked = true → dari digit ke-8 diganti '*'
 */
function formatRekening(raw, masked = true) {
    if (!raw) return "-";
    const digits = ("" + raw).replace(/\D/g, "");
    const n = digits.length;
    if (n === 0) return "";

    const result = [];
    for (let i = 0; i < n; i++) {
        const pos = i + 1;
        if (masked && pos >= 8) {
            result.push("*");
        } else {
            result.push(digits[i]);
        }
    }

    const g1 = result.slice(0, 1).join("");
    const g2 = result.slice(1, 4).join("");
    const gMiddle = n > 4 ? result.slice(4, n - 1).join("") : "";
    const gLast = n > 1 ? result.slice(-1).join("") : "";

    return [g1, g2, gMiddle, gLast].filter(Boolean).join("-");
}

/**
 * Format Nomor HP
 * - Jika masked = true → 4 digit terakhir jadi "****"
 */
function formatHp(raw, masked = true) {
    if (!raw) return "-";
    const digits = ("" + raw).replace(/\D/g, "");
    const n = digits.length;
    if (n === 0) return "";

    if (masked && n > 4) {
        return digits.slice(0, n - 4) + "****";
    }
    return digits;
}

// User
const authUser = computed(
    () => props.auth?.user || { id: 0, name: "-", role: "-" }
);

// Filter waktu
const filterWaktu = ref("harian");

const formatRupiah = (value) => {
    if (!value) return "Rp 0";
    return "Rp " + Number(value).toLocaleString("id-ID");
};

// ===================== Grafik =====================
const chartDataForCard = computed(() => {
    const raw = props.grafik?.[filterWaktu.value] || [];
    return {
        labels: raw.map((d) => d.tanggal || ""),
        datasets: [
            {
                label: "Jumlah Setoran",
                data: raw.map((d) => Number(d.jumlah) || 0),
                borderColor: "#3B82F6",
                backgroundColor: "rgba(59,130,246,0.2)",
                fill: true,
                tension: 0.3,
            },
        ],
    };
});

// ===================== Tabel Riwayat Setoran =====================
const combinedSetorans = computed(() => {
    return props.tabel || [];
});

const totalSetoran = computed(() => {
    return combinedSetorans.value.reduce((sum, d) => sum + (d.jumlah || 0), 0);
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
