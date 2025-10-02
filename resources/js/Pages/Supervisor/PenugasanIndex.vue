<template>
    <Head title="Penjadwalan" />

    <SupervisorLayout :user="user" activePage="penugasan">
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
                                >Supervisor</a
                            >
                        </li>
                        <li>/</li>
                        <li
                            class="text-gray-700 dark:text-gray-200 font-semibold"
                        >
                            Penugasan
                        </li>
                    </ol>
                </nav>

                <!-- Header -->
                <header class="mb-10">
                    <h1
                        class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight"
                    >
                        Penjadwalan & Manajemen Petugas
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">
                        Atur jadwal penugasan harian petugas dan kelola daftar
                        petugas yang tersedia.
                    </p>
                </header>

                <!-- Grid Penjadwalan (Kiri) & Manajemen Petugas (Kanan) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Penjadwalan Hari Ini -->
                    <section
                        class="bg-white dark:bg-gray-900 rounded-2xl shadow p-6"
                    >
                        <h2
                            class="text-xl font-bold text-gray-800 dark:text-white mb-2"
                        >
                            Penjadwalan Hari Ini
                        </h2>
                        <p
                            class="text-sm text-gray-500 dark:text-gray-400 mb-6"
                        >
                            Kelola tanggal, blok, dan petugas yang tersedia
                        </p>

                        <!-- Form Penugasan -->
                        <form
                            @submit.prevent="submitJadwal"
                            class="space-y-4 mb-8"
                        >
                            <div>
                                <label
                                    class="block text-sm font-medium dark:text-white"
                                    >Tanggal</label
                                >
                                <input
                                    v-model="jadwalForm.tanggal"
                                    type="date"
                                    class="w-full border rounded px-3 py-2 dark:bg-gray-800 dark:text-white"
                                />
                                <p
                                    v-if="jadwalErrors.tanggal"
                                    class="text-sm text-red-600"
                                >
                                    {{ jadwalErrors.tanggal }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium dark:text-white"
                                    >Pilih Blok</label
                                >
                                <select
                                    v-model.number="jadwalForm.blok_id"
                                    class="w-full border rounded px-3 py-2 dark:bg-gray-800 dark:text-white"
                                >
                                    <option disabled value="">
                                        -- Pilih Blok --
                                    </option>
                                    <option
                                        v-for="blok in bloks"
                                        :key="blok.id"
                                        :value="blok.id"
                                    >
                                        {{ blok.nama_blok }}
                                    </option>
                                </select>
                                <p
                                    v-if="jadwalErrors.blok_id"
                                    class="text-sm text-red-600"
                                >
                                    {{ jadwalErrors.blok_id }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium dark:text-white"
                                    >Pilih Petugas</label
                                >
                                <select
                                    v-model="jadwalForm.petugas_id"
                                    class="w-full border rounded px-3 py-2 dark:bg-gray-800 dark:text-white"
                                >
                                    <option disabled value="">
                                        -- Pilih --
                                    </option>
                                    <option
                                        v-for="u in users"
                                        :key="u.id"
                                        :value="u.id"
                                    >
                                        {{ u.name }}
                                    </option>
                                </select>
                                <p
                                    v-if="jadwalErrors.petugas_id"
                                    class="text-sm text-red-600"
                                >
                                    {{ jadwalErrors.petugas_id }}
                                </p>
                            </div>

                            <button
                                type="submit"
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-full shadow transition"
                            >
                                Simpan Penugasan
                            </button>
                        </form>

                        <!-- Tabel Jadwal Hari Ini -->
                        <div
                            class="overflow-hidden rounded-lg shadow border custom-scrollbar"
                        >
                            <table class="w-full text-sm table-fixed">
                                <thead
                                    class="bg-gray-100 dark:bg-gray-800 dark:text-white"
                                >
                                    <tr>
                                        <th class="p-3 text-left">Tanggal</th>
                                        <th class="p-3 text-left">Petugas</th>
                                        <th class="p-3 text-left">Blok</th>
                                        <th class="p-3 text-left">
                                            Supervisor
                                        </th>
                                        <th class="p-3 text-left">Aksi</th>
                                    </tr>
                                </thead>
                            </table>
                            <!-- Scrollable body -->
                            <div class="max-h-[400px] overflow-y-auto">
                                <table class="w-full text-sm table-fixed">
                                    <tbody>
                                        <tr
                                            v-for="jadwal in jadwals.data"
                                            :key="jadwal.id"
                                            class="border-t hover:bg-gray-50 dark:hover:bg-gray-700"
                                        >
                                            <td class="p-3 dark:text-white">
                                                {{ jadwal.tanggal }}
                                            </td>
                                            <td class="p-3 dark:text-white">
                                                {{ jadwal.petugas.name }}
                                            </td>
                                            <td class="p-3 dark:text-white">
                                                {{ jadwal.blok.nama_blok }}
                                            </td>
                                            <td class="p-3 dark:text-white">
                                                {{ jadwal.supervisor.name }}
                                            </td>
                                            <td class="p-3 dark:text-white">
                                                <template
                                                    v-if="
                                                        isCancelable(
                                                            jadwal.tanggal
                                                        )
                                                    "
                                                >
                                                    <button
                                                        @click="
                                                            openBatalModal(
                                                                jadwal.id
                                                            )
                                                        "
                                                        class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-full text-sm"
                                                    >
                                                        Batal
                                                    </button>
                                                </template>
                                                <template v-else>
                                                    <span class="text-gray-500"
                                                        >Tidak bisa batal</span
                                                    >
                                                </template>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div
                            v-if="jadwals?.links?.length > 0"
                            class="mt-4 flex justify-center"
                        >
                            <nav class="inline-flex flex-wrap gap-1 text-sm">
                                <template
                                    v-for="(link, i) in jadwals.links"
                                    :key="i"
                                >
                                    <Link
                                        v-if="link.url"
                                        :href="link.url"
                                        class="px-3 py-1 border rounded"
                                        :class="{
                                            'bg-blue-600 text-white':
                                                link.active,
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
                    </section>

                    <!-- Manajemen Petugas -->
                    <section
                        class="bg-white dark:bg-gray-900 rounded-2xl shadow p-6"
                    >
                        <h2
                            class="text-xl font-bold mb-2 text-gray-800 dark:text-white"
                        >
                            Manajemen Petugas
                        </h2>
                        <p
                            class="text-sm text-gray-500 dark:text-gray-400 mb-4"
                        >
                            Kelola daftar petugas yang tersedia
                        </p>

                        <button
                            class="mb-6 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-full shadow transition"
                            @click="openModal('add')"
                        >
                            + Tambah Petugas
                        </button>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div
                                v-for="user in users"
                                :key="user.id"
                                class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 border border-gray-200 dark:border-gray-700 transition hover:shadow-md"
                            >
                                <div
                                    class="flex justify-between items-center mb-3"
                                >
                                    <h3
                                        class="text-lg font-bold text-gray-800 dark:text-white"
                                    >
                                        {{ user.name }}
                                    </h3>
                                    <span
                                        class="text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        #{{ user.id }}
                                    </span>
                                </div>

                                <p
                                    class="text-sm text-gray-700 dark:text-gray-300 mb-4"
                                >
                                    {{ user.email }}
                                </p>

                                <div class="flex gap-2">
                                    <button
                                        @click="openModal('edit', user)"
                                        class="flex-1 bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-2 rounded-full text-sm font-medium transition"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        @click="confirmDelete(user.id)"
                                        class="flex-1 bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-full text-sm font-medium transition"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>

        <!-- Modal Tambah/Edit Petugas -->
        <div
            v-if="modalOpen"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50"
            @click.self="$emit('update:show', false)"
        >
            <div
                class="bg-white/80 dark:bg-gray-900/80 backdrop-blur-md p-6 rounded-2xl shadow-2xl w-full max-w-md border border-gray-200/40 dark:border-gray-700/40 animate-fadeIn"
            >
                <!-- Header -->
                <h3
                    class="text-xl font-semibold mb-6 text-gray-800 dark:text-gray-100 tracking-wide"
                >
                    {{ modalType === "add" ? "Tambah" : "Edit" }} Petugas
                </h3>

                <!-- Form -->
                <div class="space-y-5">
                    <!-- Nama -->
                    <div>
                        <label
                            class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300"
                        >
                            Nama
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-800/70 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                        />
                        <p v-if="errors.name" class="mt-1 text-sm text-red-500">
                            {{ errors.name }}
                        </p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label
                            class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300"
                        >
                            Email
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-800/70 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                        />
                        <p
                            v-if="errors.email"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ errors.email }}
                        </p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label
                            class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300"
                        >
                            Password
                        </label>
                        <input
                            v-model="form.password"
                            :placeholder="
                                modalType === 'edit'
                                    ? 'Biarkan kosong jika tidak ingin mengubah'
                                    : ''
                            "
                            type="password"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-800/70 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                        />
                        <p
                            v-if="errors.password"
                            class="mt-1 text-sm text-red-500"
                        >
                            {{ errors.password }}
                        </p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-8 flex justify-end gap-3">
                    <button
                        @click="modalOpen = false"
                        class="px-5 py-2.5 rounded-xl bg-gray-200/70 dark:bg-gray-700/70 dark:text-white hover:bg-gray-300 dark:hover:bg-gray-600 transition-all duration-200 shadow-sm"
                    >
                        Batal
                    </button>
                    <button
                        @click="submitForm"
                        class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-blue-500 text-white hover:from-blue-700 hover:to-blue-600 transition-all duration-200 shadow-md"
                    >
                        Simpan
                    </button>
                </div>
            </div>
        </div>

        <!-- Konfirmasi Hapus -->
        <div>
            <!-- Modal Reusable -->
            <ModalKonfirmasi
                :show="confirmDeleteId"
                title="Konfirmasi Hapus"
                message="Yakin ingin menghapus petugas ini?"
                @cancel="confirmDeleteId = null"
                @confirm="deletePetugas"
            />
        </div>

        <ModalError
            :show="showErrorModal"
            :message="errorMessage"
            @close="showErrorModal = false"
        />

        <ConfirmBatalModal
            :show="showConfirmBatal"
            @close="showConfirmBatal = false"
            @confirm="batalJadwal"
        />

        <ToastNotification :show="showToast" :message="toastMessage" />
    </SupervisorLayout>
