<template>
    <SupervisorLayout :user="user" activePage="laporan">
        <div class="min-h-screen py-8 px-4 md:px-8">
            <div
                class="bg-gray-50 dark:bg-gray-950 rounded-3xl shadow-inner p-6 md:p-10"
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
                        <li
                            class="text-gray-700 dark:text-gray-200 font-semibold"
                        >
                            Laporan Setoran
                        </li>
                    </ol>
                </nav>

                <header class="mb-10">
                    <h1
                        class="text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight"
                    >
                        Laporan Setoran
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">
                        Rekapitulasi setoran berdasarkan periode, petugas, dan
                        blok
                    </p>
                </header>

                <!-- Tombol Export -->
                <div class="flex justify-end mb-4">
                    <button
                        v-if="laporan?.data?.length > 0"
                        @click="handleExport"
                        :disabled="exporting"
                        class="bg-green-600 hover:bg-green-700 text-white font-medium px-4 py-2 rounded-full text-sm inline-flex items-center gap-2 shadow-md transition disabled:opacity-50"
                    >
                        ⬇️ Export Excel
                        <svg
                            v-if="exporting"
                            class="animate-spin h-4 w-4"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="white"
                                stroke-width="4"
                                fill="none"
                            />
                            <path
                                class="opacity-75"
                                fill="white"
                                d="M4 12a8 8 0 018-8v8H4z"
                            />
                        </svg>
                    </button>
                </div>

                <!-- Tabel -->
                <div
                    class="overflow-x-auto bg-white dark:bg-gray-900 rounded-2xl shadow p-4"
                >
                    <table
                        class="w-full text-sm text-left text-gray-700 dark:text-gray-200"
                    >
                        <thead>
                            <!-- Filter -->
                            <tr class="bg-gray-50 dark:bg-gray-800">
                                <th class="px-4 py-2">
                                    <select
                                        v-model="form.range"
                                        class="w-full border px-2 py-1 rounded text-sm dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="harian">Harian</option>
                                        <option value="mingguan">
                                            Mingguan
                                        </option>
                                        <option value="bulanan">Bulanan</option>
                                        <option value="tahunan">Tahunan</option>
                                    </select>
                                </th>
                                <th class="px-4 py-2">
                                    <select
                                        v-model="form.petugas_id"
                                        class="w-full border px-2 py-1 rounded text-sm dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="">Semua Petugas</option>
                                        <option
                                            v-for="p in petugasList"
                                            :key="p.id"
                                            :value="p.id"
                                        >
                                            {{ p.name }}
                                        </option>
                                    </select>
                                </th>
                                <th class="px-4 py-2">
                                    <input
                                        type="text"
                                        placeholder="Cari Nasabah..."
                                        v-model="form.nasabah"
                                        class="w-full border px-2 py-1 rounded text-sm dark:bg-gray-700 dark:text-white"
                                    />
                                </th>
                                <th class="px-4 py-2">
                                    <select
                                        v-model="form.blok"
                                        class="w-full border px-2 py-1 rounded text-sm dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="">Semua Blok</option>
                                        <option
                                            v-for="blok in blokList"
                                            :key="blok"
                                            :value="blok"
                                        >
                                            {{ blok }}
                                        </option>
                                    </select>
                                </th>
                                <th class="px-4 py-2">
                                    <input
                                        type="text"
                                        placeholder="Cari Supervisor..."
                                        v-model="form.supervisor"
                                        class="w-full px-2 py-1 rounded text-sm dark:bg-gray-700 dark:text-white"
                                    />
                                </th>
                                <th class="px-4 py-2">
                                    <select
                                        v-model="form.jenis_setoran"
                                        class="w-full border px-2 py-1 rounded text-sm dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="">Semua Jenis</option>
                                        <option
                                            v-for="jenis in props.jenisSetoranList"
                                            :key="jenis"
                                            :value="jenis"
                                        >
                                            {{ jenis }}
                                        </option>
                                    </select>
                                </th>
                                <th class="px-4 py-2">
                                    <select
                                        v-model="form.status"
                                        class="w-full border px-2 py-1 rounded text-sm dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="">Semua Status</option>
                                        <option
                                            v-for="st in props.statusList"
                                            :key="st"
                                            :value="st"
                                        >
                                            {{ st }}
                                        </option>
                                    </select>
                                </th>
                                <th colspan="3"></th>
                            </tr>

                            <!-- Judul Kolom -->
                            <tr>
                                <th class="px-4 py-2 text-center">Tanggal</th>
                                <th class="px-4 py-2 text-center">Petugas</th>
                                <th class="px-4 py-2 text-center">Nasabah</th>
                                <th class="px-4 py-2 text-center">Blok</th>
                                <th class="px-4 py-2 text-center">
                                    Supervisor
                                </th>

                                <!-- perkecil -->
                                <th class="px-2 py-2 text-center w-24">
                                    Jenis
                                </th>

                                <!-- perbesar -->
                                <th class="px-4 py-2 text-center w-40">
                                    Status
                                </th>

                                <th class="px-4 py-2 text-center">Nominal</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="(item, index) in laporan?.data || []"
                                :key="index"
                                class="hover:bg-gray-50 dark:hover:bg-gray-800 transition"
                            >
                                <td class="px-4 py-2 text-center">
                                    {{ item.tanggal_waktu ?? "-" }}
                                </td>
                                <td class="px-4 py-2 text-center">
                                    {{ item.petugas ?? "-" }}
                                </td>
                                <td class="px-4 py-2 text-center">
                                    {{ item.nasabah ?? "-" }}
                                </td>
                                <td class="px-4 py-2 text-center">
                                    {{ item.blok ?? "-" }}
                                </td>
                                <td class="px-4 py-2 text-center">
                                    {{ item.supervisor ?? "-" }}
                                </td>

                                <!-- jenis lebih kecil -->
                                <td class="px-2 py-2 text-center">
                                    <span
                                        class="px-2 py-1 rounded-full text-sm font-semibold whitespace-nowrap"
                                        :class="{
                                            'bg-blue-600 text-white':
                                                item.jenis_setoran ===
                                                'Reguler',
                                            'bg-orange-400 text-white':
                                                item.jenis_setoran === 'Titip',
                                        }"
                                    >
                                        {{ item.jenis_setoran }}
                                    </span>
                                </td>

                                <!-- status lebih lebar + nowrap -->
                                <td class="px-4 py-2 text-center">
                                    <span
                                        class="px-2 py-1 rounded-full text-sm font-semibold whitespace-nowrap"
                                        :class="{
                                            'bg-green-100 text-green-700':
                                                item.status === 'Normal',
                                            'bg-yellow-100 text-yellow-700':
                                                item.status
                                                    ?.toLowerCase()
                                                    .includes('update'),
                                            'bg-red-100 text-red-700':
                                                item.status
                                                    ?.toLowerCase()
                                                    .includes('batal'),
                                        }"
                                    >
                                        {{ item.status }}
                                    </span>
                                </td>

                                <td class="px-4 py-2 text-center">
                                    {{ formatRupiah(item.jumlah ?? 0) }}
                                </td>
                            </tr>

                            <tr v-if="!laporan?.data?.length">
                                <td
                                    colspan="8"
                                    class="px-4 py-4 text-center text-gray-500"
                                >
                                    Tidak ada data untuk filter ini.
                                </td>
                            </tr>
                        </tbody>

                        <tfoot v-if="laporan?.data?.length > 0">
                            <tr
                                class="bg-gray-100 dark:bg-gray-700 font-semibold"
                            >
                                <td colspan="7" class="px-4 py-2 text-right">
                                    Total
                                </td>
                                <td class="px-4 py-2">
                                    {{ formatRupiah(totalJumlahPage) }}
                                </td>
                            </tr>
                            <tr
                                class="bg-gray-100 dark:bg-gray-700 font-semibold"
                            >
                                <td colspan="7" class="px-4 py-2 text-right">
                                    Grand Total
                                </td>
                                <td class="px-4 py-2">
                                    Rp {{ grandTotal.toLocaleString("id-ID") }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>

                    <!-- Pagination -->
                    <div
                        v-if="laporan?.links?.length > 0"
                        class="mt-6 flex justify-center"
                    >
                        <nav class="inline-flex flex-wrap gap-1 text-sm">
                            <template
                                v-for="(link, i) in laporan.links"
                                :key="i"
                            >
                                <Link
                                    v-if="link.url"
                                    href="#"
                                    class="px-3 py-1 border rounded"
                                    :class="{
                                        'bg-blue-600 text-white': link.active,
                                        'bg-white text-gray-800 hover:bg-gray-100 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600':
                                            !link.active,
                                    }"
                                    v-html="link.label"
                                    @click.prevent="goToPage(link)"
                                />
                                <span
                                    v-else
                                    class="px-3 py-1 border rounded text-gray-400 cursor-not-allowed"
                                    v-html="link.label"
                                />
                            </template>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Loading Export -->
        <Transition name="fade">
            <div
                v-if="exporting"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
            >
                <div
                    class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl p-8 w-[90%] max-w-md text-center space-y-4"
                >
                    <div class="text-xl font-semibold text-blue-600">
                        Menyiapkan file Excel...
                    </div>
                    <div
                        class="w-full bg-gray-200 rounded-full h-4 overflow-hidden"
                    >
                        <div
                            class="bg-blue-600 h-full transition-all duration-300"
                            :style="{ width: exportProgress + '%' }"
                        ></div>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-200">
                        Mohon tunggu, file sedang dibuat...
                    </p>
                </div>
            </div>
        </Transition>
    </SupervisorLayout>
