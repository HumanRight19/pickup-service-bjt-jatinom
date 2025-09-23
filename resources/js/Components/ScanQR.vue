<template>
    <div class="scan-container">
        <div id="reader" style="width: 300px"></div>

        <!-- Tombol batal overlay -->
        <!-- <div class="mt-3 text-center">
            <button
                @click="cancelScan"
                class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 transition"
            >
                Batal
            </button>
        </div> -->
    </div>
</template>

<script setup>
import { onMounted, onBeforeUnmount } from "vue";
import { Html5QrcodeScanner } from "html5-qrcode";

let scannerInstance = null;

// Emit ke parent kalau scan berhasil/gagal
const emit = defineEmits(["scan-success", "scan-fail", "cancel-scan"]);

const onScanSuccess = (decodedText) => {
    emit("scan-success", decodedText); // kirim token/uuid ke parent
    stopScanner(); // stop kamera setelah berhasil
};

const onScanError = (errMsg) => {
    console.log("Scan error:", errMsg);
};

// Start scanner
const startScanner = () => {
    if (!scannerInstance) {
        const config = { fps: 10, qrbox: { width: 250, height: 250 } };
        scannerInstance = new Html5QrcodeScanner("reader", config, false);
        scannerInstance.render(onScanSuccess, onScanError);
    }
};

// Stop scanner
function stopScanner() {
    if (scannerInstance) {
        scannerInstance.clear().catch(() => {});
        scannerInstance = null;
    }
}

function cancelScan() {
    stopScanner();
    emit("cancel-scan");
}

// Lifecycle
onMounted(() => {
    startScanner();
});
onBeforeUnmount(() => {
    stopScanner();
});

// Expose supaya bisa dipanggil parent
defineExpose({ stopScanner });
</script>

<style scoped>
.scan-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}
</style>
