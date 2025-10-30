<template>
    <a
        :href="href"
        class="group flex items-center transition-all duration-300 rounded-xl px-3.5 py-3 cursor-pointer select-none hover:bg-blue-100/80 dark:hover:bg-blue-800/60 hover:text-blue-600 dark:hover:text-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-400/70"
        :class="expanded ? 'justify-start gap-3' : 'justify-center'"
    >
        <!-- Icon dengan background bulat (selalu lingkaran) -->
        <div
            class="flex items-center justify-center w-10 h-10 flex-shrink-0 rounded-full bg-blue-100 dark:bg-blue-900/60 text-blue-600 dark:text-blue-300 transition-all duration-300 group-hover:bg-blue-200 dark:group-hover:bg-blue-800"
        >
            <component
                :is="iconComponent"
                class="w-6 h-6 transition-transform duration-300 group-hover:scale-110"
            />
        </div>

        <!-- Label -->
        <span
            v-if="expanded"
            class="text-base font-semibold tracking-wide transition-colors duration-300 dark:text-white"
        >
            {{ label }}
        </span>
    </a>
</template>

<script setup>
import { computed } from "vue";
import {
    Home,
    CalendarCheck,
    Users,
    Grid,
    BookOpenText,
    Banknote,
} from "lucide-vue-next";

const props = defineProps({
    icon: String,
    label: String,
    href: String,
    expanded: Boolean,
});

const icons = {
    home: Home,
    calendarcheck: CalendarCheck,
    users: Users,
    grid: Grid,
    bookopentext: BookOpenText,
    banknote: Banknote,
};

const iconComponent = computed(() => icons[props.icon] || Home);
</script>

<style scoped>
a {
    margin-bottom: 4px;
}

/* Transisi smooth untuk sidebar mode collapse */
a > div {
    transition: all 0.3s ease;
}
</style>
