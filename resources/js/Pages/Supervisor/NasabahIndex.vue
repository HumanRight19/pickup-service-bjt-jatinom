<template>
    <SupervisorLayout :user="user" activePage="nasabah">
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
                            Data Nasabah
                        </li>
                    </ol>
                </nav>

                <!-- Header -->
                <header
                    class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4"
                >
                    <div>
                        <h1
                            class="text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight"
                        >
                            Data Nasabah
                        </h1>
                        <p class="text-gray-600 dark:text-gray-400 mt-2">
                            Kelola dan perbarui informasi nasabah pasar
                        </p>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="flex gap-2">
                        <button
                            @click="openForm()"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium shadow transition-all"
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
                                    d="M12 4v16m8-8H4"
                                />
                            </svg>
                            Tambah Nasabah
                        </button>

                        <!-- Import Excel -->
                        <label
                            for="import-file"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-green-600 hover:bg-green-700 text-white text-sm font-medium shadow cursor-pointer"
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
                                    d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M12 12v8m0 0l-4-4m4 4l4-4M12 4v8"
                                />
                            </svg>
                            Import Nasabah
                        </label>
                        <input
                            type="file"
                            id="import-file"
                            class="hidden"
                            accept=".xlsx,.xls"
                            @change="importNasabah"
                        />
                    </div>
                </header>

                <!-- Search -->
                <div class="mb-8">
                    <div
                        class="flex items-center bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-3 shadow-sm focus-within:ring-2 focus-within:ring-blue-500 transition-all"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 text-gray-500 dark:text-gray-400 mr-3"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 103.5 3.5a7.5 7.5 0 0013.15 13.15z"
                            />
                        </svg>
                        <input
                            v-model="search"
                            @input="debouncedSearch"
                            type="text"
                            placeholder="Cari nama nasabah..."
                            class="w-full bg-transparent focus:outline-none text-sm dark:text-white"
                        />
                    </div>
                </div>

                <!-- Table -->
                <section
                    class="bg-white dark:bg-gray-900 rounded-2xl shadow p-6"
                >
                    <NasabahTable
                        :nasabahs="nasabahs.data"
                        @edit="openForm"
                        @delete="confirmHapus"
                    />

                    <!-- Pagination -->
                    <div class="mt-6 flex justify-center">
                        <div class="flex gap-2">
                            <button
                                v-for="(link, i) in nasabahs.links"
                                :key="i"
                                v-html="link.label"
                                :disabled="!link.url || link.label === '...'"
                                @click="goToPage(link.label)"
                                class="px-3 py-1.5 rounded-lg text-sm"
                                :class="[
                                    link.active
                                        ? 'bg-blue-600 text-white'
                                        : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200',
                                ]"
                            />
                        </div>
                    </div>
                </section>

                <!-- Modals yang tetap dipakai -->
                <NasabahFormModal
                    v-model:show="showForm"
                    :editing="editingNasabah"
                    :blok-pasars="blokPasars"
                    @saved="refreshData"
                    :key="editingNasabah ? editingNasabah.id : 'new'"
                />

                <ModalKonfirmasi
                    :show="showModalHapus"
                    @confirm="hapusNasabah"
                    @cancel="batalHapus"
                />

                <!-- Modal Loading Import -->
                <Transition name="fade">
                    <div
                        v-if="showImportLoading"
                        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
                    >
                        <div
                            class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 w-[90%] max-w-md text-center space-y-4"
                        >
                            <div class="text-xl font-semibold text-blue-600">
                                Mengimpor data nasabah...
                            </div>
                            <div
                                class="w-full bg-gray-200 rounded-full h-4 overflow-hidden"
                            >
                                <div
                                    class="bg-blue-600 h-full transition-all duration-300"
                                    :style="{ width: importProgress + '%' }"
                                ></div>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-200">
                                Mohon tunggu, proses import sedang
                                berlangsung...
                            </p>
                        </div>
                    </div>
                </Transition>
            </div>
        </div>
        <!-- Toast Notifikasi -->
        <transition name="fade">
            <div
                v-if="notif.show"
                :class="[
                    'fixed bottom-5 right-5 px-4 py-2 text-sm rounded shadow-md',
                    notif.type === 'success'
                        ? 'bg-green-600 text-white'
                        : 'bg-red-600 text-white',
                ]"
            >
                {{ notif.message }}
            </div>
        </transition>
    </SupervisorLayout>
