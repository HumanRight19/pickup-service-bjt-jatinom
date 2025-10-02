<template>
    <transition name="fade">
        <div
            v-if="show"
            class="fixed bottom-6 right-6 bg-green-600 text-white px-4 py-3 rounded-lg shadow-lg text-sm"
        >
            {{ message }}
        </div>
    </transition>
</template>

<script setup>
defineProps({
    show: Boolean,
    message: String,
});
</script>

<style>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.4s;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
