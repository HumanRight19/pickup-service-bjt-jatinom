<template>
    <div class="flex flex-col items-center gap-4">
        <!-- Kamera Preview -->
        <div
            id="reader"
            class="relative rounded-3xl overflow-hidden shadow-2xl border border-white/20 bg-white/10 backdrop-blur-md w-[320px] h-[320px]"
        >
            <!-- Overlay laser -->
            <div v-if="isScanning" class="absolute inset-0 pointer-events-none">
                <div class="scanner-laser"></div>
                <div class="laser-glow"></div>
            </div>

            <!-- Overlay loading -->
            <div
                v-if="!isScanning"
                class="absolute inset-0 flex flex-col items-center justify-center text-gray-300 dark:text-gray-400 bg-gradient-to-br from-gray-900/60 to-gray-700/60 backdrop-blur-md"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-10 w-10 mb-2 opacity-70"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M3 4a1 1 0 011-1h3m12 0h2a1 1 0 011 1v3m0 12a1 1 0 01-1 1h-3m-12 0H4a1 1 0 01-1-1v-3M4 8h16M8 4v16"
                    />
                </svg>
                <p class="text-sm tracking-wide">Mengaktifkan kamera...</p>
            </div>

            <!-- Tombol switch kamera -->
            <button
                v-if="cameras.length > 1 && isScanning"
                @click="switchCamera"
                class="absolute top-3 right-3 bg-white/20 hover:bg-white/30 backdrop-blur-md rounded-full p-2 transition-all"
                title="Ganti Kamera"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="w-5 h-5 text-white"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14H6a2 2 0 01-2-2m0 0a2 2 0 012-2h12a2.032 2.032 0 011.595-.595L20 7h-5"
                    />
                </svg>
            </button>
        </div>

        <!-- Dropdown Kamera -->
        <div class="w-full">
            <label class="text-sm text-gray-400 mb-1 block">Pilih Kamera</label>
            <select
                v-model="selectedCamera"
                @change="startScan"
                class="w-full p-2 rounded-lg border border-gray-700 bg-gray-900/70 text-gray-100 focus:ring-2 focus:ring-cyan-400 outline-none transition"
            >
                <option disabled value="">-- Pilih Kamera --</option>
                <option v-for="cam in cameras" :key="cam.id" :value="cam.id">
                    {{ cam.label || `Kamera ${cam.id}` }}
                </option>
            </select>
        </div>

        <!-- Tombol Aksi -->
        <div class="flex gap-2 w-full">
            <button
                v-if="!isScanning"
                @click="startScan"
                class="flex-1 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-cyan-500 hover:opacity-90 text-white font-medium transition-all shadow-md"
            >
                Mulai Scan
            </button>

            <button
                v-else
                @click="stopScan"
                class="flex-1 py-2.5 rounded-xl bg-gradient-to-r from-red-600 to-rose-500 hover:opacity-90 text-white font-medium transition-all shadow-md"
            >
                Stop Scan
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from "vue";
import { Html5Qrcode } from "html5-qrcode";

const emit = defineEmits(["scan-success", "scan-fail"]);

const html5QrCode = ref(null);
const cameras = ref([]);
const selectedCamera = ref("");
const isScanning = ref(false);

async function loadCameras() {
    try {
        const devices = await Html5Qrcode.getCameras();
        cameras.value = devices;
        if (devices.length > 0) {
            // pilih otomatis kamera belakang
            const backCam =
                devices.find((cam) =>
                    cam.label.toLowerCase().includes("back")
                ) || devices[0];
            selectedCamera.value = backCam.id;
            await startScan();
        }
    } catch (e) {
        console.error("Gagal memuat kamera:", e);
    }
}

async function startScan() {
    if (!selectedCamera.value) return;

    await stopScan(); // pastikan instance lama berhenti

    // --- fix: recreate elemen #reader ---
    const readerElem = document.getElementById("reader");
    if (readerElem) {
        const newReader = readerElem.cloneNode(false);
        readerElem.parentNode.replaceChild(newReader, readerElem);
    }

    html5QrCode.value = new Html5Qrcode("reader");
    isScanning.value = true;

    try {
        await html5QrCode.value.start(
            { deviceId: { exact: selectedCamera.value } },
            { fps: 10, qrbox: 250 },
            (decodedText) => {
                emit("scan-success", decodedText);
                stopScan();
            },
            (err) => console.warn("Scan error:", err)
        );
    } catch (err) {
        console.error("Gagal mulai scan:", err);
        isScanning.value = false;
    }
}

async function stopScan() {
    if (html5QrCode.value && isScanning.value) {
        await html5QrCode.value.stop().catch(() => {});
        await html5QrCode.value.clear().catch(() => {});
        html5QrCode.value = null;
    }
    isScanning.value = false;
}

async function switchCamera() {
    if (!cameras.value.length) return;
    const idx = cameras.value.findIndex((c) => c.id === selectedCamera.value);
    const nextCam = cameras.value[(idx + 1) % cameras.value.length];
    selectedCamera.value = nextCam.id;
    await startScan();
}

onMounted(() => loadCameras());
onBeforeUnmount(() => stopScan());
</script>

<style scoped>
.scanner-laser {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 2px;
    background: linear-gradient(90deg, transparent, #00f0ff, transparent);
    animation: laser-scan 2s linear infinite;
}
.laser-glow {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 2px;
    background: rgba(0, 255, 255, 0.2);
    box-shadow: 0 0 20px 5px rgba(0, 255, 255, 0.4);
    animation: laser-scan 2s linear infinite;
}
@keyframes laser-scan {
    0% {
        top: 0%;
    }
    50% {
        top: 95%;
    }
    100% {
        top: 0%;
    }
}
</style>