</template>

<script setup>
import { Link, useForm } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
import debounce from "lodash/debounce";
import SupervisorLayout from "@/Layouts/SupervisorLayout.vue";

const props = defineProps({
    user: Object,
    laporan: Object,
    petugasList: Array,
    blokList: Array,
    filters: Object,
    totalJumlah: Number,
    totalJumlahPage: Number, // <-- tambahkan
    jenisSetoranList: Array, // ✅
    statusList: Array, // ✅
    grandTotal: {
        type: Number,
        default: 0,
    },
});

const totalJumlahPage = computed(() => props.totalJumlahPage ?? 0);

const initialFilters = {
    range: props.filters.range || "mingguan",
    petugas_id: props.filters.petugas_id || "",
    blok: props.filters.blok || "",
    nasabah: props.filters.nasabah || "",
    supervisor: props.filters.supervisor || "",
    jenis_setoran: props.filters.jenis_setoran || "", // ✅ baru
    status: props.filters.status || "", // ✅ baru
};

const form = useForm({ ...initialFilters, page: props.filters?.page || 1 });

// Watch filter utama → reset ke page 1
watch(
    [
        () => form.range,
        () => form.petugas_id,
        () => form.blok,
        () => form.jenis_setoran,
        () => form.status,
    ],
    () => {
        form.page = 1;
        submitFilter();
    }
);

