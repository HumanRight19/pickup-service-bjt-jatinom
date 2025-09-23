<template>
    <SupervisorLayout :user="page.props.auth.user" activePage="setoranRequest">
        <div
            class="min-h-screen bg-gray-50 dark:bg-gray-950 p-6 flex flex-col gap-8"
        >
            <!-- Pending Requests -->
            <section class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
                <h2
                    class="text-xl font-bold mb-4 text-gray-800 dark:text-gray-100"
                >
                    Pending Requests
                </h2>

                <!-- Pending Table -->
                <div class="overflow-x-auto">
                    <table
                        class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-center"
                    >
                        <thead
                            class="bg-gray-100 dark:bg-gray-900 dark:text-white"
                        >
                            <tr>
                                <th class="px-4 py-2 text-sm font-medium">#</th>
                                <th class="px-4 py-2 text-sm font-medium">
                                    Tanggal
                                </th>
                                <th class="px-4 py-2 text-sm font-medium">
                                    Petugas
                                </th>
                                <th class="px-4 py-2 text-sm font-medium">
                                    Nasabah
                                </th>
                                <th class="px-4 py-2 text-sm font-medium">
                                    Tipe
                                </th>
                                <th class="px-4 py-2 text-sm font-medium">
                                    Pengajuan
                                </th>
                                <th class="px-4 py-2 text-sm font-medium">
                                    Jumlah Lama
                                </th>
                                <th class="px-4 py-2 text-sm font-medium">
                                    Jumlah Baru
                                </th>
                                <th class="px-4 py-2 text-sm font-medium">
                                    Alasan
                                </th>
                                <th
                                    class="px-4 py-2 text-center text-sm font-medium"
                                >
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-gray-200 dark:divide-gray-700 dark:text-white"
                        >
                            <tr
                                v-for="(req, index) in pendingRequestsData"
                                :key="req.id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                <!-- Kolom nomor -->
                                <td class="px-4 py-2 text-sm">
                                    {{ pendingIndexStart + index + 1 }}
                                </td>

                                <!-- Kolom tanggal -->
                                <td
                                    class="px-4 py-2 text-sm whitespace-nowrap"
                                    v-html="formatDateTimeRow(req.created_at)"
                                ></td>

                                <!-- Kolom nama petugas -->
                                <td class="px-4 py-2 text-sm">
                                    {{ req.petugas?.name }}
                                </td>

                                <!-- Kolom nama nasabah -->
                                <td class="px-4 py-2 text-sm">
                                    {{ req.setoranable?.nasabah?.nama }}
                                </td>

                                <!-- Kolom tipe setoran -->
                                <td class="px-4 py-2 text-sm capitalize">
                                    <span
                                        class="px-2 py-1 rounded-full text-sm font-semibold"
                                        :class="{
                                            'bg-blue-600 text-white':
                                                req.setoranable_type ===
                                                'App\\Models\\Setoran',
                                            'bg-orange-400 text-white':
                                                req.setoranable_type !==
                                                'App\\Models\\Setoran',
                                        }"
                                    >
                                        {{
                                            req.setoranable_type ===
                                            "App\\Models\\Setoran"
                                                ? "Reguler"
                                                : "Titip"
                                        }}
                                    </span>
                                </td>

                                <!-- Kolom jenis pengajuan -->
                                <td class="px-4 py-2 text-sm">
                                    <span
                                        class="px-2 py-1 rounded-full text-sm font-semibold"
                                        :class="{
                                            'bg-yellow-100 text-yellow-800':
                                                req.type === 'update',
                                            'bg-pink-100 text-pink-600':
                                                req.type === 'batal',
                                        }"
                                    >
                                        {{
                                            req.type === "update"
                                                ? "Update"
                                                : "Batal"
                                        }}
                                    </span>
                                </td>

                                <!-- Kolom nominal setoran lama -->
                                <td class="px-4 py-2 text-sm">
                                    {{ formatRupiah(req.jumlah_lama) }}
                                </td>

                                <!-- Kolom nominal setoran baru -->
                                <td class="px-4 py-2 text-sm">
                                    {{ formatRupiah(req.jumlah_baru ?? "-") }}
                                </td>

                                <!-- Kolom alasan -->
                                <td
                                    class="px-4 py-2 text-sm max-w-[250px] break-words"
                                >
                                    {{ req.alasan }}
                                </td>

                                <!-- Kolom status pengajuan -->
                                <td
                                    class="px-4 py-2 text-center flex gap-2 justify-center"
                                >
                                    <button
                                        @click="approveRequest(req.id)"
                                        class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white rounded-md text-sm"
                                    >
                                        Approve
                                    </button>
                                    <button
                                        @click="rejectRequest(req.id)"
                                        class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded-md text-sm"
                                    >
                                        Reject
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="pendingRequestsData.length === 0">
                                <td
                                    colspan="8"
                                    class="text-center text-gray-500 py-4"
                                >
                                    Tidak ada pending request
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Pending -->
                <div class="mt-4 flex justify-center gap-2">
                    <button
                        v-for="link in pendingRequests.links"
                        :key="link.label"
                        @click="
                            link.url &&
                                goToPendingPage(
                                    link.label.replace(/[^0-9]/g, '')
                                )
                        "
                        v-html="link.label"
                        :disabled="!link.url"
                        class="px-3 py-1 border rounded-md hover:bg-gray-100 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600"
                        :class="{
                            'bg-blue-500 text-white dark:text-white':
                                link.active,
                        }"
                    />
                </div>
            </section>

            <!-- History Requests -->
            <section class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
                <h2
                    class="text-xl font-bold mb-4 text-gray-800 dark:text-gray-100"
                >
                    History Requests
                </h2>

                <div class="overflow-x-auto">
                    <table
                        class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-center"
                    >
                        <thead>
                            <!-- Filter Row -->
                            <tr
                                class="bg-gray-100 dark:bg-gray-900 dark:text-white"
                            >
                                <th class="px-4 py-2"></th>
                                <th class="px-4 py-2">
                                    <select
                                        v-model="form.range"
                                        class="w-full border px-2 py-1 rounded text-sm dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="semua">Semua</option>
                                        <option value="harian">Harian</option>
                                        <option value="mingguan">
                                            Mingguan
                                        </option>
                                        <option value="bulanan">Bulanan</option>
                                    </select>
                                </th>
                                <th class="px-4 py-2">
                                    <select
                                        v-model="form.petugas_id"
                                        class="w-full border px-2 py-1 rounded text-sm dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="">Semua Petugas</option>
                                        <option
                                            v-for="p in petugasList"
                                            :key="p.id"
                                            :value="p.id"
                                        >
                                            {{ p.name }}
                                        </option>
                                    </select>
                                </th>
                                <th class="px-4 py-2">
                                    <input
                                        type="text"
                                        placeholder="Cari Nasabah..."
                                        v-model="form.nasabah"
                                        class="w-full border px-2 py-1 rounded text-sm dark:bg-gray-700 dark:text-white"
                                    />
                                </th>
                                <th class="px-4 py-2">
                                    <select
                                        v-model="form.tipe"
                                        class="w-full border px-2 py-1 rounded text-sm dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="">Semua Tipe</option>
                                        <option value="titip">Titip</option>
                                        <option value="reguler">Reguler</option>
                                    </select>
                                </th>
                                <th class="px-4 py-2">
                                    <select
                                        v-model="form.status"
                                        class="w-full border px-2 py-1 rounded text-sm dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="">Semua Status</option>
                                        <option value="approved">
                                            Approved
                                        </option>
                                        <option value="rejected">
                                            Rejected
                                        </option>
                                    </select>
                                </th>
                                <th colspan="3"></th>
                                <th class="px-4 py-2">
                                    <select
                                        v-model="form.pengajuan"
                                        class="w-full border px-2 py-1 rounded text-sm dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="">
                                            Semua Pengajuan
                                        </option>
                                        <option value="batal">Batal</option>
                                        <option value="update">Update</option>
                                    </select>
                                </th>
                            </tr>

                            <!-- Column Header -->
                            <tr
                                class="bg-gray-100 dark:bg-gray-900 dark:text-white"
                            >
                                <th class="px-4 py-2 text-center text-sm">#</th>
                                <th class="px-4 py-2 text-center text-sm">
                                    Tanggal
                                </th>
                                <th class="px-4 py-2 text-center text-sm">
                                    Petugas
                                </th>
                                <th class="px-4 py-2 text-center text-sm">
                                    Nasabah
                                </th>
                                <th class="px-4 py-2 text-center text-sm">
                                    Tipe
                                </th>
                                <th class="px-4 py-2 text-center text-sm">
                                    Status
                                </th>
                                <th class="px-4 py-2 text-center text-sm">
                                    Jumlah Lama
                                </th>
                                <th class="px-4 py-2 text-center text-sm">
                                    Jumlah Baru
                                </th>
                                <th class="px-4 py-2 text-center text-sm">
                                    Alasan
                                </th>
                                <th class="px-4 py-2 text-center text-sm">
                                    Pengajuan
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="(req, index) in filteredHistory"
                                :key="req.id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                <!-- nomor urut -->
                                <td
                                    class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300"
                                >
                                    {{ historyIndexStart + index + 1 }}
                                </td>

                                <!-- tanggal -->
                                <td
                                    class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap"
                                    v-html="formatDateTimeRow(req.created_at)"
                                ></td>

                                <!-- petugas -->
                                <td
                                    class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300"
                                >
                                    {{ req.petugas?.name ?? "-" }}
                                </td>

                                <!-- nasabah -->
                                <td
                                    class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300"
                                >
                                    {{ req.setoranable?.nasabah?.nama ?? "-" }}
                                </td>

                                <!-- tipe setoran -->
                                <td class="px-4 py-2 text-sm">
                                    <span
                                        class="px-2 py-1 rounded-full text-sm font-semibold"
                                        :class="{
                                            'bg-blue-600 text-white':
                                                req.setoranable_type ===
                                                'App\\Models\\Setoran',
                                            'bg-orange-400 text-white':
                                                req.setoranable_type !==
                                                'App\\Models\\Setoran',
                                        }"
                                    >
                                        {{
                                            req.setoranable_type ===
                                            "App\\Models\\Setoran"
                                                ? "Reguler"
                                                : "Titip"
                                        }}
                                    </span>
                                </td>

                                <!-- status -->
                                <td class="px-4 py-2 text-sm">
                                    <span
                                        class="px-2 py-1 rounded-full text-sm font-semibold"
                                        :class="{
                                            'bg-green-100 text-green-600':
                                                req.status === 'approved',
                                            'bg-red-100 text-red-600':
                                                req.status === 'rejected',
                                            'bg-yellow-100 text-yellow-600':
                                                req.status === 'pending',
                                        }"
                                    >
                                        {{
                                            req.status.charAt(0).toUpperCase() +
                                            req.status.slice(1)
                                        }}
                                    </span>
                                </td>

                                <!-- jumlah lama -->
                                <td
                                    class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300"
                                >
                                    {{ formatRupiah(req.jumlah_lama) }}
                                </td>

                                <!-- jumlah baru -->
                                <td
                                    class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300"
                                >
                                    {{
                                        req.jumlah_baru
                                            ? formatRupiah(req.jumlah_baru)
                                            : "-"
                                    }}
                                </td>

                                <!-- alasan -->
                                <td
                                    class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300"
                                >
                                    {{ req.alasan ?? "-" }}
                                </td>

                                <!-- pengajuan -->
                                <td class="px-4 py-2 text-sm">
                                    <span
                                        class="px-2 py-1 rounded-full text-sm font-semibold"
                                        :class="{
                                            'bg-yellow-100 text-yellow-800':
                                                req.type === 'update',
                                            'bg-pink-100 text-pink-600':
                                                req.type === 'batal',
                                        }"
                                    >
                                        {{
                                            req.type === "update"
                                                ? "Update"
                                                : "Batal"
                                        }}
                                    </span>
                                </td>
                            </tr>

                            <!-- kalau kosong -->
                            <tr v-if="filteredHistory.length === 0">
                                <td
                                    colspan="10"
                                    class="px-4 py-4 text-center text-gray-500 dark:text-gray-400"
                                >
                                    Belum ada history
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination History -->
                <div class="mt-4 flex justify-center gap-2">
                    <button
                        v-for="link in historyLinks"
                        :key="link.label"
                        @click.prevent="
                            link.url &&
                                goToHistoryPage(
                                    link.label.replace(/[^0-9]/g, '')
                                )
                        "
                        v-html="link.label"
                        :disabled="!link.url"
                        class="px-3 py-1 border rounded-md hover:bg-gray-100 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600"
                        :class="{
                            'bg-blue-500 text-white dark:text-white':
                                link.active,
                        }"
                    ></button>
                </div>
            </section>
        </div>
    </SupervisorLayout>
