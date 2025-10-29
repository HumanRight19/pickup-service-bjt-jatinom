<template>
    <Head title="Setoran Reguler" />

    <PetugasLayout :user="petugas" activePage="dashboard">
        <div
            class="min-h-screen py-4 px-4 md:px-8 bg-gray-50 dark:bg-gray-800 transition-colors"
        >
            <div
                class="bg-gray-50 dark:bg-gray-950 rounded-3xl shadow-inner p-6 md:p-10"
            >
                <!-- Header -->
                <header class="mb-6">
                    <h1
                        class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight"
                    >
                        Setoran Harian • {{ blok.nama_blok }}
                    </h1>
                    <p
                        class="text-gray-600 dark:text-gray-400 mt-1 flex flex-wrap items-center gap-2"
                    >
                        Selamat datang,
                        <span
                            class="font-semibold text-green-500 dark:text-green-400"
                            >{{ petugas.name }}</span
                        >
                        👋
                        <span
                            class="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded-full text-sm font-medium"
                            >{{ tanggalHariIni }}</span
                        >
                    </p>
                </header>

                <!-- Total Setoran Card Modern -->
                <div
                    class="mb-6 w-full max-w-md mx-auto bg-gradient-to-r from-green-400 to-green-600 dark:from-green-800 dark:to-green-900/40 rounded-3xl p-6 flex flex-col items-center justify-center shadow-xl backdrop-blur-sm hover:scale-105 transition-transform duration-300"
                >
                    <div class="flex items-center gap-3">
                        <!-- Icon setoran -->
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-10 w-10 text-white dark:text-green-200"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 8c-3 0-5 1.5-5 4s2 4 5 4 5-1.5 5-4-2-4-5-4z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 8v8m0 0l-3-3m3 3l3-3"
                            />
                        </svg>
                        <h2
                            class="text-xl sm:text-2xl font-bold text-white dark:text-green-100"
                        >
                            Total Setoran Hari Ini
                        </h2>
                    </div>
                    <p
                        class="mt-4 text-4xl sm:text-5xl font-extrabold text-white dark:text-green-50 tracking-tight animate-pulse"
                    >
                        Rp{{ formatRupiah(totalSetoran) }}
                    </p>
                </div>

                <!-- Search + QR -->
                <div class="mb-6 w-full sm:max-w-md relative">
                    <input
                        v-model="form.search"
                        type="text"
                        placeholder="Cari nama nasabah..."
                        class="w-full px-4 py-2 pr-10 border rounded-lg text-sm dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-green-500 focus:outline-none transition"
                    />
                    <button
                        @click="startScan"
                        class="absolute inset-y-0 right-0 flex items-center justify-center px-2 bg-green-600 text-white rounded-r-lg hover:bg-green-700 hover:scale-110 transition z-10"
                        title="Scan QR Nasabah"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 3h6v6H3V3zM15 3h6v6h-6V3zM3 15h6v6H3v-6zM15 15h6v6h-6v-6zM11 11h2v2h-2v-2z"
                            />
                        </svg>
                    </button>
                </div>

                <!-- Tombol Tampilkan Semua -->
                <div
                    v-if="focusedNasabahId === 'search_done'"
                    class="text-right mb-4"
                >
                    <button
                        @click="showAllNasabahs"
                        class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition"
                    >
                        Tampilkan Semua
                    </button>
                </div>

                <!-- Grid Nasabah -->
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4"
                    ref="nasabahGrid"
                >
                    <div
                        v-for="nasabah in displayedNasabahs"
                        :key="nasabah.id"
                        :ref="(el) => (nasabahRefs[nasabah.id] = el)"
                        :class="[
                            'bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-md flex flex-col justify-between hover:shadow-lg transition',
                            highlightedNasabahId === String(nasabah.id)
                                ? 'ring-4 ring-green-500 animate-pulse'
                                : '',
                        ]"
                    >
                        <div>
                            <h3
                                class="text-base font-bold text-gray-800 dark:text-white"
                            >
                                {{ nasabah.nama }}
                            </h3>
                            <span
                                class="inline-block bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 text-sm font-semibold px-2 py-0.5 rounded-full mt-1"
                            >
                                {{ nasabah.nama_umplung }}
                            </span>
                            <p
                                class="text-sm text-gray-500 dark:text-gray-400 mt-1"
                            >
                                {{ nasabah.alamat }}
                            </p>

                            <!-- Status setoran -->
                            <p
                                v-if="
                                    setoransLocal[nasabah.id]?.status ===
                                        'sudah_setor' ||
                                    setoransLocal[nasabah.id]?.status ===
                                        'pengajuan_batal_ditolak'
                                "
                                class="text-green-600 text-sm mt-1 font-medium"
                            >
                                Sudah Setor: Rp{{
                                    formatRupiah(
                                        setoransLocal[nasabah.id]?.jumlah
                                    )
                                }}
                            </p>
                            <p
                                v-else-if="
                                    setoransLocal[nasabah.id]?.status ===
                                    'pengajuan_batal'
                                "
                                class="text-yellow-600 text-sm mt-1 font-medium"
                            >
                                Menunggu approval batal
                            </p>
                            <p
                                v-else-if="
                                    setoransLocal[nasabah.id]?.status ===
                                    'pengajuan_batal_diterima'
                                "
                                class="text-blue-600 text-sm mt-1 font-medium"
                            >
                                Pengajuan batal diterima, bisa setor ulang
                            </p>
                        </div>

                        <!-- Tombol aksi -->
                        <div class="mt-3 flex justify-end gap-2">
                            <button
                                :disabled="
                                    isAllDisabled(nasabah.id) ||
                                    !isInputEnabled(nasabah.id)
                                "
                                @click="openInputModal(nasabah.id)"
                                class="p-2 rounded-full bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50"
                                title="Input Setoran"
                            >
                                <Plus class="w-4 h-4" />
                            </button>

                            <button
                                @click="goToDetail(nasabah.id)"
                                :disabled="
                                    isAllDisabled(nasabah.id) ||
                                    !isEditEnabled(nasabah.id)
                                "
                                class="p-2 rounded-full bg-yellow-500 text-white hover:bg-yellow-600 disabled:opacity-50 disabled:cursor-not-allowed"
                                title="Detail"
                            >
                                <Info class="w-4 h-4" />
                            </button>

                            <button
                                @click="openCancelModal(nasabah.id)"
                                :disabled="
                                    isAllDisabled(nasabah.id) ||
                                    !isCancelEnabled(nasabah.id)
                                "
                                class="p-2 rounded-full bg-red-600 text-white hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                title="Batalkan Setoran"
                            >
                                <X class="w-4 h-4" />
                            </button>

                            <button
                                @click="handlePrintClick(nasabah.id)"
                                :disabled="
                                    isAllDisabled(nasabah.id) ||
                                    !isPrintEnabled(nasabah.id)
                                "
                                class="p-2 rounded-full bg-green-600 text-white hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                title="Cetak"
                            >
                                <Printer class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>

                <slot name="modals" />

                <!-- Pagination -->
                <div
                    v-if="props.nasabahs.last_page > 1"
                    class="mt-6 flex justify-center dark:text-white"
                >
                    <nav>
                        <ul class="flex gap-1">
                            <li
                                v-for="link in props.nasabahs.links"
                                :key="link.label"
                            >
                                <button
                                    :disabled="!link.url"
                                    @click="goToPage(link)"
                                    v-html="link.label"
                                    class="px-3 py-1 rounded border border-gray-300 dark:border-gray-700 text-sm transition"
                                    :class="{
                                        'bg-blue-600 text-white border-blue-600':
                                            link.active,
                                        'hover:bg-gray-200 dark:hover:bg-gray-700':
                                            link.url,
                                        'opacity-50 cursor-not-allowed':
                                            !link.url,
                                    }"
                                ></button>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <ScanModal
            v-if="scanning"
            mode="reguler"
            @nasabah-found="handleNasabahFound"
            @nasabah-not-found="handleNasabahNotFound"
            @cancel-scan="stopScan"
        />

        <InputSetoranModal
            :modelValue="formValue"
            :open="showInputModal"
            @update:modelValue="formValue = $event"
            @close="closeInputModal"
            @submit="submitSetoranWrapper"
        />

        <CancelSetoranModal
            v-if="showCancelModal"
            v-model="cancelReason"
            @close="closeCancelModal"
            @confirm="
                (reason, done) => {
                    confirmCancel(reason).finally(() => done?.());
                }
            "
        />

        <RequestSentModal
            v-if="showRequestSentModal"
            @close="closeRequestSentModal"
        />

        <LoadingModal v-if="showLoadingModal" :progress="loadingProgress" />

        <InfoModal
            v-model:show="infoModal.show"
            :title="infoModal.title"
            :message="infoModal.message"
        />

        <!-- Toast -->
        <transition name="fade">
            <div
                v-if="notif.show"
                class="fixed bottom-5 right-5 bg-green-600 text-white px-4 py-2 text-sm rounded shadow-md"
            >
                {{ notif.message }}
            </div>
        </transition>
    </PetugasLayout>