</template>

<script setup>
import { Head } from "@inertiajs/vue3";
import SupervisorLayout from "@/Layouts/SupervisorLayout.vue";
import ModalError from "./Partials/ModalError.vue";
import ModalKonfirmasi from "./Partials/ModalKonfirmasi.vue";
import ConfirmBatalModal from "@/Components/Modals/ConfirmBatalModal.vue";
import ToastNotification from "@/Components/ToastNotification.vue";
import { ref } from "vue";
import { Link, router } from "@inertiajs/vue3";

const showErrorModal = ref(false);
const errorMessage = ref("");

const showConfirmBatal = ref(false);
const jadwalToDelete = ref(null);

const showToast = ref(false);
const toastMessage = ref("");

const props = defineProps({
    user: Object,
    users: Array,
    jadwals: Object,
    bloks: Array,
});

// Fungsi cek apakah jadwal bisa dibatalkan
function isCancelable(tanggal) {
    const today = new Date();
    today.setHours(0, 0, 0, 0); // reset jam agar hanya tanggal yang dibandingkan

    const jadwalDate = new Date(tanggal);
    jadwalDate.setHours(0, 0, 0, 0);

    // hanya boleh batal jika tanggal = hari ini atau besok
    const diffTime = jadwalDate - today;
    const diffDays = diffTime / (1000 * 60 * 60 * 24);

    return diffDays >= 0 && diffDays <= 1;
}