</template>

<script setup>
import { ref, reactive } from "vue";
import { router } from "@inertiajs/vue3";
import _ from "lodash";

import SupervisorLayout from "@/Layouts/SupervisorLayout.vue";
import NasabahTable from "./Partials/NasabahTable.vue";
import NasabahFormModal from "./Partials/NasabahFormModal.vue";
import ModalKonfirmasi from "./Partials/ModalKonfirmasi.vue";

const props = defineProps({
    user: Object,
    nasabahs: Object,
    blokPasars: Array,
    filters: Object,
});

const nasabahs = reactive({ ...props.nasabahs });

const showForm = ref(false);
const editingNasabah = ref(null);

const showModalHapus = ref(false);
const idToDelete = ref(null);

const search = ref(props.filters.search || "");
const debouncedSearch = _.debounce(() => doSearch(), 500);

const showImportLoading = ref(false);
const importProgress = ref(0);

// toast
const notif = ref({ show: false, message: "", type: "success" });

function showToast(message, type = "success", duration = 3000) {
    notif.value = { show: true, message, type };
    setTimeout(() => (notif.value.show = false), duration);
}

function importNasabah(event) {
    const file = event.target.files[0];
    if (!file) return;
    const formData = new FormData();
    formData.append("file", file);

    showImportLoading.value = true;
    importProgress.value = 0;
    let interval = setInterval(() => {
        if (importProgress.value < 90) importProgress.value += 10;
    }, 300);

    router.post("/supervisor/nasabah/import", formData, {
        onSuccess: () => {
            event.target.value = "";
            refreshData();
            showToast("Import nasabah berhasil!", "success");
        },
        onError: (err) => {
            console.error(err);
            alert("Import gagal!");
        },
        onFinish: () => {
            clearInterval(interval);
            importProgress.value = 100;
            setTimeout(() => (showImportLoading.value = false), 500);
        },
    });
}

function openForm(nasabah = null) {
    editingNasabah.value = nasabah;
    showForm.value = true;
}

function refreshData() {
    showForm.value = false;
    editingNasabah.value = null;

    router.get(
        route("supervisor.nasabah.index"),
        {},
        {
            preserveState: true,
            onSuccess: (page) => {
                Object.assign(nasabahs, page.props.nasabahs || {});
            },
            onError: () =>
                showToast("Gagal memperbarui data nasabah!", "error"),
        }
    );
}

function confirmHapus(id) {
    idToDelete.value = id;
    showModalHapus.value = true;
}

function hapusNasabah() {
    if (!idToDelete.value) return;
    router.delete(`/supervisor/nasabah/${idToDelete.value}`, {
        onSuccess: () => {
            // hapus nasabah dari reactive object langsung
            nasabahs.data = nasabahs.data.filter(
                (n) => n.id !== idToDelete.value
            );

            showModalHapus.value = false;
            idToDelete.value = null;
            showToast("Nasabah berhasil dihapus!", "success");
        },
        onError: () => showToast("Gagal menghapus nasabah!", "error"),
    });
}

function batalHapus() {
    showModalHapus.value = false;
    idToDelete.value = null;
}

function doSearch(page = 1) {
    router.post(
        route("supervisor.nasabah.index"),
        { search: search.value, page },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onSuccess: (page) => {
                Object.assign(nasabahs, page.props.nasabahs);
            },
        }
    );
}

function goToPage(label) {
    if (isNaN(label)) return;
    doSearch(Number(label));
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
</style>
