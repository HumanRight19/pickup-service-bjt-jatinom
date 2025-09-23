<template>
    <PetugasLayout :user="petugas" activePage="dashboard">
        <div class="min-h-screen py-6 px-4 sm:px-6 bg-gray-50 dark:bg-gray-950">
            <div class="space-y-6">
                <!-- Header -->
                <header class="flex items-center justify-between mb-2">
                    <button
                        @click="$inertia.visit('/petugas/titip-setoran')"
                        class="flex items-center text-blue-500 hover:text-blue-600 text-sm sm:text-base"
                    >
                        ← Kembali
                    </button>
                    <h1
                        class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white tracking-tight"
                    >
                        Detail Nasabah
                    </h1>
                </header>

                <!-- Info Nasabah -->
                <div
                    class="bg-white/95 dark:bg-gray-800/95 rounded-2xl shadow-md backdrop-blur-sm p-5 sm:p-6 space-y-4"
                >
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold dark:text-white">
                            {{ nasabah.nama }}
                        </h2>
                        <span
                            class="inline-block bg-gradient-to-r from-blue-400 to-blue-600 text-white text-xs font-semibold px-3 py-1 rounded-full"
                        >
                            {{ nasabah.nama_umplung }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
                                    {{ nasabah.alamat }}
                                </p>
                            </div>
                        </div>

                        <!-- Blok -->
                        <div class="flex items-start gap-2">
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
                                    {{
                                        titipHariIni?.blok?.nama_blok ||
                                        nasabah.blokPasar?.nama_blok ||
                                        "-"
                                    }}
                                </span>
                            </div>
                        </div>

                        <!-- Nomor Rekening -->
                        <div class="flex items-start gap-2">
                            <span class="text-yellow-500 mt-1">🏦</span>
                            <div>
                                <p
                                    class="text-sm text-gray-500 dark:text-gray-300"
                                >
                                    No. Rekening
                                </p>
                                <p
                                    class="text-gray-700 dark:text-white font-medium"
                                >
                                    {{ nasabah.nomor_rekening }}
                                </p>
                            </div>
                        </div>

                        <!-- Nomor HP -->
                        <div class="flex items-start gap-2">
                            <span class="text-purple-500 mt-1">📞</span>
                            <div>
                                <p
                                    class="text-sm text-gray-500 dark:text-gray-300"
                                >
                                    No. HP
                                </p>
                                <p
                                    class="text-gray-700 dark:text-white font-medium"
                                >
                                    {{ nasabah.nomor_hp }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Setoran Hari Ini -->
                <div
                    v-if="titipHariIni"
                    class="bg-yellow-50 dark:bg-blue-900/20 rounded-2xl shadow-md p-5 sm:p-6 flex flex-col gap-4"
                >
                    <div
                        class="flex flex-col sm:flex-row sm:justify-between sm:items-center"
                    >
                        <div>
                            <h3 class="text-lg font-semibold dark:text-white">
                                Setoran Hari Ini
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-300">
                                Tanggal:
                                {{ formatDate(titipHariIni.tanggal_titip) }}
                            </p>
                            <p
                                class="text-xl font-bold text-gray-800 dark:text-white mt-1"
                            >
                                Rp
                                {{
                                    titipHariIni.jumlah?.toLocaleString("id-ID")
                                }}
                            </p>
                            <p
                                v-if="latestRequestStatus === 'pending'"
                                class="text-sm text-yellow-600 dark:text-yellow-300 mt-2"
                            >
                                Menunggu approval
                            </p>
                        </div>

                        <!-- Tombol Edit Nominal -->
                        <button
                            @click="openEditModal(titipHariIni)"
                            :disabled="!canEdit"
                            class="inline-flex items-center px-4 py-2 rounded-full bg-yellow-500 hover:bg-yellow-600 text-white font-medium shadow-md transition disabled:bg-yellow-700 disabled:text-white disabled:cursor-not-allowed"
                        >
                            ✏️ Edit Nominal
                        </button>
                    </div>
                </div>

                <!-- Modal Edit Nominal -->
                <transition name="fade">
                    <div
                        v-if="modalEdit"
                        class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
                        @click.self="modalEdit = false"
                    >
                        <transition name="scale-fade">
                            <div
                                class="bg-white dark:bg-gray-800 rounded-3xl w-80 shadow-xl overflow-hidden relative"
                            >
                                <div
                                    class="absolute top-0 left-0 w-full h-16 bg-gradient-to-r from-yellow-400 via-yellow-500 to-yellow-600"
                                ></div>
                                <div class="p-6 pt-20 flex flex-col gap-4">
                                    <h2
                                        class="text-lg font-bold text-gray-800 dark:text-gray-100 text-center"
                                    >
                                        Edit Titip Setoran
                                    </h2>

                                    <!-- Input Nominal -->
                                    <div class="relative">
                                        <span
                                            class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-300"
                                            >Rp</span
                                        >
                                        <input
                                            :value="formattedEditJumlah"
                                            @input="onEditJumlahChange"
                                            type="text"
                                            inputmode="numeric"
                                            placeholder="Masukkan nominal"
                                            class="w-full border border-gray-300 dark:border-gray-600 pl-10 px-3 py-2 rounded-xl focus:outline-none focus:ring-2 focus:ring-yellow-500 dark:bg-gray-700 dark:text-gray-100 transition"
                                        />
                                    </div>

                                    <p
                                        v-if="!isTitipSetoranValid"
                                        class="text-red-600 dark:text-red-400 text-sm mt-1"
                                    >
                                        Titip setoran minimal Rp100
                                    </p>

                                    <!-- Buttons -->
                                    <div
                                        class="flex justify-between gap-4 mt-2"
                                    >
                                        <button
                                            @click="modalEdit = false"
                                            class="flex-1 flex items-center justify-center gap-2 px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl transition text-sm"
                                        >
                                            Batal
                                        </button>
                                        <button
                                            @click="submitEdit"
                                            :disabled="!isTitipSetoranValid"
                                            class="flex-1 flex items-center justify-center gap-2 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-xl transition text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            Simpan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </transition>
                    </div>
                </transition>

                <!-- Toast -->
                <transition name="fade">
                    <div
                        v-if="notif.show"
                        class="fixed bottom-5 left-1/2 -translate-x-1/2 bg-green-600 text-white px-5 py-2 text-sm rounded-full shadow-md"
                    >
                        {{ notif.message }}
                    </div>
                </transition>
            </div>
        </div>
    </PetugasLayout>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { router } from "@inertiajs/vue3";
import PetugasLayout from "@/Layouts/PetugasLayout.vue";

const props = defineProps({
    nasabah: Object,
    petugas: Object,
    titipHariIni: Object, // tambahkan ini
});

const modalEdit = ref(false);
const editJumlah = ref("");
const editingSetoran = ref(null);
const notif = ref({ show: false, message: "" });

// Format tanggal
function formatDate(date) {
    if (!date) return "-";
    return new Date(date).toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    });
}

