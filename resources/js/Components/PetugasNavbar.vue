<template>
    <header
        class="bg-blue-800 dark:bg-blue-900 text-white shadow-md px-4 py-2 flex justify-between items-center sticky top-0 z-50 transition-all"
    >
        <!-- Sidebar Toggle -->
        <button
            @click="$emit('toggleSidebar')"
            class="text-white p-2 rounded hover:bg-blue-700 dark:hover:bg-blue-800 transition"
        >
            <svg
                class="w-6 h-6"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16"
                />
            </svg>
        </button>

        <h1 class="text-lg font-bold">Petugas Panel</h1>

        <!-- Right Section -->
        <div class="flex items-center gap-4">
            <!-- Dark Mode Toggle -->
            <button
                @click="toggleDarkMode"
                class="w-12 h-6 bg-gray-300 dark:bg-gray-700 rounded-full flex items-center p-1 transition-all duration-300 shadow-inner relative"
            >
                <span
                    class="absolute w-5 h-5 flex items-center justify-center bg-white text-xs rounded-full shadow-md transform transition-transform duration-300"
                    :class="isDark ? 'translate-x-6' : 'translate-x-0'"
                >
                    <span v-if="isDark">🌙</span>
                    <span v-else>☀️</span>
                </span>
            </button>

            <!-- Profile Dropdown -->
            <div class="relative" @click.stop="dropdownOpen = !dropdownOpen">
                <button
                    class="flex items-center gap-1 text-sm sm:text-base text-white font-medium hover:text-blue-300 dark:hover:text-blue-400 transition"
                >
                    👤 {{ user.name }}
                    <svg
                        class="w-4 h-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 9l-7 7-7-7"
                        />
                    </svg>
                </button>

                <transition name="fade-scale">
                    <div
                        v-if="dropdownOpen"
                        class="absolute right-0 mt-2 w-44 bg-white dark:bg-gray-800 shadow-lg rounded-xl z-50 py-2 text-sm"
                    >
                        <a
                            :href="route('petugas.profile.edit')"
                            class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 text-black transition rounded-lg dark:text-white"
                        >
                            Edit Profile
                        </a>
                        <button
                            @click="logout"
                            class="w-full text-left px-4 py-2 text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition"
                        >
                            Logout
                        </button>
                    </div>
                </transition>
            </div>
        </div>
    </header>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({ user: Object });
const dropdownOpen = ref(false);
const isDark = ref(false);

const logout = () => {
    router.post("/logout", {}, { onFinish: () => location.reload() });
};

const toggleDarkMode = () => {
    isDark.value = !isDark.value;
    document.documentElement.classList.toggle("dark", isDark.value);
    localStorage.setItem("theme", isDark.value ? "dark" : "light");
};

onMounted(() => {
    const savedTheme = localStorage.getItem("theme");
    isDark.value = savedTheme === "dark";
    if (isDark.value) document.documentElement.classList.add("dark");

    document.addEventListener("click", (e) => {
        if (!e.target.closest(".relative")) dropdownOpen.value = false;
    });
});
</script>

<style>
.fade-scale-enter-active,
.fade-scale-leave-active {
    transition: all 0.2s ease;
}
.fade-scale-enter-from {
    opacity: 0;
    transform: scale(0.95);
}
.fade-scale-enter-to {
    opacity: 1;
    transform: scale(1);
}
.fade-scale-leave-from {
    opacity: 1;
    transform: scale(1);
}
.fade-scale-leave-to {
    opacity: 0;
    transform: scale(0.95);
}
</style>