</template>

<script setup>
import { Head } from "@inertiajs/vue3";
import PetugasLayout from "@/Layouts/PetugasLayout.vue";
import { Plus, Info, Printer, X } from "lucide-vue-next";
import { ref, reactive, computed, nextTick, onMounted, watch } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import debounce from "lodash/debounce";

import ScanModal from "@/Components/Modals/ScanModal.vue";
import InfoModal from "@/Components/Modals/InfoModal.vue";
import InputSetoranModal from "@/Components/Modals/InputSetoranModal.vue";
import CancelSetoranModal from "@/Components/Modals/CancelSetoranModal.vue";
import RequestSentModal from "@/Components/Modals/RequestSentModal.vue";
import LoadingModal from "@/Components/Modals/LoadingModal.vue";

const props = defineProps({
    user: Object,
    nasabahs: Object,
    setorans: Object,
    blok: Object,
    petugas: Object,
    totalSetoranHariIni: Number, // <--- tambahkan ini
    search: String,
});

// --- Local copy untuk search cepat ---
const nasabahsLocal = ref([...props.nasabahs.data]);

// --- Sync setorans ---
const setoransLocal = reactive({});

Object.values(props.setorans || {}).forEach((s) => {
    setoransLocal[s.nasabah_id] = { ...s };
});

