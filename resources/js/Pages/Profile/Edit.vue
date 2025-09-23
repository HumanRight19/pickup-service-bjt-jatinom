<script setup>
import { useForm } from "@inertiajs/vue3";
import { Head, usePage } from "@inertiajs/vue3";
import { onMounted, ref } from "vue";
import SupervisorLayout from "@/Layouts/SupervisorLayout.vue";

const props = defineProps({
    auth: Object,
    status: String,
});

const form = useForm({
    name: props.auth.user.name,
    email: props.auth.user.email,
    password: "",
});

const page = usePage();
const successMessage = ref("");

onMounted(() => {
    if (page.props.status) {
        successMessage.value = page.props.status;
        setTimeout(() => (successMessage.value = ""), 3000);
    }
});
</script>

<template>
    <SupervisorLayout :user="auth.user">
        <Head title="Edit Profil" />

        <div class="max-w-4xl mx-auto px-6 py-10">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">
                Edit Profil
            </h1>

            <!-- Notifikasi -->
            <transition name="fade">
                <div
                    v-if="successMessage"
                    class="bg-green-100 dark:bg-green-800 border border-green-300 dark:border-green-600 text-green-900 dark:text-green-100 px-4 py-3 rounded mb-6"
                >
                    {{ successMessage }}
                </div>
            </transition>

            <form
                @submit.prevent="form.patch('/profile')"
                class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow space-y-8"
            >
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nama -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1"
                        >
                            Nama
                        </label>
                        <input
                            type="text"
                            v-model="form.name"
                            class="w-full border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                        <p
                            v-if="form.errors.name"
                            class="text-sm text-red-500 mt-1"
                        >
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1"
                        >
                            Email
                        </label>
                        <input
                            type="email"
                            v-model="form.email"
                            class="w-full border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                        <p
                            v-if="form.errors.email"
                            class="text-sm text-red-500 mt-1"
                        >
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Password -->
                    <div class="md:col-span-2">
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1"
                        >
                            Password (Opsional)
                        </label>
                        <input
                            type="password"
                            v-model="form.password"
                            placeholder="••••••••"
                            class="w-full border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                        <p
                            v-if="form.errors.password"
                            class="text-sm text-red-500 mt-1"
                        >
                            {{ form.errors.password }}
                        </p>
                    </div>
                </div>

                <!-- Tombol Simpan -->
                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-blue-600 hover:bg-blue-700 transition text-white font-semibold px-6 py-2 rounded-lg disabled:opacity-50 shadow"
                    >
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </SupervisorLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
