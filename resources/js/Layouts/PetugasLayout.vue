<template>
    <div
        class="flex min-h-screen bg-gray-100 dark:bg-gray-900 overflow-x-hidden"
    >
        <!-- Sidebar -->
        <PetugasSidebar :open="sidebarOpen" @toggle="toggleSidebar" />

        <!-- Mobile Sidebar & Overlay -->
        <div v-if="sidebarOpen && isMobile" class="fixed inset-0 z-40 flex">
            <!-- Overlay dengan fade -->
            <transition name="fade">
                <div
                    class="fixed inset-0 bg-black bg-opacity-50"
                    @click="sidebarOpen = false"
                ></div>
            </transition>

            <!-- Sidebar dengan slide -->
            <transition name="slide">
                <PetugasSidebar
                    :open="sidebarOpen"
                    class="relative w-64 bg-white dark:bg-gray-800 shadow-lg"
                />
            </transition>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0 transition-all duration-300">
            <PetugasNavbar
                :user="user"
                @toggleSidebar="toggleSidebar"
                class="fixed top-0 left-0 right-0 z-30 transition-all duration-300"
            />
            <main class="flex-1 overflow-y-auto p-4 md:p-6 pt-20 md:pt-24">
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
import { Inertia } from "@inertiajs/inertia";
import PetugasNavbar from "@/Components/PetugasNavbar.vue";
import PetugasSidebar from "@/Components/PetugasSidebar.vue";

const props = defineProps({ user: Object, activePage: String });

const sidebarOpen = ref(true);
const isMobile = ref(false);
const notifikasi = ref([]);

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};
const closeNotif = (index) => {
    notifikasi.value.splice(index, 1);
};

onMounted(() => {
    const checkMobile = () => {
        isMobile.value = window.innerWidth < 768;
        if (!isMobile.value) sidebarOpen.value = true;
    };
    checkMobile();
    window.addEventListener("resize", checkMobile);

    if (!props.user) Inertia.visit("/login");
});
</script>

<style scoped>
/* Sidebar slide animation */
.slide-enter-active,
.slide-leave-active {
    transition: transform 0.3s ease-in-out;
}
.slide-enter-from {
    transform: translateX(-100%);
}
.slide-enter-to {
    transform: translateX(0);
}
.slide-leave-from {
    transform: translateX(0);
}
.slide-leave-to {
    transform: translateX(-100%);
}
@keyframes slideInRight {
    0% {
        transform: translateX(100%);
        opacity: 0;
    }
    100% {
        transform: translateX(0);
        opacity: 1;
    }
}
.animate-slideInRight {
    animation: slideInRight 0.3s ease-out forwards;
}
/* Overlay fade */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
.fade-enter-to,
.fade-leave-from {
    opacity: 1;
}
</style>
