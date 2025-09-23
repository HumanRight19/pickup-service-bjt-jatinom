<template>
    <Teleport to="body">
        <transition name="fade">
            <div
                v-if="show"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-6"
            >
                <div
                    class="bg-white dark:bg-gray-900 rounded-xl shadow-lg w-full max-w-md p-6 relative"
                >
                    <!-- Tombol close -->
                    <button
                        @click="$emit('close')"
                        class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 dark:hover:text-white"
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
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>

                    <h2
                        class="text-xl font-bold text-gray-800 dark:text-white mb-4"
                    >
                        Detail Nasabah
                    </h2>

                    <div
                        class="space-y-2 text-sm text-gray-700 dark:text-gray-300"
                    >
                        <p>
                            <span class="font-medium">Nama Umplung:</span>
                            {{ nasabah.nama_umplung || "-" }}
                        </p>
                        <p>
                            <span class="font-medium">Nama Resmi:</span>
                            {{ nasabah.nama }}
                        </p>
                        <p>
                            <span class="font-medium">Alamat:</span>
                            {{ nasabah.alamat || "-" }}
                        </p>
                        <p>
                            <span class="font-medium">Nomor HP:</span>
                            {{ nasabah.nomor_hp || "-" }}
                        </p>
                        <p>
                            <span class="font-medium">Nomor Rekening:</span>
                            {{ nasabah.nomor_rekening || "-" }}
                        </p>
                        <p>
                            <span class="font-medium">Blok Pasar:</span>
                            {{ nasabah.blok_pasar?.nama_blok || "-" }}
                        </p>
                    </div>

                    <div class="mt-5 text-right">
                        <button
                            @click="$emit('close')"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </Teleport>
</template>

<script setup>
defineProps({
    show: Boolean,
    nasabah: Object,
});

defineEmits(["close"]);
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
