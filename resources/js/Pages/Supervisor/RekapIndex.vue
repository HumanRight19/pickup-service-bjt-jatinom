<template>
    <div>
        <h1 class="text-xl font-bold mb-4">Laporan Setoran</h1>

        <!-- Filter -->
        <select
            v-model="selectedFilter"
            @change="filterData"
            class="border p-1 rounded mb-4"
        >
            <option value="harian">Harian</option>
            <option value="mingguan">Mingguan</option>
            <option value="bulanan">Bulanan</option>
            <option value="tahunan">Tahunan</option>
        </select>

        <!-- Tabel Rekap -->
        <table class="w-full border mb-4">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Nasabah</th>
                    <th>Petugas</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="s in setorans" :key="s.id">
                    <td>{{ s.tanggal_setor }}</td>
                    <td>{{ s.nasabah.nama }}</td>
                    <td>{{ s.petugas.name }}</td>
                    <td>Rp {{ format(s.jumlah) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="font-bold text-lg">Total: Rp {{ format(total) }}</div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps(["setorans", "total", "filter"]);

const selectedFilter = ref(props.filter);

function filterData() {
    router.get(
        "/supervisor/rekap",
        { filter: selectedFilter.value },
        { preserveState: true }
    );
}

function format(num) {
    return new Intl.NumberFormat("id-ID").format(num);
}
</script>
