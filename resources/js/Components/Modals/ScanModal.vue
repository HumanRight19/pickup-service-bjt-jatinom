<template>
    <div
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 dark:text-white"
    >
        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl w-80">
            <h2 class="text-lg font-bold mb-4">Scan QR Nasabah</h2>

            <ScanQR
                @scan-success="onScanSuccess"
                @scan-fail="onScanFail"
                @cancel-scan="$emit('cancel-scan')"
            />

            <button
                @click="$emit('cancel-scan')"
                class="mt-4 w-full py-2 bg-red-600 text-white rounded hover:bg-red-700"
            >
                Batal
            </button>
        </div>
    </div>
</template>

<script setup>
import axios from "axios";
import ScanQR from "@/Components/ScanQR.vue";

const emit = defineEmits(["nasabah-found", "nasabah-not-found", "cancel-scan"]);

const props = defineProps({
    mode: { type: String, default: "reguler" }, // "reguler" atau "titip"
});

async function onScanSuccess(tokenOrObj) {
    try {
        let token = null;

        if (tokenOrObj && typeof tokenOrObj === "object") {
            token = tokenOrObj.uuid ?? tokenOrObj.id ?? null;
        } else if (typeof tokenOrObj === "string") {
            const trimmed = decodeURIComponent(tokenOrObj.trim());

            // Paksa ambil UUID saja, jangan kirim URL penuh
            const uuidMatch = trimmed.match(
                /[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}/
            );
            if (!uuidMatch) {
                emit("nasabah-not-found", "QR tidak valid");
                return;
            }
            token = uuidMatch[0]; // selalu UUID murni
        }

        if (!token) {
            emit("nasabah-not-found", "QR tidak valid");
            return;
        }

        // Tentukan endpoint
        const url =
            props.mode === "titip"
                ? route("petugas.titipsetoran.byQr", { token })
                : `/petugas/nasabah/by-qr/${token}`;

        const res = await axios.get(url);

        // Normalisasi response server
        let nasabah = null;

        if (res.data?.data) {
            nasabah = res.data.data;
        } else if (res.data?.nasabah) {
            nasabah = res.data.nasabah;
        } else if (res.data?.id && res.data?.uuid) {
            nasabah = {
                ...res.data,
                id: String(res.data.id), // pastikan konsisten string
                uuid: String(res.data.uuid), // pastikan konsisten lowercase
            };
        }

        if (nasabah) {
            emit("nasabah-found", nasabah);
        } else {
            emit(
                "nasabah-not-found",
                res.data?.message || "Nasabah tidak ditemukan!"
            );
        }
    } catch (err) {
        emit("nasabah-not-found", "QR tidak valid / nasabah tidak ditemukan");
    }
}

function onScanFail() {
    emit("nasabah-not-found");
}
</script>
