<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-100 dark:bg-gray-950 text-gray-800 dark:text-gray-100 p-6"
    >
        <section class="w-full max-w-2xl">
            <div
                class="relative overflow-hidden rounded-2xl shadow-2xl bg-white/80 dark:bg-gray-900/80 backdrop-blur border border-gray-200/60 dark:border-gray-800/60 animate-fadeIn"
            >
                <!-- Top gradient bar -->
                <div
                    class="h-1.5 bg-gradient-to-r from-red-500 via-amber-500 to-blue-600"
                ></div>

                <div
                    class="p-8 md:p-10 flex flex-col items-center text-center gap-6"
                >
                    <!-- Illustration -->
                    <div class="w-40 h-40 md:w-48 md:h-48">
                        <svg
                            viewBox="0 0 200 200"
                            class="w-full h-full"
                            aria-hidden="true"
                        >
                            <defs>
                                <linearGradient
                                    id="g1"
                                    x1="0"
                                    x2="1"
                                    y1="0"
                                    y2="1"
                                >
                                    <stop offset="0%" stop-color="#ef4444" />
                                    <stop offset="100%" stop-color="#f59e0b" />
                                </linearGradient>
                                <linearGradient
                                    id="g2"
                                    x1="0"
                                    x2="1"
                                    y1="1"
                                    y2="0"
                                >
                                    <stop offset="0%" stop-color="#60a5fa" />
                                    <stop offset="100%" stop-color="#22d3ee" />
                                </linearGradient>
                            </defs>
                            <circle
                                cx="100"
                                cy="100"
                                r="80"
                                fill="url(#g2)"
                                opacity="0.1"
                            />
                            <g transform="translate(50,40)">
                                <path
                                    d="M45 0L90 18v33c0 30-20 51-45 64C20 102 0 81 0 51V18L45 0Z"
                                    fill="url(#g1)"
                                />
                                <g transform="translate(27,34)">
                                    <rect
                                        x="0"
                                        y="18"
                                        width="36"
                                        height="28"
                                        rx="6"
                                        fill="white"
                                        class="dark:fill-gray-800"
                                    />
                                    <rect
                                        x="14"
                                        y="26"
                                        width="8"
                                        height="12"
                                        rx="4"
                                        fill="#ef4444"
                                    />
                                    <path
                                        d="M18 0c7 0 12 5 12 12v6h-6v-6a6 6 0 0 0-12 0v6h-6v-6C6 5 11 0 18 0Z"
                                        fill="white"
                                        class="dark:fill-gray-800"
                                    />
                                </g>
                            </g>
                            <!-- Sparkles -->
                            <circle cx="165" cy="45" r="3" fill="#22d3ee">
                                <animate
                                    attributeName="r"
                                    values="2;3;2"
                                    dur="2.2s"
                                    repeatCount="indefinite"
                                />
                            </circle>
                            <circle cx="30" cy="65" r="2.5" fill="#93c5fd">
                                <animate
                                    attributeName="r"
                                    values="2;3;2"
                                    dur="2.8s"
                                    repeatCount="indefinite"
                                />
                            </circle>
                        </svg>
                    </div>

                    <!-- Title & Message -->
                    <div>
                        <h1
                            class="text-2xl md:text-3xl font-extrabold tracking-tight text-red-600"
                        >
                            🚫 Terlalu Banyak Percobaan
                        </h1>
                        <p
                            class="mt-3 text-base md:text-lg text-gray-600 dark:text-gray-300 leading-relaxed"
                        >
                            Anda telah mencoba login terlalu sering.<br />
                            Silakan tunggu
                            <span class="font-bold text-red-500">{{
                                countdown
                            }}</span>
                            detik sebelum mencoba lagi.
                        </p>
                    </div>

                    <!-- Info Card -->
                    <div class="w-full grid sm:grid-cols-2 gap-3 text-sm">
                        <div
                            class="rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/70 dark:bg-gray-800/60 p-3 text-left"
                        >
                            <div class="text-gray-500 dark:text-gray-400">
                                Status
                            </div>
                            <div class="mt-0.5 font-semibold text-red-600">
                                Rate Limited
                            </div>
                            <div
                                class="text-xs mt-2 text-gray-500 dark:text-gray-400"
                            >
                                Waktu: {{ now }}
                            </div>
                        </div>
                        <div
                            class="rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/70 dark:bg-gray-800/60 p-3 text-left"
                        >
                            <div class="text-gray-500 dark:text-gray-400">
                                Retry Setelah
                            </div>
                            <div class="mt-0.5 font-mono font-semibold">
                                {{ retryAfter }} detik
                            </div>
                            <div
                                class="w-full bg-gray-200 dark:bg-gray-700 h-2 rounded-full mt-2"
                            >
                                <div
                                    class="h-2 bg-red-500 rounded-full transition-all duration-1000"
                                    :style="{ width: progressBarWidth + '%' }"
                                ></div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div
                        class="flex flex-col sm:flex-row gap-3 w-full justify-center mt-2"
                    >
                        <button
                            @click="$emit('close')"
                            class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-red-600 text-white font-medium hover:bg-red-700 active:opacity-90 transition disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="countdown > 0"
                        >
                            {{
                                countdown > 0
                                    ? "⏳ Tunggu..."
                                    : "✅ OK, Saya Mengerti"
                            }}
                        </button>
                        <!-- <button
                            @click="reloadPage"
                            class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                        >
                            🔄 Muat Ulang
                        </button> -->
                    </div>

                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                        Kode:
                        <span class="font-mono">429-TOO-MANY-REQUESTS</span>
                    </p>
                </div>

                <!-- Soft glow -->
                <div
                    class="pointer-events-none absolute -top-20 -right-20 w-56 h-56 rounded-full blur-3xl opacity-25"
                    style="
                        background: radial-gradient(
                            closest-side,
                            #22d3ee,
                            transparent
                        );
                    "
                ></div>
                <div
                    class="pointer-events-none absolute -bottom-24 -left-24 w-64 h-64 rounded-full blur-3xl opacity-20"
                    style="
                        background: radial-gradient(
                            closest-side,
                            #f59e0b,
                            transparent
                        );
                    "
                ></div>
            </div>
        </section>
    </div>
</template>

<script setup>
import { ref, watch, onBeforeUnmount } from "vue";

const props = defineProps({
    show: Boolean,
    retryAfter: { type: Number, default: 60 },
});

const emit = defineEmits(["close"]);

const countdown = ref(props.retryAfter);
const progressBarWidth = ref(100);
let interval = null;

const now = new Date().toLocaleString("id-ID", {
    dateStyle: "medium",
    timeStyle: "short",
});

watch(
    () => props.show,
    (val) => {
        if (val) {
            countdown.value = props.retryAfter;
            progressBarWidth.value = 100;
            startCountdown();
        } else {
            stopCountdown();
        }
    }
);

function startCountdown() {
    stopCountdown();
    interval = setInterval(() => {
        if (countdown.value > 0) {
            countdown.value--;
            progressBarWidth.value = (countdown.value / props.retryAfter) * 100;
        } else {
            stopCountdown();
            emit("close");
        }
    }, 1000);
}

function stopCountdown() {
    if (interval) {
        clearInterval(interval);
        interval = null;
    }
}

function reloadPage() {
    window.location.reload();
}

onBeforeUnmount(() => stopCountdown());
</script>

<style scoped>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
.animate-fadeIn {
    animation: fadeIn 0.35s ease-out;
}
</style>
