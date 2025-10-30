<template>
    <aside
        :class="[
            'transition-all duration-300 z-40 fixed md:relative top-0 left-0 h-screen md:h-auto flex flex-col shrink-0 border-r border-gray-200 dark:border-gray-800',
            open ? 'w-64' : 'w-20',
            'bg-gray-50 dark:bg-gray-950',
        ]"
    >
        <!-- Header -->
        <div
            class="flex items-center px-4 h-14 bg-blue-800 dark:bg-blue-900 text-white shadow-md rounded-bl-2xl"
        >
            <div
                class="flex items-center justify-center h-10 w-10 rounded-full bg-blue-600 text-white font-bold text-xl flex-shrink-0"
            >
                📚
            </div>

            <transition name="fade">
                <span v-if="open" class="ml-3 text-lg font-semibold truncate">
                    Pick Up Service
                </span>
            </transition>
        </div>

        <!-- Menu modernized -->
        <nav
            class="flex-1 overflow-y-auto py-5 flex flex-col gap-1 px-2 text-gray-700 dark:text-gray-200"
        >
            <SidebarItem
                icon="home"
                label="Dashboard"
                href="/supervisor/dashboard"
                :expanded="open"
            />
            <SidebarItem
                icon="calendarcheck"
                label="Penugasan Harian"
                href="/supervisor/penugasan"
                :expanded="open"
            />
            <SidebarItem
                icon="users"
                label="Nasabah"
                href="/supervisor/nasabah"
                :expanded="open"
            />
            <SidebarItem
                icon="grid"
                label="Blok Pasar"
                href="/supervisor/blok"
                :expanded="open"
            />
            <SidebarItem
                icon="bookopentext"
                label="Laporan Lengkap"
                href="/supervisor/laporan"
                :expanded="open"
            />
            <SidebarItem
                icon="banknote"
                label="Batal / Update Setoran"
                href="/supervisor/setoran-requests"
                :expanded="open"
            />
        </nav>
    </aside>
</template>

<script setup>
import SidebarItem from "./SidebarItem.vue";
defineProps({
    open: Boolean,
});
</script>

<style>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
