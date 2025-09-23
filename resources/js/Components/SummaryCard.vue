<template>
    <div
        class="bg-white border-l-4 shadow rounded-lg p-4 flex items-center"
        :class="warnaBorder"
    >
        <div class="mr-4 text-3xl" :class="warnaIcon">
            <slot name="icon">📊</slot>
        </div>
        <div>
            <p
                class="text-sm font-semibold text-gray-600 uppercase tracking-wide"
            >
                {{ title }}
            </p>
            <p class="text-xl font-bold text-gray-800">{{ value }}</p>
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: String,
    value: [String, Number],
    warna: {
        type: String,
        default: "blue", // bisa: blue, green, yellow, red, indigo
    },
});

const warnaIcon = {
    blue: "text-blue-500",
    green: "text-green-500",
    yellow: "text-yellow-500",
    red: "text-red-500",
    indigo: "text-indigo-500",
}[__props.warna];

const warnaBorder = {
    blue: "border-blue-500",
    green: "border-green-500",
    yellow: "border-yellow-500",
    red: "border-red-500",
    indigo: "border-indigo-500",
}[__props.warna];
</script>
