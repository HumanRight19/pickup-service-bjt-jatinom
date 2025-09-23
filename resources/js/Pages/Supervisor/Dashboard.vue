<template>
    <SupervisorLayout :user="user" activePage="dashboard">
        <!-- Container utama dengan rounded background -->
        <div class="min-h-screen py-8 px-4 md:px-8">
            <div
                class="bg-gray-50 dark:bg-gray-950 rounded-3xl shadow-inner p-6 md:p-10"
            >
                <!-- Breadcrumb -->
                <nav class="mb-6 text-sm text-gray-500 dark:text-gray-400">
                    <ol class="flex space-x-2">
                        <li>
                            <a
                                href="/supervisor"
                                class="hover:text-indigo-600 transition font-medium"
                            >
                                Supervisor
                            </a>
                        </li>
                        <li>/</li>
                        <li
                            class="text-gray-700 dark:text-gray-200 font-semibold"
                        >
                            Dashboard
                        </li>
                    </ol>
                </nav>

                <!-- Header -->
                <header class="mb-10">
                    <h1
                        class="text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight"
                    >
                        Dashboard Supervisor
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">
                        Selamat datang kembali,
                        <span class="font-semibold text-indigo-600">{{
                            user.name
                        }}</span>
                        👋
                    </p>
                </header>

                <!-- Ringkasan Statistik -->
                <section class="mb-12">
                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8"
                    >
                        <div
                            v-for="(item, index) in stats"
                            :key="index"
                            class="relative p-6 rounded-2xl shadow-lg bg-white dark:bg-gray-900 transition transform hover:-translate-y-1 hover:shadow-2xl"
                        >
                            <div
                                class="absolute top-0 left-0 w-full h-1 rounded-t-2xl"
                                :class="item.color"
                            ></div>
                            <h3
                                class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2"
                            >
                                {{ item.title }}
                            </h3>
                            <p
                                class="text-3xl font-extrabold text-gray-900 dark:text-white mb-1"
                            >
                                {{ item.value }}
                            </p>
                            <p
                                class="text-xs text-gray-400 dark:text-gray-500 mb-2"
                            >
                                {{ item.subtitle }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Grafik Statistik -->
                <section
                    class="bg-white dark:bg-gray-900 rounded-2xl shadow p-6 mb-10"
                >
                    <div class="flex items-center justify-between mb-6">
                        <h3
                            class="text-lg font-semibold text-gray-800 dark:text-white"
                        >
                            📊 Statistik Setoran
                        </h3>
                        <select
                            v-model="periode"
                            class="rounded-md border-gray-300 dark:border-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm dark:bg-gray-800 dark:text-white"
                        >
                            <option value="mingguan">7 Hari Terakhir</option>
                            <option value="bulanan">6 Bulan Terakhir</option>
                            <option value="tahunan">12 Bulan Terakhir</option>
                        </select>
                    </div>
                    <ChartCard
                        :key="periode"
                        :title="judulGrafik"
                        :chart-data="dataGrafikAktif"
                    />
                </section>

                <!-- Pergerakan Setoran -->
                <section
                    class="bg-white dark:bg-gray-900 rounded-2xl shadow p-6 mb-10"
                >
                    <h3
                        class="text-lg font-semibold text-gray-800 dark:text-white mb-4"
                    >
                        📈 Pergerakan Setoran per Blok
                    </h3>
                    <PergerakanSetoran :data="pergerakanSetoranPerBlok" />
                </section>

                <!-- Action Section -->
                <div class="flex justify-end">
                    <button
                        @click="downloadQRCodes"
                        :disabled="loading"
                        class="bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 text-white font-semibold px-6 py-3 rounded-xl shadow-lg transition disabled:opacity-50"
                    >
                        <span v-if="loading">⏳ Menyiapkan ZIP...</span>
                        <span v-else>⬇️ Download Semua QR Nasabah</span>
                    </button>
                </div>
            </div>
        </div>
    </SupervisorLayout>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";
import axios from "axios";
import ChartCard from "@/Components/ChartCard.vue";
import SupervisorLayout from "@/Layouts/SupervisorLayout.vue";
import PergerakanSetoran from "@/Components/PergerakanSetoran.vue";

const props = defineProps<{
    user: { id: number; name: string; email: string; role: string };
    jumlahPetugas: number;
    jumlahNasabah: number;
    totalSetoranHariIni: number;
    totalSetoranBulanIni: number;
    totalSetoranTahunIni: number;
    grafikHarian: Array<{ tanggal: string; jumlah: number }>;
    grafikBulanan: Array<{ bulan: string; jumlah: number }>;
    grafikTahunan: Array<{ bulan: string; jumlah: number }>;
    pergerakanSetoranPerBlok: Array<{
        nama_blok: string;
        minggu_ini: number;
        minggu_lalu: number;
    }>;
}>();

const stats = computed(() => [
    {
        title: "Jumlah Petugas",
        value: props.jumlahPetugas,
        subtitle: "Aktif bulan ini",
        color: "bg-gradient-to-r from-blue-500 to-blue-400",
    },
    {
        title: "Jumlah Nasabah",
        value: props.jumlahNasabah,
        subtitle: "Total terdaftar",
        color: "bg-gradient-to-r from-green-500 to-green-400",
    },
    {
        title: "Total Setoran",
        value: `Rp ${props.totalSetoranBulanIni.toLocaleString("id-ID")}`,
        subtitle: "Bulan ini",
        color: "bg-gradient-to-r from-yellow-500 to-yellow-400",
    },
    {
        title: "Rekap Harian",
        value: `Rp ${props.totalSetoranHariIni.toLocaleString("id-ID")}`,
        subtitle: "Hari ini",
        color: "bg-gradient-to-r from-red-500 to-red-400",
    },
]);

const periode = ref("mingguan");

const dataGrafikAktif = computed(() => {
    switch (periode.value) {
        case "bulanan":
            return props.grafikBulanan;
        case "tahunan":
            return props.grafikTahunan;
        default:
            return props.grafikHarian;
    }
});

const judulGrafik = computed(() => {
    switch (periode.value) {
        case "bulanan":
            return "Setoran 6 Bulan Terakhir";
        case "tahunan":
            return "Setoran 12 Bulan Terakhir";
        default:
            return "Setoran 7 Hari Terakhir";
    }
});

const loading = ref(false);

async function downloadQRCodes() {
    loading.value = true;
    try {
        const response = await axios.get("/supervisor/download-qr", {
            responseType: "blob",
        });
        const blob = new Blob([response.data], { type: "application/zip" });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = `qrcodes_${new Date().toISOString().split("T")[0]}.zip`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error("Gagal download QR Codes:", error);
    } finally {
        loading.value = false;
    }
}
</script>
