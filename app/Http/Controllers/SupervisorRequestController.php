<?php
namespace App\Http\Controllers;

use App\Models\SetoranRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Arr;
use Carbon\Carbon;
class SupervisorRequestController extends Controller
{
    public function index(Request $request)
    {
        // Ambil filter dari session atau set default
        $defaultFilter = [
            'range' => 'semua',
            'petugas_id' => null,
            'blok' => null,
            'nasabah' => null,
            'status' => null,
            'pengajuan' => null,
            'tipe' => null,
        ];

        // Merge filter dari request, buang kosong/null
        $filter = array_merge($defaultFilter, array_filter($request->only(array_keys($defaultFilter)), fn($v) => $v !== null && $v !== ''));
        session(['request_filter' => $filter]); // simpan ke session

        // Hitung range waktu
        [$start, $end] = $this->getDateRange($filter['range']);

        // Halaman pagination
        $pendingPage = $request->query('pending_page', 1);
        $historyPage = $request->query('history_page', 1);

        // ------------------ Pending Requests ------------------
        $pendingRequests = SetoranRequest::with([
            'petugas',
            'setoranable' => fn($morph) => $morph->morphWith([
                \App\Models\Setoran::class => ['nasabah.blokPasar'],
                \App\Models\TitipSetoran::class => ['nasabah.blokPasar'],
            ])
        ])
            ->where('status', 'pending')
            ->when($start && $end, fn($q) => $q->whereBetween('created_at', [$start, $end]))
            ->when(Arr::get($filter, 'petugas_id'), fn($q, $val) => $q->where('petugas_id', $val))
            ->when(Arr::get($filter, 'tipe') === 'reguler', fn($q) => $q->where('setoranable_type', \App\Models\Setoran::class))
            ->when(Arr::get($filter, 'tipe') === 'titip', fn($q) => $q->where('setoranable_type', \App\Models\TitipSetoran::class))
            ->when(Arr::get($filter, 'blok'), fn($q, $val) => $q->whereHasMorph(
                'setoranable',
                [\App\Models\Setoran::class, \App\Models\TitipSetoran::class],
                fn($sq) => $sq->whereHas('nasabah.blokPasar', fn($bq) => $bq->where('nama', $val))
            ))
            ->when(Arr::get($filter, 'nasabah'), fn($q, $val) => $q->whereHasMorph(
                'setoranable',
                [\App\Models\Setoran::class, \App\Models\TitipSetoran::class],
                fn($sq) => $sq->whereHas('nasabah', fn($nq) => $nq->where('nama', 'like', "%{$val}%"))
            ))
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'pending_page', $pendingPage);

        $pendingRequests->getCollection()->transform(
            fn($req) => tap($req, fn($r) => $r->tanggal_request = $r->created_at->format('Y-m-d'))
        );