const search = ref(props.search || "");

const highlightedNasabahId = ref(null);
const nasabahRefs = reactive({});
const formValue = ref(0);
const scanning = ref(false);
const showInputModal = ref(false);
const nasabahIdActive = ref(null);
const notif = ref({ show: false, message: "" });
const showLoadingModal = ref(false);
const loadingProgress = ref(0);
const tanggalHariIni = new Date().toLocaleDateString("id-ID", {
    weekday: "long",
    year: "numeric",
    month: "long",
    day: "numeric",
});
const showCancelModal = ref(false);
const cancelNasabahId = ref(null);
const cancelReason = ref("");
const showRequestSentModal = ref(false);

const form = useForm({
    search: props.search || "",
    page: props.nasabahs.current_page || 1,
});

// Sync setorans
function syncSetoransLocal(newSetorans) {
    Object.values(newSetorans).forEach((setoran) => {
        if (setoran.nasabah_id) {
            setoransLocal[String(setoran.nasabah_id)] = {
                ...setoran,
                status: setoran.status ?? setoran.status_setoran,
                status_setoran: setoran.status_setoran,
            };
        }
    });
}

// total setoran
const totalSetoran = ref(props.totalSetoranHariIni || 0);

onMounted(() => {
    nasabahsLocal.value.forEach((n) => {
        if (n.id == null) console.warn("Nasabah tanpa id:", n);
    });

    if (props.setorans) {
        syncSetoransLocal(props.setorans);
    }
});

function formatRupiah(value) {
    return !value && value !== 0 ? "0" : Number(value).toLocaleString("id-ID");
}

