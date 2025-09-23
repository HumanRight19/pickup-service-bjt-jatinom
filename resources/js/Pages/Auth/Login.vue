<template>
    <GuestLayout :card="false">
        <div class="flex w-full h-screen">
            <!-- LEFT (Image / Illustration) -->
            <div
                class="hidden md:flex w-1/2 items-center justify-center bg-cover bg-center"
                style="background-image: url('/images/background.png')"
            ></div>

            <!-- RIGHT (Form Login) -->
            <div
                class="flex flex-col justify-center items-center w-full md:w-1/2 h-screen px-8 sm:px-12 lg:px-16 bg-white"
            >
                <div class="w-full max-w-md space-y-6">
                    <div class="flex justify-center mb-6">
                        <Link href="/">
                            <img
                                src="/images/logo-color.png"
                                alt="Logo"
                                class="h-12 w-auto"
                            />
                        </Link>
                    </div>

                    <h2 class="text-3xl font-bold text-gray-800 text-center">
                        Selamat Datang 👋
                    </h2>
                    <p class="text-gray-500 text-center text-sm">
                        Masuk untuk melanjutkan ke dashboard Anda
                    </p>

                    <div
                        v-if="status"
                        class="mb-4 text-sm text-green-600 text-center"
                    >
                        {{ status }}
                    </div>

                    <!-- FORM -->
                    <form @submit.prevent="submit" class="space-y-5">
                        <div>
                            <InputLabel for="email" value="Email" />
                            <TextInput
                                id="email"
                                type="email"
                                class="mt-1 block w-full"
                                v-model="form.email"
                                required
                                autofocus
                                autocomplete="username"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.email"
                            />
                        </div>

                        <div>
                            <InputLabel for="password" value="Password" />
                            <TextInput
                                id="password"
                                type="password"
                                class="mt-1 block w-full"
                                v-model="form.password"
                                required
                                autocomplete="current-password"
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.password"
                            />
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="flex items-center text-sm">
                                <Checkbox v-model:checked="form.remember" />
                                <span class="ml-2 text-gray-700">
                                    Ingat saya
                                </span>
                            </label>

                            <!--  
                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="text-sm text-indigo-600 hover:underline"
                            >
                                Lupa password?
                            </Link>
                            -->
                        </div>

                        <PrimaryButton
                            class="w-full justify-center py-3 text-lg"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            Masuk
                        </PrimaryButton>
                    </form>
                </div>
            </div>
        </div>
        <TooManyRequestsModal
            :show="tooManyAttemptsModal"
            :retryAfter="retryAfterModal"
            @close="tooManyAttemptsModal = false"
        />
    </GuestLayout>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { Link, useForm } from "@inertiajs/vue3";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import Checkbox from "@/Components/Checkbox.vue";
import TooManyRequestsModal from "@/Components/TooManyRequestsModal.vue";

onMounted(() => {
    document.title = "Login - Sistem Pickup Service";

    const stored = localStorage.getItem("retry_after");
    if (stored) {
        const remaining = Math.floor((stored - Date.now()) / 1000);
        if (remaining > 0) {
            retryAfterModal.value = remaining;
            tooManyAttemptsModal.value = true;
            startCountdown(); // ⬅️ start countdown tiap detik
        } else {
            localStorage.removeItem("retry_after");
        }
    }
});

const props = defineProps({
    canResetPassword: { type: Boolean, default: false },
    status: { type: String, default: "" },
});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

let countdownInterval = null;

function startCountdown() {
    clearInterval(countdownInterval);
    countdownInterval = setInterval(() => {
        retryAfterModal.value--;
        if (retryAfterModal.value <= 0) {
            clearInterval(countdownInterval);
            tooManyAttemptsModal.value = false;
            localStorage.removeItem("retry_after");
        }
    }, 1000);
}

const tooManyAttemptsModal = ref(false);
const retryAfterModal = ref(60);

function submit() {
    form.post(route("login"), {
        onSuccess: () => {
            axios.get("/csrf-token").then((res) => {
                axios.defaults.headers.common["X-CSRF-TOKEN"] = res.data.token;
            });
        },
        onFinish: () => form.reset("password"),
        onError: (errors) => {
            if (
                errors.email &&
                errors.email.includes("Too many login attempts")
            ) {
                retryAfterModal.value = form.errors.retry_after ?? 60;
                localStorage.setItem(
                    "retry_after",
                    Date.now() + retryAfterModal.value * 1000
                );
                tooManyAttemptsModal.value = true;
                form.reset("password");
                startCountdown(); // ⬅️ mulai countdown saat modal muncul
            }
        },
    });
}
</script>