function openBatalModal(id) {
    jadwalToDelete.value = id;
    showConfirmBatal.value = true;
}

// Fungsi untuk membatalkan jadwal
function batalJadwal() {
    if (!jadwalToDelete.value) return;

    router.delete(route("supervisor.jadwal.destroy", jadwalToDelete.value), {
        onSuccess: () => {
            showConfirmBatal.value = false;
            jadwalToDelete.value = null;

            toastMessage.value = "Jadwal berhasil dibatalkan!";
            showToast.value = true;

            setTimeout(() => (showToast.value = false), 3000);
        },
        onError: () => {
            errorMessage.value = "Gagal membatalkan jadwal.";
            showErrorModal.value = true;
        },
    });
}

const jadwalForm = ref({
    tanggal: "",
    petugas_id: "",
    blok_id: "",
    supervisor_id: props.user?.id ?? null,
});

const jadwalErrors = ref({});

function submitJadwal() {
    jadwalErrors.value = {};

    // 👇 pastikan blok_id dan petugas_id bertipe number
    jadwalForm.value.blok_id = parseInt(jadwalForm.value.blok_id);
    jadwalForm.value.petugas_id = parseInt(jadwalForm.value.petugas_id);

    router.post("/supervisor/jadwal", jadwalForm.value, {
        onError: (e) => {
            if (e.tanggal) {
                errorMessage.value = e.tanggal;
                showErrorModal.value = true;
            } else {
                jadwalErrors.value = e;
            }
        },

        onSuccess: () => {
            jadwalForm.value = {
                tanggal: "",
                petugas_id: "",
                blok_id: "",
                supervisor_id: props.user.id,
            };
        },
    });
}

//pagination
function goToPage(link) {
    if (!link.url) return;

    router.visit(link.url, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            // Bersihkan query string di URL setelah data dimuat
            window.history.replaceState({}, "", "/supervisor/penugasan");
        },
    });
}

const modalOpen = ref(false);
const modalType = ref("add");
const form = ref({ name: "", email: "", password: "" });
const errors = ref({});
const confirmDeleteId = ref(null);

function openModal(type, user = null) {
    modalType.value = type;
    modalOpen.value = true;
    errors.value = {};
    if (type === "edit" && user) {
        form.value = {
            name: user.name,
            email: user.email,
            password: "",
            id: user.id,
        };
    } else {
        form.value = { name: "", email: "", password: "" };
    }
}

function submitForm() {
    errors.value = {};
    const url =
        modalType.value === "add"
            ? "/supervisor/petugas"
            : `/supervisor/petugas/${form.value.id}`;
    const method = modalType.value === "add" ? "post" : "put";

    router[method](url, form.value, {
        onError: (e) => (errors.value = e),
        onSuccess: () => (modalOpen.value = false),
    });
}

function confirmDelete(id) {
    confirmDeleteId.value = id;
}

function deletePetugas() {
    router.delete(`/supervisor/petugas/${confirmDeleteId.value}`, {
        onSuccess: () => (confirmDeleteId.value = null),
        onError: () => {
            // Opsional: tampilkan alert atau toast
            console.error("Gagal menghapus petugas");
            confirmDeleteId.value = null;
        },
    });
}
</script>

<style>
.custom-scrollbar::-webkit-scrollbar {
    width: 8px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #4f46e5; /* bisa diganti warna favorit */
    border-radius: 8px;
    border: 2px solid transparent;
    background-clip: content-box;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background-color: #6366f1;
}
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