        // ------------------ History Requests ------------------
        $historyRequests = SetoranRequest::with([
            'petugas',
            'setoranable' => fn($morph) => $morph->morphWith([
                \App\Models\Setoran::class => ['nasabah.blokPasar'],
                \App\Models\TitipSetoran::class => ['nasabah.blokPasar'],
            ])
        ])
            ->whereIn('status', ['approved', 'rejected'])
            ->when(Arr::get($filter, 'range') !== 'semua' && $start && $end, fn($q) => $q->whereBetween('created_at', [$start, $end]))
            ->when(Arr::get($filter, 'petugas_id'), fn($q, $val) => $q->where('petugas_id', $val))
            ->when(Arr::get($filter, 'status') && Arr::get($filter, 'status') !== 'all', fn($q, $val) => $q->where('status', $val))
            ->when(Arr::get($filter, 'pengajuan') && Arr::get($filter, 'pengajuan') !== 'all', fn($q, $val) => $q->where('type', $val))
            ->when(Arr::get($filter, 'tipe') === 'reguler', fn($q) => $q->where('setoranable_type', \App\Models\Setoran::class))
            ->when(Arr::get($filter, 'tipe') === 'titip', fn($q) => $q->where('setoranable_type', \App\Models\TitipSetoran::class))
            ->when(Arr::get($filter, 'blok'), fn($q, $val) => $q->whereHasMorph(
                'setoranable',
                [\App\Models\Setoran::class, \App\Models\TitipSetoran::class],
                fn($sq) => $sq->whereHas('nasabah.blokPasar', fn($bq) => $bq->where('nama', $val))
            ))
            ->when(Arr::get($filter, 'nasabah'), fn($q, $val) => $q->whereHasMorph(
                'setoranable',
                [\App\Models\Setoran::class, \App\Models\TitipSetoran::class],
                fn($sq) => $sq->whereHas('nasabah', fn($nq) => $nq->where('nama', 'like', "%{$val}%"))
            ))
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'history_page', $historyPage);

        $historyRequests->getCollection()->transform(
            fn($req) => tap($req, fn($r) => $r->tanggal_request = $r->created_at->format('Y-m-d'))
        );

        // ------------------ Return Inertia ------------------
        return Inertia::render('Supervisor/SetoranRequestPage', [
            'pendingRequests' => $pendingRequests,
            'historyRequests' => $historyRequests,
            'filters' => $filter,
            'petugasList' => User::where('role', 'petugas')->get(),
        ]);
    }

    public function filter(Request $request)
    {
        // Ambil filter dari request, simpan ke session biar tetap tersimpan
        $filterKeys = ['range', 'petugas_id', 'blok', 'nasabah', 'tipe', 'status', 'pengajuan'];
        $filter = array_filter($request->only($filterKeys), fn($v) => $v !== null && $v !== '');
        session(['request_filter' => $filter]);

        // Bangun query history requests
        $query = SetoranRequest::with([
            'petugas',
            'setoranable' => fn($morph) => $morph->morphWith([
                \App\Models\Setoran::class => ['nasabah.blokPasar'],
                \App\Models\TitipSetoran::class => ['nasabah.blokPasar'],
            ]),
        ])->whereIn('status', ['approved', 'rejected']); // khusus history

        // Filter range waktu
        if (!empty($filter['range']) && $filter['range'] !== 'semua') {
            [$start, $end] = match ($filter['range']) {
                'harian' => [now()->startOfDay(), now()->endOfDay()],
                'mingguan' => [now()->startOfWeek(), now()->endOfWeek()],
                'bulanan' => [now()->startOfMonth(), now()->endOfMonth()],
                'tahunan' => [now()->startOfYear(), now()->endOfYear()],
                default => [null, null],
            };

            if ($start && $end) {
                $query->whereBetween('created_at', [$start, $end]);
            }
        }

        // Filter petugas
        if (!empty($filter['petugas_id'])) {
            $query->where('petugas_id', $filter['petugas_id']);
        }

        // Filter blok
        if (!empty($filter['blok'])) {
            $query->whereHasMorph(
                'setoranable',
                [\App\Models\Setoran::class, \App\Models\TitipSetoran::class],
                fn($sq) => $sq->whereHas('nasabah.blokPasar', fn($bq) => $bq->where('nama', $filter['blok']))
            );
        }

        // Filter nama nasabah
        if (!empty($filter['nasabah'])) {
            $query->whereHasMorph(
                'setoranable',
                [\App\Models\Setoran::class, \App\Models\TitipSetoran::class],
                fn($sq) => $sq->whereHas('nasabah', fn($nq) => $nq->where('nama', 'like', "%{$filter['nasabah']}%"))
            );
        }

        // Filter tipe
        if (!empty($filter['tipe'])) {
            if ($filter['tipe'] === 'reguler') {
                $query->where('setoranable_type', \App\Models\Setoran::class);
            } elseif ($filter['tipe'] === 'titip') {
                $query->where('setoranable_type', \App\Models\TitipSetoran::class);
            }
        }

        // Filter status
        if (!empty($filter['status']) && $filter['status'] !== 'all') {
            $query->where('status', $filter['status']);
        }

        // Filter pengajuan (type)
        if (!empty($filter['pengajuan']) && $filter['pengajuan'] !== 'all') {
            $query->where('type', $filter['pengajuan']);
        }

        // Pagination
        $historyPage = $request->input('history_page', 1);
        $historyRequests = $query->orderByDesc('created_at')
            ->paginate(10, ['*'], 'history_page', $historyPage);

        // Tambahkan field tanggal_request
        $historyRequests->getCollection()->transform(
            fn($req) => tap($req, fn($r) => $r->tanggal_request = $r->created_at->format('Y-m-d'))
        );

        return Inertia::render('Supervisor/SetoranRequestPage', [
            'pendingRequests' => [], // pending tidak diubah karena filter hanya untuk history
            'historyRequests' => $historyRequests,
            'filters' => $request->all(),
            'petugasList' => User::where('role', 'petugas')->get(),
        ]);
    }

    private function getDateRange($range)
    {
        if (!$range || $range === 'semua') {
            return [null, null];
        }

        switch ($range) {
            case 'harian':
                return [Carbon::today(), Carbon::today()->endOfDay()];
            case 'mingguan':
                return [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()];
            case 'bulanan':
                return [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
            case 'tahunan':
                return [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()];
            default:
                return [null, null];
        }
    }

    // ACC request
    public function approve($id)
    {
        DB::transaction(function () use ($id) {
            $request = SetoranRequest::findOrFail($id);

            if ($request->status !== 'pending') {
                throw new \Exception('Request sudah tidak pending.');
            }

            $setoranable = $request->setoranable;
            if (!$setoranable) {
                throw new \Exception('Setoran terkait tidak ditemukan.');
            }

            if ($request->type === 'batal') {
                // ACC batal
                $request->update([
                    'status' => 'approved',
                    'supervisor_id' => auth()->id(),
                ]);

                // Update setoran jadi batal
                $setoranable->update(['status' => 'batal']);

                // Reject otomatis semua edit nominal pending
                SetoranRequest::where('setoranable_type', get_class($setoranable))
                    ->where('setoranable_id', $setoranable->id)
                    ->where('type', 'update')
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'rejected',
                        'alasan' => 'Rejected otomatis karena pengajuan batal disetujui',
                        'supervisor_id' => auth()->id(),
                    ]);

            } else {
                // ACC update nominal
                $setoranable->update(['jumlah' => $request->jumlah_baru]);

                $request->update([
                    'status' => 'approved',
                    'supervisor_id' => auth()->id(),
                ]);
            }
        });

        return redirect()->route('supervisor.setoran-requests.index')
            ->with('success', 'Request setoran disetujui.');
    }

    // Reject request
    public function reject($id)
    {
        DB::transaction(function () use ($id) {
            $request = SetoranRequest::findOrFail($id);

            if ($request->status !== 'pending') {
                throw new \Exception('Request sudah tidak pending.');
            }

            $setoranable = $request->setoranable;
            if (!$setoranable) {
                throw new \Exception('Setoran terkait tidak ditemukan.');
            }

            // Update status request jadi rejected
            $request->update([
                'status' => 'rejected',
                'supervisor_id' => auth()->id(),
                'alasan' => $request->alasan ?? 'Ditolak oleh supervisor',
            ]);

            // Catatan: Setoran tidak diubah apa-apa, tetap pakai jumlah/status lama
        });

        return redirect()->route('supervisor.setoran-requests.index')
            ->with('success', 'Request setoran ditolak.');
    }

}