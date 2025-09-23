<template>
    <a
        :href="href"
        :class="[
            'flex items-center transition-colors duration-200 rounded-xl px-4 py-3 hover:bg-blue-200 dark:hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500',
            expanded ? 'justify-start' : 'justify-center',
        ]"
    >
        <!-- Icon -->
        <span class="text-xl flex-shrink-0">{{ icon }}</span>

        <!-- Label -->
        <span
            v-if="expanded"
            class="ml-3 text-md font-semibold dark:text-white"
        >
            {{ label }}
        </span>
    </a>
</template>

<script setup>
defineProps({
    icon: String,
    label: String,
    href: String,
    expanded: Boolean,
});
</script>
