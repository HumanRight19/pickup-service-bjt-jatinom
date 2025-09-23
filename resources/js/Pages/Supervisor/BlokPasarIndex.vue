<template>
    <SupervisorLayout :user="user" activePage="blokpasar">
        <!-- Container utama -->
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
                            Blok Pasar
                        </li>
                    </ol>
                </nav>

                <!-- Header -->
                <header class="mb-10 flex items-center justify-between">
                    <div>
                        <h1
                            class="text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight"
                        >
                            Blok Pasar
                            <span class="text-indigo-600"
                                >({{ blokPasars.length }})</span
                            >
                        </h1>
                        <p class="text-gray-600 dark:text-gray-400 mt-2">
                            Kelola daftar blok pasar yang tersedia
                        </p>
                    </div>
                </header>

                <!-- Form Tambah -->
                <div
                    class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-lg overflow-hidden mb-10 max-w-xl"
                >
                    <div
                        class="flex items-center gap-3 px-6 py-4 bg-gray-100 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700"
                    >
                        <div
                            class="w-10 h-10 rounded-full flex items-center justify-center bg-blue-100 dark:bg-blue-900"
                        >
                            <PlusIcon
                                class="w-5 h-5 text-blue-600 dark:text-blue-300"
                            />
                        </div>
                        <div>
                            <h2
                                class="text-lg font-bold text-gray-800 dark:text-white"
                            >
                                Tambah Blok Baru
                            </h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Masukkan data blok baru
                            </p>
                        </div>
                    </div>

                    <form @submit.prevent="submitTambah" class="px-6 py-5">
                        <div class="mb-6">
                            <label
                                class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300"
                                for="nama_blok"
                            >
                                Nama Blok
                            </label>
                            <input
                                v-model="formTambah.nama_blok"
                                type="text"
                                id="nama_blok"
                                class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-800 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 px-4 py-3 transition"
                                placeholder="Contoh: Blok A"
                                required
                            />
                        </div>

                        <div class="flex justify-end">
                            <button
                                type="submit"
                                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-semibold text-sm transition"
                            >
                                <PlusIcon class="w-4 h-4" />
                                Tambah Blok
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Grid Card Blok Pasar -->
                <div>
                    <h2 class="text-lg font-semibold mb-4 dark:text-white">
                        Daftar Blok
                    </h2>
                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-5"
                    >
                        <div
                            v-for="b in blokPasars"
                            :key="b.id"
                            class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 border border-gray-200 dark:border-gray-700 hover:shadow-lg transition"
                        >
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3
                                        class="text-xl font-bold text-gray-800 dark:text-white"
                                    >
                                        {{ b.nama_blok }}
                                    </h3>
                                    <p
                                        class="text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        ID: #{{ b.id }}
                                    </p>
                                </div>
                                <div class="flex gap-2">
                                    <!-- Icon Edit -->
                                    <button
                                        @click="edit(b)"
                                        class="w-9 h-9 rounded-full flex items-center justify-center bg-yellow-100 hover:bg-yellow-200 dark:bg-yellow-300/10 dark:hover:bg-yellow-300/20 transition"
                                        title="Edit"
                                    >
                                        <PencilIcon
                                            class="w-5 h-5 text-yellow-600 dark:text-yellow-400"
                                        />
                                    </button>
                                    <!-- Icon Delete -->
                                    <button
                                        @click="confirmDelete(b)"
                                        class="w-9 h-9 rounded-full flex items-center justify-center bg-red-100 hover:bg-red-200 dark:bg-red-300/10 dark:hover:bg-red-300/20 transition"
                                        title="Hapus"
                                    >
                                        <TrashIcon
                                            class="w-5 h-5 text-red-600 dark:text-red-400"
                                        />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Konfirmasi Hapus -->
                <ModalKonfirmasi
                    :show="showModal"
                    @cancel="showModal = false"
                    @confirm="hapus"
                    title="Hapus Blok"
                    :message="`Apakah kamu yakin ingin menghapus ${
                        blokToDelete?.nama_blok || 'blok ini'
                    }?`"
                />

                <!-- Modal Edit -->
                <ModalEditBlok
                    :show="showEditModal"
                    :form="formEdit"
                    @close="cancelEdit"
                    @submit="submitEdit"
                />

                <!-- Toast Notification -->
                <transition name="fade">
                    <div
                        v-if="toast"
                        class="fixed bottom-5 right-5 bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg z-50 text-sm"
                    >
                        {{ toast }}
                    </div>
                </transition>
            </div>
        </div>
    </SupervisorLayout>
</template>

<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import { PencilIcon, TrashIcon, PlusIcon } from "@heroicons/vue/24/solid";
import SupervisorLayout from "@/Layouts/SupervisorLayout.vue";
import ModalKonfirmasi from "./Partials/ModalKonfirmasi.vue";
import ModalEditBlok from "./Partials/ModalEditBlok.vue";

// Props
const props = defineProps({
    user: Object,
    blokPasars: Array,
    flash: Object,
});

// State form tambah
const formTambah = ref({
    nama_blok: "",
});

// State edit
const formEdit = ref({
    id: null,
    nama_blok: "",
});
const showEditModal = ref(false);

// Modal hapus
const showModal = ref(false);
const blokToDelete = ref(null);

// Toast
const toast = ref("");
function showToast(message) {
    toast.value = message;
    setTimeout(() => (toast.value = ""), 3000);
}

// Tambah blok
function submitTambah() {
    router.post("/supervisor/blok", formTambah.value, {
        onSuccess: () => {
            showToast("Blok berhasil ditambahkan!");
            formTambah.value = { nama_blok: "" };
        },
    });
}

// Edit blok
function edit(b) {
    formEdit.value = { ...b };
    showEditModal.value = true;
}
function submitEdit() {
    router.put(`/supervisor/blok/${formEdit.value.id}`, formEdit.value, {
        onSuccess: () => {
            showToast("Blok berhasil diperbarui!");
            cancelEdit();
        },
    });
}
function cancelEdit() {
    formEdit.value = { id: null, nama_blok: "" };
    showEditModal.value = false;
}

// Hapus blok
function confirmDelete(blok) {
    blokToDelete.value = blok;
    showModal.value = true;
}
function hapus() {
    router.delete(`/supervisor/blok/${blokToDelete.value.id}`, {
        onSuccess: () => {
            showModal.value = false;
            showToast("Blok berhasil dihapus.");
        },
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
