<template>
    <transition name="fade">
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
            @click.self="$emit('cancel-scan')"
        >
            <div
                class="relative bg-white dark:bg-gray-900 p-6 md:p-8 rounded-3xl shadow-2xl w-[90%] max-w-md text-gray-800 dark:text-gray-100 border border-gray-200/20 dark:border-gray-700/50 transition-all duration-300"
            >
                <!-- Header -->
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-xl font-semibold tracking-tight">
                        Scan QR Nasabah
                    </h2>
                    <button
                        @click="$emit('cancel-scan')"
                        class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                        title="Tutup"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>

                <!-- Scan Component -->
                <ScanQR @scan-success="onScanSuccess" @scan-fail="onScanFail" />

                <!-- Footer -->
                <div class="mt-6">
                    <button
                        @click="$emit('cancel-scan')"
                        class="w-full py-2.5 rounded-xl bg-gray-600 hover:bg-gray-700 text-white font-medium transition duration-300"
                    >
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </transition>
</template>

<script setup>
import axios from "axios";
import ScanQR from "@/Components/ScanQR.vue";

const emit = defineEmits(["nasabah-found", "nasabah-not-found", "cancel-scan"]);

const props = defineProps({
    mode: { type: String, default: "reguler" },
});

async function onScanSuccess(tokenOrObj) {
    try {
        let token = null;

        if (typeof tokenOrObj === "object") {
            token = tokenOrObj.uuid ?? tokenOrObj.id ?? null;
        } else if (typeof tokenOrObj === "string") {
            const match = decodeURIComponent(tokenOrObj.trim()).match(
                /[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}/
            );
            if (!match) return emit("nasabah-not-found", "QR tidak valid");
            token = match[0];
        }

        if (!token) return emit("nasabah-not-found", "QR tidak valid");

        const url =
            props.mode === "titip"
                ? route("petugas.titipsetoran.byQr", { token })
                : `/petugas/nasabah/by-qr/${token}`;

        const res = await axios.get(url);
        const nasabah = res.data?.data || res.data?.nasabah || res.data || null;

        if (nasabah) emit("nasabah-found", nasabah);
        else emit("nasabah-not-found", "Nasabah tidak ditemukan!");
    } catch {
        emit("nasabah-not-found", "QR tidak valid / nasabah tidak ditemukan");
    }
}

function onScanFail() {
    emit("nasabah-not-found");
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