const focusedNasabahId = ref(null);

watch(
    () => form.search,
    debounce((val) => {
        router.post(
            route("petugas.dashboard"),
            { search: val },
            {
                preserveState: true, // ✅ tetap di halaman tanpa reload full
                replace: true,
                onSuccess: () => {
                    window.history.replaceState(
                        {},
                        "",
                        route("petugas.dashboard")
                    );
                },
            }
        );
    }, 400)
);

// --- Tampilkan Semua muncul hanya setelah hasil ditemukan ---
watch(
    () => props.nasabahs.data,
    (newData) => {
        if (form.search && newData.length > 0) {
            // Hasil ditemukan, munculkan tombol tampilkan semua
            focusedNasabahId.value = "search_done";
        } else {
            focusedNasabahId.value = null;
        }
    },
    { immediate: true }
);

// untuk memberi efek highlight otomatis
watch(
    () => props.nasabahs.data,
    (newData) => {
        if (form.search && newData.length > 0) {
            // Cari nasabah pertama yang cocok
            const found = newData.find((n) =>
                n.nama.toLowerCase().includes(form.search.toLowerCase())
            );

            if (found) {
                // Set ID string untuk highlight
                highlightedNasabahId.value = String(found.id);

                // Tunggu nextTick supaya DOM sudah ter-render
                nextTick(() => {
                    const el = nasabahRefs[found.id];
                    if (el?.scrollIntoView) {
                        el.scrollIntoView({
                            behavior: "smooth",
                            block: "center",
                        });
                    }

                    // Hilangkan highlight otomatis setelah 2 detik
                    setTimeout(() => {
                        if (highlightedNasabahId.value === String(found.id)) {
                            highlightedNasabahId.value = null;
                        }
                    }, 2000);
                });

                // Fokus tombol tampilkan semua
                focusedNasabahId.value = "search_done";
            } else {
                highlightedNasabahId.value = null;
                focusedNasabahId.value = null;
            }
        } else {
            highlightedNasabahId.value = null;
            focusedNasabahId.value = null;
        }
    },
    { immediate: true }
);

// Replace displayedNasabahs computed
const displayedNasabahs = computed(() => props.nasabahs.data || []);

function openInputModal(nasabahId) {
    nasabahIdActive.value = nasabahId;

    const nasabahSetoran = setoransLocal[nasabahId];

    // Reset formValue agar field input selalu kosong untuk setoran baru
    if (
        !nasabahSetoran ||
        nasabahSetoran.status === "pengajuan_batal_diterima"
    ) {
        formValue.value = 0; // kosong
    } else {
        formValue.value = nasabahSetoran.jumlah || 0;
    }

    showInputModal.value = true;
}

function closeInputModal() {
    showInputModal.value = false;
    nasabahIdActive.value = null;
    formValue.value = 0;
}

// Info modal untuk semua alert
const infoModal = reactive({
    show: false,
    title: "Info",
    message: "",
});

function showInfoModal(message, title = "Info") {
    infoModal.title = title;
    infoModal.message = message;
    infoModal.show = true;
}

// Cancel setoran
function openCancelModal(nasabahId) {
    const setoran = setoransLocal[String(nasabahId)];
    if (!setoran || !setoran.setoran_id) {
        showInfoModal(
            "Setoran untuk nasabah ini belum ada, batal tidak bisa dilakukan."
        );
        return;
    }

    cancelNasabahId.value = String(nasabahId);
    cancelReason.value = "";
    showCancelModal.value = true;
}

function closeCancelModal() {
    showCancelModal.value = false;
    cancelNasabahId.value = null;
    cancelReason.value = "";
}