// Setoran hari ini
const titipHariIni = computed(() => props.titipHariIni ?? null);

// Status request terakhir
const latestRequestStatus = computed(() => {
    const reqs = titipHariIni.value?.requests ?? [];
    return reqs.length ? reqs.slice(-1)[0].status : null;
});

// Tombol Edit aktif jika belum pending / sudah approved
const canEdit = computed(() => latestRequestStatus.value !== "pending");

// Validasi input minimal Rp100
const isTitipSetoranValid = computed(() => Number(editJumlah.value) >= 100);

// Format jumlah untuk input
const formattedEditJumlah = computed(() => {
    if (!editJumlah.value) return "";
    return editJumlah.value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
});

// Buka modal edit
function openEditModal(titip) {
    editingSetoran.value = titip;
    editJumlah.value = titip.jumlah.toString();
    modalEdit.value = true;
}

// Update editJumlah saat input
function onEditJumlahChange(e) {
    const raw = e.target.value.replace(/\D/g, "");
    editJumlah.value = raw || "";
}

// Submit edit setoran
function submitEdit() {
    router.post(
        route("petugas.titipsetoran.ajukanEdit"),
        {
            titip_setoran_id: editingSetoran.value.id,
            nominal_baru: editJumlah.value,
        },
        {
            onSuccess: () => {
                modalEdit.value = false;
                notif.value = {
                    show: true,
                    message:
                        "Permintaan koreksi nominal titip setoran terkirim ke supervisor!",
                };
                setTimeout(() => (notif.value.show = false), 3000);
                router.reload();
            },
        }
    );
}

// Watcher untuk update tombol otomatis jika approved
watch(
    () => titipHariIni.value?.requests?.slice(-1)[0]?.status,
    (newStatus, oldStatus) => {
        if (oldStatus !== "approved" && newStatus === "approved") {
            notif.value = {
                show: true,
                message:
                    "Supervisor telah menyetujui titip setoran. Tombol Edit sekarang aktif!",
            };
            setTimeout(() => (notif.value.show = false), 3000);
        }
    }
);
</script>

<style scoped>
.fade-scale-enter-active,
.fade-scale-leave-active {
    transition: all 0.25s ease;
}
.fade-scale-enter-from,
.fade-scale-leave-to {
    opacity: 0;
    transform: scale(0.95);
}
</style>
