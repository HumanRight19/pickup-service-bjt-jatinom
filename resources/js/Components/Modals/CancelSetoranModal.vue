<template>
    <transition name="fade">
        <div
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
        >
            <transition name="scale-fade">
                <div
                    class="bg-white dark:bg-gray-800 rounded-3xl w-80 shadow-xl overflow-hidden relative"
                >
                    <!-- Decorative top gradient -->
                    <div
                        class="absolute top-0 left-0 w-full h-16 bg-gradient-to-r from-red-400 via-red-500 to-red-600"
                    ></div>

                    <div class="p-6 pt-20 flex flex-col gap-4">
                        <h2
                            class="text-lg font-bold text-gray-800 dark:text-gray-100 text-center"
                        >
                            Batalkan Setoran
                        </h2>

                        <textarea
                            v-model="reason"
                            placeholder="Masukkan alasan batal..."
                            class="w-full border border-gray-300 dark:border-gray-600 px-3 py-2 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-gray-100 transition resize-none"
                            rows="3"
                            @keyup.enter="handleConfirm"
                            :disabled="loading"
                        ></textarea>

                        <div class="flex justify-between gap-4 mt-2">
                            <button
                                @click="handleClose"
                                class="flex-1 flex items-center justify-center gap-2 px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl transition text-sm"
                                :disabled="loading"
                            >
                                <XMarkIcon class="w-4 h-4" /> Batal
                            </button>

                            <button
                                @click="handleConfirm"
                                class="flex-1 flex items-center justify-center gap-2 px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-xl transition text-sm"
                                :disabled="loading"
                            >
                                <CheckIcon class="w-4 h-4" />
                                <span v-if="!loading">Ajukan</span>
                                <span v-else>Memproses...</span>
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
    modelValue: String,
});
const emit = defineEmits(["update:modelValue", "close", "confirm"]);

const reason = ref(props.modelValue || "");
const loading = ref(false);

// Sinkronisasi modelValue dengan parent
watch(
    () => props.modelValue,
    (v) => {
        reason.value = v || "";
    }
);
watch(reason, (v) => emit("update:modelValue", v));

// Emit confirm ke parent dengan loading
function handleConfirm() {
    if (!reason.value || !reason.value.trim()) {
        alert("Masukkan alasan batal dulu!");
        return;
    }

    loading.value = true;

    // Emit ke parent, parent akan men-trigger confirmCancel()
    // Parent harus reset loading setelah selesai
    emit("confirm", reason.value.trim(), () => {
        loading.value = false;
        reason.value = "";
    });
}

// Tutup modal
function handleClose() {
    if (loading.value) return; // tidak bisa close saat loading
    reason.value = "";
    emit("update:modelValue", "");
    emit("close");
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
</style>
