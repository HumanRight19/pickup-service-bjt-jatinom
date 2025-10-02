<template>
    <!-- Overlay Form -->
    <Transition name="fade">
        <div
            v-if="show"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
            @click.self="$emit('update:show', false)"
        >
            <div
                class="bg-white/80 dark:bg-gray-900/80 backdrop-blur-md p-8 rounded-2xl w-full max-w-md shadow-2xl border border-gray-200/30 dark:border-gray-700/30 animate-fadeIn"
            >
                <!-- Judul -->
                <h2
                    class="text-2xl font-semibold mb-6 text-gray-800 dark:text-gray-100 tracking-wide"
                >
                    {{ editing ? "Edit" : "Tambah" }} Nasabah
                </h2>

                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Nama -->
                    <div>
                        <label
                            class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300"
                        >
                            Nama
                        </label>
                        <input
                            v-model="form.nama"
                            type="text"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-800/70 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                        />
                        <p v-if="errors.nama" class="text-sm text-red-500 mt-1">
                            {{ errors.nama[0] }}
                        </p>
                    </div>

                    <!-- Nama Umplung -->
                    <div>
                        <label
                            class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300"
                        >
                            Nama Umplung
                        </label>
                        <input
                            v-model="form.nama_umplung"
                            type="text"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-800/70 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                        />
                        <p
                            v-if="errors.nama_umplung"
                            class="text-sm text-red-500 mt-1"
                        >
                            {{ errors.nama_umplung[0] }}
                        </p>
                    </div>

                    <!-- Nomor Rekening -->
                    <div>
                        <label
                            class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300"
                        >
                            Nomor Rekening
                        </label>
                        <input
                            v-model="form.nomor_rekening"
                            @input="validateNumber('nomor_rekening')"
                            type="text"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-800/70 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                        />
                        <p
                            v-if="errors.nomor_rekening"
                            class="text-sm text-red-500 mt-1"
                        >
                            {{
                                Array.isArray(errors.nomor_rekening)
                                    ? errors.nomor_rekening[0]
                                    : errors.nomor_rekening
                            }}
                        </p>
                    </div>

                    <!-- Alamat -->
                    <div>
                        <label
                            class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300"
                        >
                            Alamat
                        </label>
                        <input
                            v-model="form.alamat"
                            type="text"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-800/70 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                        />
                        <p
                            v-if="errors.alamat"
                            class="text-sm text-red-500 mt-1"
                        >
                            {{ errors.alamat[0] }}
                        </p>
                    </div>

                    <!-- Nomor HP -->
                    <div>
                        <label
                            class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300"
                        >
                            Nomor HP
                        </label>
                        <input
                            v-model="form.nomor_hp"
                            @input="validateNumber('nomor_hp')"
                            type="text"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-800/70 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                        />
                        <p
                            v-if="errors.nomor_hp"
                            class="text-sm text-red-500 mt-1"
                        >
                            {{ errors.nomor_hp[0] }}
                        </p>
                    </div>

                    <!-- Blok Pasar -->
                    <div>
                        <label
                            class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300"
                        >
                            Blok Pasar
                        </label>
                        <select
                            v-model="form.blok_pasar_id"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-800/70 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                        >
                            <option value="">Pilih Blok</option>
                            <option
                                v-for="blok in blokPasars"
                                :key="blok.id"
                                :value="blok.id"
                            >
                                {{ blok.nama_blok }}
                            </option>
                        </select>
                        <p
                            v-if="errors.blok_pasar_id"
                            class="text-sm text-red-500 mt-1"
                        >
                            {{ errors.blok_pasar_id[0] }}
                        </p>
                    </div>

                    <!-- Tombol -->
                    <div class="flex justify-end gap-3 pt-4">
                        <button
                            type="button"
                            @click="$emit('update:show', false)"
                            class="px-5 py-2.5 rounded-xl bg-gray-200/70 dark:bg-gray-700/70 dark:text-white hover:bg-gray-300 dark:hover:bg-gray-600 transition-all duration-200"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="isSubmitDisabled"
                            class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-blue-500 text-white hover:from-blue-700 hover:to-blue-600 transition-all duration-200 shadow-md disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Transition>

    <!-- Modal Duplicate -->
    <transition name="fade-scale">
        <div
            v-if="showDuplicateModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
        >
            <div
                class="relative w-full max-w-md p-8 rounded-3xl shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border border-gray-200/40 dark:border-gray-700/40 text-center animate-pop"
            >
                <!-- Close Button -->
                <button
                    @click="showDuplicateModal = false"
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition"
                >
                    ✕
                </button>

                <!-- Animated Icon -->
                <div
                    class="mx-auto mb-5 w-16 h-16 flex items-center justify-center rounded-2xl bg-gradient-to-r from-red-500 to-pink-500 text-white shadow-lg animate-bounce-slow"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-8 w-8"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </div>

                <!-- Title -->
                <h2
                    class="text-2xl font-extrabold text-gray-800 dark:text-white mb-3"
                >
                    Nasabah Sudah Ada
                </h2>

                <!-- Message -->
                <p class="text-gray-600 dark:text-gray-300 mb-2 text-base">
                    Nomor rekening atau nama nasabah sudah terdaftar sebelumnya.
                </p>

                <!-- Buttons -->
                <div class="flex justify-center gap-3 mt-6">
                    <button
                        @click="showDuplicateModal = false"
                        class="px-6 py-2.5 rounded-xl font-semibold bg-gradient-to-r from-red-500 to-pink-600 text-white shadow-md hover:shadow-lg hover:scale-105 transition-transform duration-200"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </transition>

    <!-- Toast -->
    <Transition name="fade">
        <div
            v-if="toast.show"
            class="fixed top-5 right-5 z-50 bg-green-600 text-white px-5 py-3 rounded-xl shadow-lg"
        >
            {{ toast.message }}
        </div>
    </Transition>
