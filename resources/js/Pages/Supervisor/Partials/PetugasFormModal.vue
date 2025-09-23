<!-- resources/js/Pages/Supervisor/Partials/PetugasFormModal.vue -->
<template>
    <div
        v-if="show"
        class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center"
    >
        <div class="bg-white p-6 rounded w-full max-w-md shadow">
            <h2 class="text-xl font-bold mb-4">
                {{ editing ? "Edit" : "Tambah" }} Petugas
            </h2>
            <form @submit.prevent="submit">
                <div class="mb-4">
                    <label class="block">Nama</label>
                    <input
                        v-model="form.name"
                        class="w-full border p-2 rounded"
                        required
                    />
                </div>
                <div class="mb-4">
                    <label class="block">Email</label>
                    <input
                        v-model="form.email"
                        type="email"
                        class="w-full border p-2 rounded"
                        required
                    />
                </div>
                <div class="mb-4" v-if="!editing">
                    <label class="block">Password</label>
                    <input
                        v-model="form.password"
                        type="password"
                        class="w-full border p-2 rounded"
                        required
                    />
                </div>
                <div class="flex justify-end space-x-2">
                    <button
                        type="button"
                        @click="$emit('update:show', false)"
                        class="px-4 py-2 bg-gray-300 rounded"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded"
                    >
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive, watch } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    show: Boolean,
    editing: Object,
});

const emit = defineEmits(["update:show", "saved"]);

const form = reactive({
    name: "",
    email: "",
    password: "",
});

watch(
    () => props.editing,
    (user) => {
        if (user) {
            form.name = user.name;
            form.email = user.email;
            form.password = "";
        } else {
            form.name = "";
            form.email = "";
            form.password = "";
        }
    }
);

function submit() {
    if (props.editing) {
        router.put(`/petugas/${props.editing.id}`, form, {
            onSuccess: () => emit("saved"),
        });
    } else {
        router.post("/petugas", form, {
            onSuccess: () => emit("saved"),
        });
    }
}
</script>
