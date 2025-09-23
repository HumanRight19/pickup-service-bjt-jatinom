<script setup>
import { useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";

defineProps({ auth: Object });

const form = useForm({
    name: auth.user.name,
    email: auth.user.email,
    password: "",
});
</script>

<template>
    <AuthenticatedLayout :auth="auth">
        <Head title="Edit Profil" />

        <div class="py-6 sm:py-10">
            <div class="px-4 sm:px-6 lg:px-8">
                <h1
                    class="text-2xl font-bold mb-6 text-gray-800 dark:text-gray-100"
                >
                    Edit Profil
                </h1>

                <div
                    class="w-full rounded-2xl shadow-sm border bg-white dark:bg-gray-900 dark:border-gray-700 p-6"
                >
                    <form
                        @submit.prevent="form.put('/profile')"
                        class="space-y-5"
                    >
                        <!-- Nama -->
                        <div>
                            <label
                                for="name"
                                class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Nama
                            </label>
                            <input
                                id="name"
                                type="text"
                                v-model="form.name"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                            />
                            <p
                                v-if="form.errors.name"
                                class="text-red-600 text-sm mt-1"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label
                                for="email"
                                class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Email
                            </label>
                            <input
                                id="email"
                                type="email"
                                v-model="form.email"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                            />
                            <p
                                v-if="form.errors.email"
                                class="text-red-600 text-sm mt-1"
                            >
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <!-- Password -->
                        <div>
                            <label
                                for="password"
                                class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Password (Opsional)
                            </label>
                            <input
                                id="password"
                                type="password"
                                v-model="form.password"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                            />
                            <p
                                v-if="form.errors.password"
                                class="text-red-600 text-sm mt-1"
                            >
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <!-- Tombol -->
                        <div class="flex sm:justify-end">
                            <button
                                type="submit"
                                class="w-full sm:w-auto rounded-lg bg-blue-600 px-5 py-2 text-white font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :disabled="form.processing"
                            >
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