</template>

<script setup>
import { reactive, ref, watch, computed } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    show: Boolean,
    editing: Object,
    blokPasars: Array,
});
const emit = defineEmits(["update:show", "saved"]);

const form = reactive({
    nama: "",
    nama_umplung: "",
    nomor_rekening: "",
    alamat: "",
    nomor_hp: "",
    blok_pasar_id: "",
});

const errors = reactive({});
const showDuplicateModal = ref(false);
const toast = reactive({ show: false, message: "" });

// simpan data asli (buat dirty check saat edit)
const originalData = ref({});

function toastSuccess(msg) {
    toast.message = msg;
    toast.show = true;
    setTimeout(() => (toast.show = false), 3000);
}

// Prefill form saat editing
watch(
    () => props.editing,
    (val) => {
        if (val) {
            Object.assign(form, val);
            originalData.value = { ...val }; // simpan data awal
        } else {
            Object.keys(form).forEach((key) => (form[key] = ""));
            originalData.value = { ...form }; // reset originalData
        }
        Object.keys(errors).forEach((key) => (errors[key] = ""));
    },
    { immediate: true }
);

// Validasi angka
function validateNumber(field) {
    if (!/^\d*$/.test(form[field])) {
        errors[field] = [
            `${
                field === "nomor_rekening" ? "Nomor rekening" : "Nomor HP"
            } harus berupa angka!`,
        ];
    } else {
        delete errors[field];
    }
}

function submit() {
    Object.keys(errors).forEach((key) => (errors[key] = ""));
    const method = props.editing ? "put" : "post";
    const url = props.editing
        ? `/supervisor/nasabah/${props.editing.id}` // PUT untuk edit
        : "/supervisor/nasabah/create"; // POST untuk create

    router[method](url, form, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            emit("update:show", false);
            emit("saved");
        },
        onError: (err) => {
            Object.assign(errors, err);
            if (
                errors.nomor_rekening?.includes(
                    "Nomor rekening sudah terdaftar!"
                )
            ) {
                showDuplicateModal.value = true;
            }
        },
    });
}

// 🔹 Gabungan: cek error, cek field wajib kosong, cek dirty state
const isSubmitDisabled = computed(() => {
    // kalau ada error → disable
    if (Object.keys(errors).length > 0) return true;

    // field wajib kosong?
    const requiredFields = [
        "nama",
        "nama_umplung",
        "nomor_rekening",
        "alamat",
        "nomor_hp",
        "blok_pasar_id",
    ];
    for (let field of requiredFields) {
        if (!form[field] || form[field].toString().trim() === "") return true;
    }

    // kalau edit → cek dirty state
    if (props.editing) {
        return JSON.stringify(form) === JSON.stringify(originalData.value);
    }

    return false; // create mode & semua valid → aktif
});
</script>

<style>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
.fade-scale-enter-active,
.fade-scale-leave-active {
    transition: all 0.3s ease;
}
.fade-scale-enter-from {
    opacity: 0;
    transform: scale(0.85);
}
.fade-scale-leave-to {
    opacity: 0;
    transform: scale(0.9);
}

@keyframes pop {
    0% {
        transform: scale(0.9);
        opacity: 0;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}
.animate-pop {
    animation: pop 0.25s ease-out;
}

@keyframes bounce-slow {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-6px);
    }
}
.animate-bounce-slow {
    animation: bounce-slow 2s infinite;
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