</template>

<script setup>
import { ref, watch, computed } from "vue";
import { usePage, useForm, router } from "@inertiajs/vue3";
import debounce from "lodash/debounce";
import SupervisorLayout from "@/Layouts/SupervisorLayout.vue";

const page = usePage();
const props = defineProps({
    pendingRequests: Object,
    historyRequests: Object,
    petugasList: Array,
    filters: Object,
});

// --- FORM FILTER SESUAI SESSION ---
const form = useForm({
    range: props.filters.range ?? null,
    petugas_id: props.filters.petugas_id ?? "",
    blok: props.filters.blok ?? "",
    nasabah: props.filters.nasabah ?? "",
    tipe: props.filters.tipe ?? "",
    status: props.filters.status ?? "",
    pengajuan: props.filters.pengajuan ?? "",
});

const pendingRequests = computed(() => props.pendingRequests || {});

// --- DATA PENDING & HISTORY ---
const pendingRequestsData = ref([...(props.pendingRequests?.data || [])]);
const historyRequestsData = ref([...(props.historyRequests?.data || [])]);

const filteredHistory = computed(() => historyRequestsData.value);

// --- PAGINATION INFO ---
const currentPendingPage = ref(props.pendingRequests?.current_page || 1);
const perPagePending = props.pendingRequests?.per_page || 10;