// Watch pencarian → reset ke page 1 (debounce)
watch(
    [() => form.nasabah, () => form.supervisor],
    debounce(() => {
        form.page = 1;
        submitFilter();
    }, 500)
);

function submitFilter() {
    // biarkan preserveState: true di filter agar form tidak reset saat filter diproses
    form.post(route("supervisor.laporan.filter"), {
        preserveState: true,
        preserveScroll: true,
        headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                .content,
        },
    });
}

// Untuk modal loading menunggu proses export excel
const exporting = ref(false);

const exportProgress = ref(0);

async function handleExport() {
    exporting.value = true;
    exportProgress.value = 0;

    try {
        const response = await fetch("/supervisor/laporan/export", {
            method: "GET",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            },
        });

        if (!response.ok) throw new Error("Gagal mengunduh file");

        // Ambil nama file dari header Content-Disposition
        let filename = "laporan.xlsx"; // default
        const disposition = response.headers.get("Content-Disposition");
        if (disposition && disposition.includes("filename=")) {
            filename = disposition
                .split("filename=")[1]
                .replace(/"/g, "")
                .trim();
        }

        const contentLength = response.headers.get("Content-Length");
        let blob;
        if (!contentLength) {
            blob = await response.blob();
            downloadBlob(blob, filename);
            exportProgress.value = 100;
            return;
        }

        const total = parseInt(contentLength, 10);
        let loaded = 0;
        const reader = response.body.getReader();
        const chunks = [];

        while (true) {
            const { done, value } = await reader.read();
            if (done) break;
            chunks.push(value);
            loaded += value.length;
            exportProgress.value = Math.floor((loaded / total) * 100);
        }

        blob = new Blob(chunks);
        downloadBlob(blob, filename);
        exportProgress.value = 100;
    } catch (err) {
        alert("Export gagal: " + err.message);
    } finally {
        setTimeout(() => {
            exporting.value = false;
        }, 300);
    }
}

function downloadBlob(blob, filename) {
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    a.remove();
    window.URL.revokeObjectURL(url);
}

function formatRupiah(number) {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
    }).format(number);
}

function goToPage(link) {
    if (!link.url) return;

    form.page = new URL(link.url).searchParams.get("page") || 1;

    form.post(route("supervisor.laporan.filter"), {
        preserveState: true,
        preserveScroll: true,
        data: { ...form, page: form.page }, // kirim ke server biar simpan ke session
    });
}
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