function confirmCancel(reasonFromModal) {
    const nasabahId = cancelNasabahId.value;
    if (!nasabahId) return showInfoModal("Nasabah tidak valid.");

    const setoran = setoransLocal[String(nasabahId)];
    if (!setoran || !setoran.setoran_id) {
        return showInfoModal(
            "Setoran untuk nasabah ini belum ada, batal tidak bisa dilakukan."
        );
    }

    const alasan = reasonFromModal?.trim();
    if (!alasan) return showInfoModal("Masukkan alasan batal dulu!");

    // Tampilkan loading
    showLoadingModal.value = true;
    loadingProgress.value = 0;
    let interval = setInterval(() => {
        if (loadingProgress.value < 80) loadingProgress.value += 10;
    }, 200);

    axios
        .delete(route("petugas.setoran.destroy", { id: setoran.setoran_id }), {
            data: { alasan },
        })
        .then((res) => {
            // Update status lokal
            setoransLocal[String(nasabahId)] = {
                ...setoran,
                status: "pengajuan_batal",
            };

            // Tutup modal cancel & tampilkan modal sukses
            closeCancelModal();
            showRequestSentModal.value = true;

            // Optional: tampilkan toast sukses
            notif.value = {
                show: true,
                message:
                    res.data.message || "Permintaan batal berhasil dikirim.",
            };
            setTimeout(() => (notif.value.show = false), 3000);
        })
        .catch((err) => {
            console.error(err.response || err);
            showInfoModal(
                err.response?.data?.message ||
                    err.message ||
                    "Gagal batalkan setoran."
            );
        })
        .finally(() => {
            clearInterval(interval);
            loadingProgress.value = 100;
            setTimeout(() => (showLoadingModal.value = false), 300);
        });
}

// Tutup RequestSentModal
function closeRequestSentModal() {
    showRequestSentModal.value = false;
}

// --- Status tombol ---
function isInputEnabled(id) {
    const setor = setoransLocal[id];
    if (!setor) return true; // Nasabah baru bisa input
    const status = setor.status;
    return status === "belum_setor" || status === "pengajuan_batal_diterima";
}

function isEditEnabled(id) {
    const status = setoransLocal[id]?.status;
    // Bisa edit setoran hanya kalau sudah setor atau pengajuan batal ditolak
    return status === "sudah_setor" || status === "pengajuan_batal_ditolak";
}

function isCancelEnabled(id) {
    const setoran = setoransLocal[id];
    if (!setoran || !setoran.setoran_id) return false;
    const status = setoran.status;
    // Bisa batal setoran hanya kalau sudah setor atau pengajuan batal ditolak
    return status === "sudah_setor" || status === "pengajuan_batal_ditolak";
}

function isPrintEnabled(id) {
    const status = setoransLocal[id]?.status;
    // Bisa cetak hanya kalau sudah setor atau pengajuan batal ditolak
    return status === "sudah_setor" || status === "pengajuan_batal_ditolak";
}

function isAllDisabled(id) {
    const status = setoransLocal[id]?.status;
    // Kalau status pengajuan batal sedang menunggu, semua tombol nonaktif
    if (status === "pengajuan_batal") return true;
    return false;
}

// Detail & Print
function goToDetail(nasabahId) {
    const setoran = setoransLocal[nasabahId];
    if (!setoran || !setoran.setoran_id)
        return showInfoModal("Setoran belum ada.");

    router.post(route("petugas.nasabah.storeToSession"), {
        nasabah_id: nasabahId,
    });
}

