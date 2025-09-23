<template>
    <Transition name="fade-scale">
        <div
            v-if="show"
            class="fixed inset-0 bg-black/40 backdrop-blur-md flex items-center justify-center z-50 px-4"
            @click.self="closeModal"
        >
            <div
                class="bg-white/90 dark:bg-gray-900/80 backdrop-blur-lg rounded-3xl shadow-2xl w-full max-w-sm p-6 flex flex-col gap-4 border border-white/10"
            >
                <!-- Header: Icon + Title -->
                <div class="flex items-center gap-3">
                    <!-- Error Icon -->
                    <svg
                        class="w-7 h-7 text-red-500 flex-shrink-0"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v4a1 1 0 102 0V7zm-1 6a1.5 1.5 0 110-3 1.5 1.5 0 010 3z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    <h3
                        class="text-lg font-semibold text-gray-900 dark:text-gray-100"
                    >
                        Error
                    </h3>
                </div>

                <!-- Message -->
                <p
                    class="text-gray-800 dark:text-gray-200 text-base leading-relaxed break-words max-h-60 overflow-y-auto"
                >
                    {{ message }}
                </p>

                <!-- Button -->
                <div class="mt-4 flex justify-end">
                    <button
                        @click="closeModal"
                        class="px-5 py-2 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-xl shadow-lg hover:scale-105 transition-transform duration-200"
                    >
                        OK
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
const props = defineProps({
    show: Boolean,
    message: String,
});

const emit = defineEmits(["update:show"]);

function closeModal() {
    emit("update:show", false);
}
</script>

<style>
.fade-scale-enter-active,
.fade-scale-leave-active {
    transition: all 0.25s ease-out;
}
.fade-scale-enter-from,
.fade-scale-leave-to {
    opacity: 0;
    transform: scale(0.9);
}
</style>
