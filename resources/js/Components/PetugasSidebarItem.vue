<template>
    <a
        :href="href"
        :class="[
            'flex items-center transition-all duration-200 rounded-xl px-3 py-3 hover:bg-blue-100 dark:hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500',
            expanded ? 'justify-start' : 'justify-center',
        ]"
    >
        <!-- Icon -->
        <div
            class="flex items-center justify-center w-11 h-11 rounded-full transition-all duration-300"
            :class="[
                isActive
                    ? 'bg-blue-600 text-white shadow-md'
                    : 'bg-blue-100 dark:bg-blue-800 text-blue-600 dark:text-blue-300',
            ]"
        >
            <component :is="iconComponent" class="w-6 h-6" />
        </div>

        <!-- Label -->
        <span
            v-if="expanded"
            class="ml-3 text-base font-medium dark:text-white"
        >
            {{ label }}
        </span>
    </a>
</template>

<script setup>
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import { Home, Package } from "lucide-vue-next";

const props = defineProps({
    icon: String,
    label: String,
    href: String,
    expanded: Boolean,
});

const route = usePage().props.ziggy;
const currentPath = window.location.pathname;

const isActive = computed(() => currentPath === props.href);

const iconMap = {
    home: Home,
    package: Package,
};

const iconComponent = computed(() => iconMap[props.icon] || Home);
</script>

<style scoped>
a {
    font-family: "Inter", sans-serif;
}
</style>
