<template>
    <aside
        :class="[
            'fixed md:relative top-0 left-0 min-h-screen flex flex-col border-r border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 z-40',
            isMobile ? (open ? 'translate-x-0' : '-translate-x-full') : '',
        ]"
        :style="
            !isMobile
                ? {
                      width: open ? '16rem' : '5rem',
                      transition: 'width 0.3s ease',
                  }
                : {}
        "
    >
        <!-- Sidebar Header -->
        <div
            class="flex items-center px-4 h-14 bg-blue-800 dark:bg-blue-900 text-white shadow-md rounded-bl-2xl"
        >
            <div
                class="flex items-center justify-center h-10 w-10 rounded-full bg-blue-600 text-white font-bold text-xl flex-shrink-0"
            >
                🧑‍💼
            </div>
            <transition name="fade">
                <span v-if="open" class="ml-3 text-lg font-semibold truncate">
                    Petugas Panel
                </span>
            </transition>
        </div>

        <!-- Menu -->
        <nav class="flex-1 overflow-y-auto py-4 flex flex-col gap-1">
            <PetugasSidebarItem
                icon="🏠"
                label="Dashboard"
                href="/petugas/dashboard"
                :expanded="open"
            />
            <PetugasSidebarItem
                icon="📦"
                label="Titip Setoran"
                href="/petugas/titip-setoran"
                :expanded="open"
            />
        </nav>
    </aside>
</template>

<script setup>
import PetugasSidebarItem from "./PetugasSidebarItem.vue";
import { ref, onMounted } from "vue";

const props = defineProps({ open: Boolean });
const isMobile = ref(false);

onMounted(() => {
    const checkMobile = () => {
        isMobile.value = window.innerWidth < 768;
    };
    checkMobile();
    window.addEventListener("resize", checkMobile);
});
</script>

<style scoped>
/* Smooth slide for mobile */
@media (max-width: 767px) {
    aside {
        transition: transform 0.3s ease-in-out;
    }
}

/* Fade effect for sidebar text */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
