<template>
    <div
        class="flex min-h-screen bg-gray-100 dark:bg-gray-900 overflow-x-hidden"
    >
        <!-- Sidebar -->
        <Sidebar v-if="Sidebar" :open="sidebarOpen" @toggle="toggleSidebar" />

        <!-- Overlay Mobile -->
        <div
            v-if="sidebarOpen && isMobile"
            class="fixed inset-0 bg-black bg-opacity-50 z-30"
            @click="sidebarOpen = false"
        ></div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0 transition-all duration-300">
            <Navbar
                v-if="Navbar"
                :user="user ?? {}"
                :active-page="activePage"
                @toggleSidebar="toggleSidebar"
                class="fixed top-0 left-0 right-0 z-30"
            />
            <main class="flex-1 overflow-y-auto p-4">
                <slot />
            </main>
        </div>

        <!-- Toast Notification -->
        <div class="fixed top-6 right-6 z-50 flex flex-col gap-4 max-w-sm">
            <transition-group name="fade" tag="div">
                <div
                    v-for="(notif, index) in notifikasi"
                    :key="index"
                    class="relative flex items-start gap-3 bg-white dark:bg-gray-800 shadow-lg border-l-4 border-blue-600 p-4 rounded-lg animate-slideInRight"
                >
                    <div class="text-blue-600 text-xl">🔔</div>
                    <div class="flex-1">
                        <p
                            class="text-sm text-gray-800 dark:text-white font-medium"
                        >
                            {{ notif.pesan }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ notif.waktu }}
                        </p>
                    </div>
                    <button
                        @click="closeNotif(index)"
                        class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 dark:hover:text-white"
                    >
                        ❌
                    </button>
                </div>
            </transition-group>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import Navbar from "@/Components/Navbar.vue";
import Sidebar from "@/Components/Sidebar.vue";
import "@/plugins/echo";

// Props
const props = defineProps({
    user: Object, // bisa null
    activePage: {
        type: String,
        default: "", // default aman
    },
});

// Refs
const sidebarOpen = ref(true);
const isMobile = ref(false);
const notifikasi = ref([]);
const audio = new Audio("/sounds/message.mp3");

// Optional references supaya komponen tidak error
const NavbarComponent = Navbar ?? null;
const SidebarComponent = Sidebar ?? null;

// Fungsi toggle sidebar
const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

// Fungsi close notifikasi
const closeNotif = (index) => {
    notifikasi.value.splice(index, 1);
};

onMounted(() => {
    // Deteksi mobile
    const checkMobile = () => {
        isMobile.value = window.innerWidth < 768;
    };
    checkMobile();
    window.addEventListener("resize", checkMobile);

    // Notifikasi real-time
    window.Echo.channel("setoran").listen("SetoranBaru", (e) => {
        notifikasi.value.unshift({
            pesan: `${e.nama_nasabah} baru saja setor.`,
            waktu: new Date().toLocaleTimeString(),
        });

        audio.play().catch((err) => {
            console.warn("Audio gagal diputar:", err);
        });

        setTimeout(() => {
            notifikasi.value.pop();
        }, 5000);
    });

    // Proteksi jika user logout
    if (!props.user) {
        router.visit("/login");
    }
});
</script>
