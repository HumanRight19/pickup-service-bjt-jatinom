<!-- resources/js/Pages/Supervisor/Partials/PetugasTable.vue -->
<template>
    <table class="w-full border mt-4 bg-white shadow rounded">
        <thead class="bg-gray-100">
            <tr>
                <th class="text-left px-4 py-2">Nama</th>
                <th class="text-left px-4 py-2">Email</th>
                <th class="text-left px-4 py-2">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="user in users" :key="user.id" class="border-t">
                <td class="px-4 py-2">{{ user.name }}</td>
                <td class="px-4 py-2">{{ user.email }}</td>
                <td class="px-4 py-2">
                    <button
                        @click="$emit('edit', user)"
                        class="bg-yellow-400 text-white px-3 py-1 rounded mr-2"
                    >
                        Edit
                    </button>
                    <button
                        @click="$emit('delete', user.id)"
                        class="bg-red-500 text-white px-3 py-1 rounded"
                    >
                        Hapus
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</template>

<script setup>
defineProps(["users"]);
defineEmits(["edit", "delete"]);
</script>
