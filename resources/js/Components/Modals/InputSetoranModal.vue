<template>
    <!-- Backdrop -->
    <transition name="fade">
        <div
            v-if="open"
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
            @click.self="handleClose"
        >
            <!-- Modal -->
            <transition name="scale-fade">
                <div
                    class="bg-white dark:bg-gray-800 rounded-3xl w-80 shadow-xl overflow-hidden relative"
                >
                    <!-- Decorative top gradient -->
                    <div
                        class="absolute top-0 left-0 w-full h-16 bg-gradient-to-r from-green-400 via-green-500 to-green-600"
                    ></div>

                    <div class="p-6 pt-20 flex flex-col gap-4">
                        <h2
                            class="text-lg font-bold text-gray-800 dark:text-gray-100 text-center"
                        >
                            Input Setoran
                        </h2>

                        <!-- Input Rupiah -->
                        <div class="relative">
                            <span
                                class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-300"
                                >Rp</span
                            >
                            <input
                                v-model="formattedValue"
                                type="text"
                                placeholder="Masukkan nominal"
                                @input="formatRupiah"
                                class="w-full border border-gray-300 dark:border-gray-600 pl-10 px-3 py-2 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-gray-100 transition"
                            />
                        </div>

                        <!-- Error Message -->
                        <p
                            v-if="errorMessage"
                            class="text-red-600 dark:text-red-400 text-sm mt-1"
                        >
                            {{ errorMessage }}
                        </p>

                        <!-- Feedback -->
                        <transition name="slide-fade">
                            <p
                                v-if="submitted"
                                class="text-green-600 dark:text-green-400 text-sm font-semibold text-center"
                            >
                                ✅ Sudah setor!
                            </p>
                        </transition>

                        <!-- Buttons -->
                        <div class="flex justify-between gap-4 mt-2">
                            <button
                                @click="handleClose"
                                class="flex-1 flex items-center justify-center gap-2 px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl transition text-sm"
                            >
                                <XMarkIcon class="w-4 h-4" /> Batal
                            </button>

                            <button
                                @click="handleSubmit"
                                :disabled="isSubmitting"
                                class="flex-1 flex items-center justify-center gap-2 px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-xl transition text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <CheckIcon class="w-4 h-4" /> Simpan
                            </button>
                        </div>
                    </div>
                </div>
            </transition>
        </div>
    </transition>
</template>

<script setup>
import { ref, watch } from "vue";
import { XMarkIcon, CheckIcon } from "@heroicons/vue/24/solid";

const props = defineProps({
    modelValue: Number, // nilai dari parent
    open: Boolean, // kontrol buka/tutup modal dari parent
});

const emit = defineEmits(["update:modelValue", "close", "submit"]);

const value = ref(props.modelValue || 0);
const formattedValue = ref(formatToRupiah(value.value));
const submitted = ref(false);
const isSubmitting = ref(false);

const errorMessage = ref(""); // untuk menyimpan pesan error

// Reset modal
function resetModal() {
    value.value = 0;
    formattedValue.value = "";
    submitted.value = false;
    isSubmitting.value = false;
    errorMessage.value = ""; // reset error
}

// Watch saat parent buka modal
watch(
    () => props.open,
    (val) => {
        if (val) resetModal();
    }
);

// Emit update ke parent
watch(value, (v) => emit("update:modelValue", v));

// Format rupiah saat input
function formatRupiah() {
    const numericValue = formattedValue.value.replace(/\D/g, "");
    value.value = Number(numericValue);
    formattedValue.value = formatToRupiah(numericValue);

    // Validasi nominal minimal 100 saat input
    if (value.value < 100) {
        errorMessage.value = "Nominal minimal Rp100";
    } else {
        errorMessage.value = "";
    }
}

// Helper konversi ke format rupiah
function formatToRupiah(val) {
    if (!val) return "";
    return Number(val).toLocaleString("id-ID");
}

// Submit ke parent
function handleSubmit() {
    if (!value.value || value.value < 100) {
        errorMessage.value = "Nominal minimal Rp100";
        return;
    }

    isSubmitting.value = true;

    // Emit event submit ke parent, parent akan simpan setoran
    emit("submit", value.value, (success) => {
        isSubmitting.value = false;
        if (success) {
            submitted.value = true;
            setTimeout(() => handleClose(), 1500);
        }
    });
}

// Tutup modal
function handleClose() {
    emit("close");
    resetModal();
}
</script>

<style scoped>
/* Animasi backdrop fade */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Animasi modal scale + fade */
.scale-fade-enter-active {
    transition: transform 0.3s ease, opacity 0.3s ease;
}
.scale-fade-enter-from {
    transform: scale(0.8);
    opacity: 0;
}
.scale-fade-leave-active {
    transition: transform 0.2s ease, opacity 0.2s ease;
}
.scale-fade-leave-to {
    transform: scale(0.8);
    opacity: 0;
}

/* Animasi feedback slide + fade */
.slide-fade-enter-active {
    transition: all 0.4s ease;
}
.slide-fade-enter-from {
    opacity: 0;
    transform: translateY(-10px);
}
.slide-fade-leave-active {
    transition: all 0.2s ease;
}
.slide-fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