const currentHistoryPage = ref(props.historyRequests?.current_page || 1);
const perPageHistory = props.historyRequests?.per_page || 10;
const lastHistoryPage = computed(() => props.historyRequests?.last_page || 1);

// --- LINKS PAGINATION HISTORY ---
const historyLinks = computed(() => {
    return props.historyRequests?.links || [];
});

// --- NOMOR URUT SESUAI HALAMAN ---
const pendingIndexStart = computed(
    () => (currentPendingPage.value - 1) * perPagePending
);
const historyIndexStart = computed(
    () => (currentHistoryPage.value - 1) * perPageHistory
);

// --- FORMAT RUPIAH ---
const formatRupiah = (value) => {
    if (value == null || isNaN(value)) return "-";
    return "Rp. " + Number(value).toLocaleString("id-ID");
};

// --- FORMAT DATETIME ---
const formatDateTimeRow = (value) => {
    if (!value) return "-";
    const date = new Date(value);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");
    const hours = String(date.getHours()).padStart(2, "0");
    const minutes = String(date.getMinutes()).padStart(2, "0");
    const seconds = String(date.getSeconds()).padStart(2, "0");

    return `${year}-${month}-${day}<br>${hours}:${minutes}:${seconds}`;
};

// --- SUBMIT FILTER (history only) ---
function submitHistoryFilter(page = 1) {
    router.post(
        route("setoran-requests.filter"),
        {
            ...form,
            history_page: page,
        },
        {
            preserveState: true, // biar pendingRequests tetap
            preserveScroll: true, // scroll tidak reset
            onSuccess: (page) => {
                historyRequestsData.value =
                    page.props.historyRequests?.data || [];
                currentHistoryPage.value =
                    page.props.historyRequests?.current_page || 1;
            },
        }
    );
}