// Tombol Cetak
async function handlePrintClick(nasabahId) {
    const setoran = setoransLocal[nasabahId];
    if (!setoran || !setoran.setoran_id) {
        return showInfoModal("Setoran belum ada.");
    }

    // --- MULAI LOADING ---
    showLoadingModal.value = true;
    loadingProgress.value = 0;
    let interval = setInterval(() => {
        if (loadingProgress.value < 80) loadingProgress.value += 10;
    }, 200);

    try {
        // 1. Prepare cetak (simpan ke session)
        loadingProgress.value = 20;
        const prepareResponse = await axios.post(
            route("petugas.setoran.prepareCetak"),
            {
                nasabah_id: nasabahId,
            }
        );

        if (!prepareResponse.data.success) {
            throw new Error("Gagal menyiapkan data cetak");
        }

        // 2. Generate ESC/POS data
        loadingProgress.value = 50;
        const escposResponse = await axios.post(route("petugas.print.escpos"));

        if (!escposResponse.data.success) {
            throw new Error(
                escposResponse.data.message || "Gagal generate data printer"
            );
        }

        loadingProgress.value = 80;

        // 3. Coba kirim ke Bluetooth Printer App via Intent
        const printData = escposResponse.data.printData;

        // Opsi A: Intent untuk aplikasi Bluetooth Printer (Android)
        const intentUrl = `intent://print?data=${encodeURIComponent(
            printData
        )}&type=base64#Intent;scheme=blueprinter;package=com.fidea.blueprint;end`;

        // Opsi B: Custom URL scheme (fallback)
        const customScheme = `bluetoothprinter://print?data=${encodeURIComponent(
            printData
        )}`;

        // Coba intent dulu
        window.location.href = intentUrl;

        // Jika dalam 2 detik tidak redirect, tampilkan opsi alternatif
        setTimeout(() => {
            // Jika masih di halaman yang sama, berarti intent gagal
            if (
                confirm(
                    "Aplikasi Bluetooth Printer tidak terdeteksi.\n\nIngin buka preview manual untuk print?"
                )
            ) {
                // Fallback: buka preview thermal (cara lama yang masih berfungsi)
                const previewUrl = route("petugas.setoran.previewThermal");
                window.open(previewUrl, "_blank");
            }
        }, 2000);

        loadingProgress.value = 100;
    } catch (err) {
        console.error("Error saat print:", err);
        showInfoModal(err.message || "Gagal menyiapkan cetak.");
    } finally {
        clearInterval(interval);
        setTimeout(() => {
            loadingProgress.value = 100;
            showLoadingModal.value = false;
        }, 500);
    }
}

// async function handlePrintClick(nasabahId) {
//     const setoran = setoransLocal[nasabahId];
//     if (!setoran || !setoran.setoran_id) {
//         return showInfoModal("Setoran belum ada.");
//     }

//     // --- MULAI LOADING ---
//     showLoadingModal.value = true;
//     loadingProgress.value = 0;
//     let interval = setInterval(() => {
//         if (loadingProgress.value < 80) loadingProgress.value += 10;
//     }, 200);

//     try {
//         // 1. Panggil prepareCetak dengan axios, bukan router.post
//         await axios.post(route("petugas.setoran.prepareCetak"), {
//             nasabah_id: nasabahId,
//         });

//         // 2. Kalau sukses, buka preview tanpa query string
//         const url = route("petugas.setoran.previewThermal");
//         window.open(url, "_blank");
//     } catch (err) {
//         console.error("Error saat prepareCetak:", err);
//         showInfoModal("Gagal menyiapkan cetak.");
//     } finally {
//         clearInterval(interval);
//         loadingProgress.value = 100;
//         setTimeout(() => (showLoadingModal.value = false), 300);
//     }
// }

// Scan QR
function startScan() {
    scanning.value = true;
}

function stopScan() {
    scanning.value = false;
}

// --- QR Scan highlight ---
function handleNasabahFound(nasabah) {
    if (!nasabah) return;

    stopScan(); // Tutup modal scan

    // Gunakan search di server, sama seperti searchbar
    form.search = nasabah.nama; // bisa juga pakai nasabah.id
    router.post(
        route("petugas.dashboard"),
        { search: form.search, page: 1 }, // cari di server dari page manapun
        {
            preserveState: true,
            replace: true,
            onSuccess: (pageProps) => {
                // Sinkronisasi setorans lokal
                if (pageProps.setorans) {
                    syncSetoransLocal(pageProps.setorans);
                }

                // Cari nasabah hasil scan dari data server
                const found = pageProps.nasabahs.data.find(
                    (n) => String(n.id) === String(nasabah.id)
                );

                if (found) {
                    // Highlight dan fokus
                    highlightedNasabahId.value = String(found.id);
                    focusedNasabahId.value = "search_done";

                    nextTick(() => {
                        const el = nasabahRefs[found.id];
                        if (el?.scrollIntoView) {
                            el.scrollIntoView({
                                behavior: "smooth",
                                block: "center",
                            });
                        }

                        // Hilangkan highlight otomatis setelah 2 detik
                        setTimeout(() => {
                            if (
                                highlightedNasabahId.value === String(found.id)
                            ) {
                                highlightedNasabahId.value = null;
                            }
                        }, 2000);
                    });
                } else {
                    handleNasabahNotFound(); // Nasabah tidak ditemukan
                }
            },
        }
    );
}

