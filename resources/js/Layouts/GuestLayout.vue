<template>
    <div class="min-h-screen w-full flex bg-gray-100 relative overflow-hidden">
        <!-- Background -->
        <div
            class="absolute inset-0 z-0 bg-indigo-100"
            style="
                background-image: url('https://www.toptal.com/designers/subtlepatterns/uploads/wavey-fingerprint.png');
                background-size: cover;
                background-repeat: repeat;
            "
        ></div>

        <!-- Content -->
        <div
            v-if="card"
            class="relative z-20 w-full max-w-md mx-auto my-auto bg-white shadow-lg rounded-xl p-6"
        >
            <div class="flex justify-center mb-6">
                <Link href="/">
                    <img
                        src="/images/logo-color.png"
                        alt="Logo"
                        class="h-12 w-auto"
                    />
                </Link>
            </div>
            <slot />
        </div>

        <!-- Fullscreen Mode -->
        <div v-else class="relative z-20 w-full h-full flex">
            <slot />
        </div>
    </div>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";

defineProps({
    card: {
        type: Boolean,
        default: true,
    },
});
</script>