// --- WATCH FILTER FORM (debounce) ---
watch(
    [
        () => form.range,
        () => form.petugas_id,
        () => form.nasabah,
        () => form.tipe,
        () => form.status,
        () => form.pengajuan,
        () => form.blok,
    ],
    debounce(() => submitHistoryFilter(1), 400)
);

// --- WATCH HISTORY REQUESTS UNTUK UPDATE ---
watch(
    () => page.props.historyRequests,
    (val) => {
        historyRequestsData.value = val?.data || [];
        currentHistoryPage.value = val?.current_page || 1;
    }
);

// --- ACTION APPROVE / REJECT PENDING (tidak diubah) ---
const approveRequest = (id) => {
    router.post(
        `/supervisor/setoran-requests/${id}/approve`,
        {},
        {
            onSuccess: (page) => {
                // replace seluruh array agar otomatis ter-update semua perubahan status
                pendingRequestsData.value =
                    page.props.pendingRequests?.data || [];
                historyRequestsData.value =
                    page.props.historyRequests?.data || [];
            },
        }
    );
};

const rejectRequest = (id) => {
    router.post(
        `/supervisor/setoran-requests/${id}/reject`,
        {},
        {
            onSuccess: (page) => {
                pendingRequestsData.value =
                    page.props.pendingRequests?.data || [];
                historyRequestsData.value =
                    page.props.historyRequests?.data || [];
            },
        }
    );
};

// --- PAGINATION Pending (tidak diubah) ---
function goToPendingPage(page) {
    router.get(
        route("setoran-requests.index"),
        {
            pending_page: page,
            history_page: currentHistoryPage.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            onSuccess: (page) => {
                pendingRequestsData.value =
                    page.props.pendingRequests?.data || [];
                currentPendingPage.value =
                    page.props.pendingRequests?.current_page || 1;
            },
        }
    );
}

// --- PAGINATION History ---
function goToHistoryPage(page) {
    router.post(
        route("setoran-requests.filter"),
        {
            ...form,
            history_page: page,
        },
        {
            preserveState: true, // biar props baru dikirim dari server
            preserveScroll: true,
            replace: true,
            onSuccess: (page) => {
                historyRequestsData.value =
                    page.props.historyRequests?.data || [];
                currentHistoryPage.value =
                    page.props.historyRequests?.current_page || 1;
            },
        }
    );
}
</script>
