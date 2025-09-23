<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanSetoranExport;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Setoran;
use App\Models\BlokPasar;
use App\Models\TitipSetoran;
use App\Models\SetoranRequest;

class SupervisorLaporanController extends Controller
{
    public function index(Request $request)
    {
        // Ambil filter dari session
        $filter = session('laporan_filter', [
            'range' => 'mingguan',
            'petugas_id' => null,
            'blok' => null,
            'nasabah' => null,
            'supervisor' => null,
            'page' => 1,
        ]);

        $page = $filter['page'] ?? 1;
        [$startDate, $endDate] = $this->getDateRange($filter['range']);

        // === Ambil data reguler ===
        $setoranReguler = Setoran::with(['nasabah.blokPasar', 'petugas', 'supervisor'])
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('tanggal', [$startDate, $endDate]))
            ->when($filter['petugas_id'], fn($q) => $q->where('user_id', $filter['petugas_id']))
            ->when($filter['blok'], fn($q) => $q->whereHas('nasabah.blokPasar', fn($b) => $b->where('nama_blok', $filter['blok'])))
            ->when($filter['nasabah'], fn($q) => $q->whereHas('nasabah', fn($n) => $n->where('nama', 'like', "%{$filter['nasabah']}%")))
            ->when($filter['supervisor'], fn($q) => $q->whereHas('supervisor', fn($s) => $s->where('name', 'like', "%{$filter['supervisor']}%")))
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'tanggal' => Carbon::parse($s->tanggal)->format('Y-m-d'),
                'created_at' => $s->created_at,
                'tanggal_waktu' => Carbon::parse($s->created_at)->format('Y-m-d H:i:s'), // 🔑 kolom gabungan
                'petugas' => $s->petugas->name ?? '-',
                'nasabah' => $s->nasabah->nama ?? '-',
                'umplung' => $s->nasabah->nama_umplung ?? '-', // ✅ ambil langsung dari kolom
                'blok' => $s->nasabah->blokPasar->nama_blok ?? '-',
                'supervisor' => $s->supervisor->name ?? '-',
                'jumlah' => $s->jumlah,
                'jenis_setoran' => 'Reguler',
                'status_request' => null,
                'tipe_request' => null,
                'status' => 'Normal',
            ]);

        // === Ambil data titip ===
        $titipSetoran = TitipSetoran::with(['nasabah.blokPasar', 'petugas', 'supervisor'])
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('tanggal_titip', [$startDate, $endDate]))
            ->when($filter['petugas_id'], fn($q) => $q->where('petugas_id', $filter['petugas_id']))
            ->when($filter['blok'], fn($q) => $q->whereHas('nasabah.blokPasar', fn($b) => $b->where('nama_blok', $filter['blok'])))
            ->when($filter['nasabah'], fn($q) => $q->whereHas('nasabah', fn($n) => $n->where('nama', 'like', "%{$filter['nasabah']}%")))
            ->when($filter['supervisor'], fn($q) => $q->whereHas('supervisor', fn($s) => $s->where('name', 'like', "%{$filter['supervisor']}%")))
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'tanggal' => Carbon::parse($t->tanggal_titip)->format('Y-m-d'),
                'created_at' => $t->created_at,
                'tanggal_waktu' => Carbon::parse($t->created_at)->format('Y-m-d H:i:s'), // 🔑 kolom gabungan
                'petugas' => $t->petugas->name ?? '-',
                'nasabah' => $t->nasabah->nama ?? '-',
                'blok' => $t->nasabah->blokPasar->nama_blok ?? '-',
                'supervisor' => $t->supervisor->name ?? '-',
                'jumlah' => $t->jumlah,
                'jenis_setoran' => 'Titip',
                'status_request' => null,
                'tipe_request' => null,
                'status' => 'Normal',
            ]);

        // === Gabungkan reguler & titip ===
        $allSetoran = collect()
            ->merge($setoranReguler)
            ->merge($titipSetoran)
            ->keyBy(fn($item) => $item['jenis_setoran'] . '_' . $item['id']);

        // === Ambil request (update/batal) ===
        $requests = SetoranRequest::with(['setoranable.nasabah.blokPasar', 'petugas', 'supervisor'])
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->when($filter['petugas_id'], fn($q) => $q->where('petugas_id', $filter['petugas_id']))
            ->when($filter['nasabah'], fn($q) => $q->whereHas('setoranable.nasabah', fn($n) => $n->where('nama', 'like', "%{$filter['nasabah']}%")))
            ->when($filter['supervisor'], fn($q) => $q->whereHas('supervisor', fn($s) => $s->where('name', 'like', "%{$filter['supervisor']}%")))
            ->get();

        foreach ($requests as $r) {
            $setoran = $r->setoranable;
            if (!$setoran)
                continue;

            $jenis = $setoran instanceof TitipSetoran ? 'Titip' : 'Reguler';
            $key = $jenis . '_' . $setoran->id;
            $base = $allSetoran->get($key);

            // --- contoh penambahan kolom gabungan di request ---
            $tanggalWaktu = Carbon::parse($r->created_at)->format('Y-m-d H:i:s');

            // UPDATE APPROVED (lama & baru)
            if ($r->type === 'update' && $r->status === 'approved') {
                if ($base) {
                    $allSetoran->put($key . '_update_old_' . $r->id, array_merge($base, [
                        'jumlah' => $r->jumlah_lama,
                        'created_at' => $r->created_at,
                        'tanggal_waktu' => $tanggalWaktu,
                        'status_request' => $r->status,
                        'tipe_request' => $r->type,
                        'status' => 'Update Approved (Old)',
                    ]));

                    $allSetoran->put($key . '_update_new_' . $r->id, array_merge($base, [
                        'jumlah' => $r->jumlah_baru,
                        'created_at' => $r->created_at,
                        'tanggal_waktu' => $tanggalWaktu,
                        'status_request' => $r->status,
                        'tipe_request' => $r->type,
                        'status' => 'Update Approved',
                    ]));
                }
            }

            // BATAL APPROVED
            if ($r->type === 'batal' && $r->status === 'approved') {
                if ($base) {
                    $allSetoran->put($key . '_batal_' . $r->id, array_merge($base, [
                        'created_at' => $r->created_at,
                        'tanggal_waktu' => $tanggalWaktu,
                        'status_request' => $r->status,
                        'tipe_request' => $r->type,
                        'status' => 'Batal Approved',
                    ]));
                    $allSetoran->put($key, array_merge($base, [
                        'created_at' => $r->created_at,
                        'tanggal_waktu' => $tanggalWaktu,
                        'status_request' => $r->status,
                        'tipe_request' => $r->type,
                        'status' => 'Batal Approved',
                    ]));
                }
            }

            // PENDING / REJECTED
            if ($r->status !== 'approved') {
                $allSetoran->put(uniqid('req_'), [
                    'id' => $setoran->id,
                    'tanggal' => $setoran->tanggal ?? $setoran->tanggal_titip,
                    'created_at' => $r->created_at,
                    'tanggal_waktu' => $tanggalWaktu,
                    'nasabah' => $setoran->nasabah->nama ?? '-',
                    'petugas' => $setoran->petugas->name ?? '-',
                    'supervisor' => $setoran->supervisor->name ?? '-',
                    'jumlah' => $r->jumlah_baru ?? $setoran->jumlah,
                    'blok' => $setoran->nasabah->blokPasar->nama_blok ?? '-',
                    'jenis_setoran' => $jenis,
                    'status_request' => $r->status,
                    'tipe_request' => $r->type,
                    'status' => ucfirst($r->type) . ' ' . ucfirst($r->status),
                ]);
            }
        }

        // === Sorting & pagination manual ===
        $laporanCollection = $allSetoran->values()
            ->sortByDesc(fn($x) => $x['created_at'] ?? $x['id'])
            ->values();

        $perPage = 10;
        $currentPageItems = collect($laporanCollection->forPage($page, $perPage))->values();

        $paginated = new LengthAwarePaginator(
            $currentPageItems,
            $laporanCollection->count(),
            $perPage,
            $page,
            ['path' => url('/supervisor/laporan')]
        );

        // === Total fix ===
        $finalSetoran = $laporanCollection
            ->groupBy(fn($x) => $x['jenis_setoran'] . '_' . $x['id'])
            ->map(fn($rows) => $rows->sortBy('created_at')->last());

        // 🔹 Total per page
        $totalJumlahPage = $currentPageItems
            ->groupBy(fn($x) => $x['jenis_setoran'] . '_' . $x['id'])
            ->map(fn($rows) => $rows->sortBy('created_at')->last())
            ->filter(fn($x) => $x['status'] !== 'Batal Approved')
            ->sum('jumlah');

        // 🔹 Grand total (semua page)
        $grandTotal = $finalSetoran
            ->filter(fn($x) => $x['status'] !== 'Batal Approved')
            ->sum('jumlah');

        return Inertia::render('Supervisor/Laporan', [
            'user' => Auth::user(),
            'laporan' => $paginated,
            'totalJumlahPage' => $totalJumlahPage,
            'grandTotal' => $grandTotal, // ganti nama biar lebih jelas
            'petugasList' => User::where('role', 'petugas')->get(['id', 'name']),
            'blokList' => BlokPasar::select('nama_blok')->distinct()->pluck('nama_blok'),
            'filters' => $filter,
            'jenisSetoranList' => ['Reguler', 'Titip'],
            'statusList' => $laporanCollection->pluck('status')->unique()->values(),
        ]);
    }

    public function filter(Request $request)
    {
        $filter = [
            'range' => $request->input('range', 'mingguan'),
            'petugas_id' => $request->input('petugas_id'),
            'blok' => $request->input('blok'),
            'nasabah' => $request->input('nasabah'),
            'supervisor' => $request->input('supervisor'),
            'status' => $request->input('status'),          // 🔑 filter status
            'jenis_setoran' => $request->input('jenis_setoran'), // 🔑 filter jenis setoran
            'page' => max(1, (int) $request->input('page', 1)), // <-- simpan PAGE
        ];

        session(['laporan_filter' => $filter]);

        // Selalu redirect ke index supaya URL tetap bersih
        return redirect()->route('supervisor.laporan.index');
    }

    public function export()
    {
        $filter = session('laporan_filter', [
            'range' => 'mingguan',
            'petugas_id' => null,
            'blok' => null,
            'nasabah' => null,
            'supervisor' => null,
            'status' => null,
            'jenis_setoran' => null,
        ]);

        $range = $filter['range'];
        $petugasId = is_numeric($filter['petugas_id']) ? (int) $filter['petugas_id'] : null;
        $blok = $filter['blok'];
        $nasabah = $filter['nasabah'];
        $supervisor = $filter['supervisor'];
        $status = $filter['status'];
        $jenis_setoran = $filter['jenis_setoran'];

        [$startDate, $endDate] = $this->getDateRange($range);

        $filename = 'Rekap_Layanan_Pickup_Service_' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new LaporanSetoranExport(
                $startDate,
                $endDate,
                $petugasId,
                $blok,
                $nasabah,
                $supervisor,
                $status,
                $jenis_setoran,
                Auth::user()->name
            ),
            $filename
        );
    }

    private function getDateRange(string $range): array
    {
        $startDate = match ($range) {
            'harian' => Carbon::now()->startOfDay(),
            'mingguan' => Carbon::now()->subDays(7)->startOfDay(),
            'bulanan' => Carbon::now()->subMonth()->startOfDay(),
            'tahunan' => Carbon::now()->subYear()->startOfDay(),
            default => Carbon::now()->subDays(7)->startOfDay(),
        };

        $endDate = Carbon::now()->endOfDay();

        return [$startDate, $endDate];
    }
}