// --- Tombol Tampilkan Semua ---
function showAllNasabahs() {
    form.search = "";
    focusedNasabahId.value = null;
    highlightedNasabahId.value = null;

    router.get(
        route("petugas.dashboard"),
        { page: 1 },
        {
            preserveState: true,
            replace: true,
            onSuccess: (pageProps) => {
                // Sinkronisasi setoransLocal lagi
                if (pageProps.setorans) {
                    syncSetoransLocal(pageProps.setorans);
                }
            },
        } // <--- preserveState true biar gak reload sidebar
    );
}

function handleNasabahNotFound() {
    scanning.value = false;
    showInfoModal("Nasabah tidak ditemukan!");
}

function submitSetoranWrapper(value, callback) {
    if (!nasabahIdActive.value) return callback(false);
    const nasabahId = nasabahIdActive.value;

    // --- MULAI LOADING ---
    showLoadingModal.value = true;
    loadingProgress.value = 0;
    let interval = setInterval(() => {
        if (loadingProgress.value < 80) loadingProgress.value += 10;
    }, 200);

    axios
        .post("/petugas/setoran", { nasabah_id: nasabahId, jumlah: value })
        .then((res) => {
            const newSetoran = res.data.setoran;
            if (!newSetoran)
                throw new Error("Setoran baru tidak diterima dari server");

            setoransLocal[nasabahId] = {
                ...newSetoran,
                setoran_id: newSetoran.id,
                status: newSetoran.status ?? "sudah_setor",
                tanggal: newSetoran.tanggal
                    ? new Date(newSetoran.tanggal)
                    : null,
            };

            // 🟩 Tambahkan ini — update total langsung tanpa reload
            if (res.data.totalSetoran !== undefined) {
                totalSetoran.value = res.data.totalSetoran;
            } else {
                // fallback kalau backend belum kirim total baru
                totalSetoran.value += Number(newSetoran.jumlah || 0);
            }

            highlightedNasabahId.value = nasabahId;
            nextTick(() => {
                nasabahRefs[nasabahId]?.scrollIntoView({
                    behavior: "smooth",
                    block: "center",
                });
            });
            setTimeout(() => (highlightedNasabahId.value = null), 2000);

            closeInputModal();
            callback(true);
        })
        .catch((err) => {
            showInfoModal(
                err.response?.data?.message ||
                    err.message ||
                    "Gagal input setoran"
            );
            callback(false);
        })
        .finally(() => {
            clearInterval(interval);
            loadingProgress.value = 100;
            setTimeout(() => (showLoadingModal.value = false), 300);
        });
}

// --- Pagination ---
const totalPages = computed(() => props.nasabahs.last_page || 1);

function goToPage(link) {
    if (!link?.url) return;

    try {
        const url = new URL(link.url, window.location.origin);
        const page = url.searchParams.get("page") || 1;

        router.post(
            route("petugas.dashboard"),
            { page }, // update session dashboard_page
            {
                preserveState: true, // ✅ biar sidebar gak buka ulang
                replace: true,
                onSuccess: () => {
                    // setelah sukses, hapus query param agar URL tetap bersih
                    window.history.replaceState(
                        {},
                        "",
                        route("petugas.dashboard")
                    );
                },
            }
        );
    } catch (err) {
        console.warn("Gagal parsing URL pagination:", err);
    }
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
@keyframes pulse {
    0%,
    100% {
        opacity: 1;
        transform: scale(1);
    }
    50% {
        opacity: 0.9;
        transform: scale(1.02);
    }
}
.animate-pulse {
    animation: pulse 1.2s ease-in-out;
}
</style>
