<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Nasabah;
use App\Models\BlokPasar;
use App\Models\TitipSetoran;
use App\Models\SetoranRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Imports\NasabahImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Jobs\GenerateQrJob;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class NasabahAdminController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $nasabahs = Nasabah::with(['blokPasar', 'setorans.petugas', 'titipSetorans.petugas'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                        ->orWhere('nama_umplung', 'like', "%{$search}%");
                });
            })
            ->orderBy('nama')
            ->paginate(10);

        $nasabahs->getCollection()->transform(function ($n) {
            // Ambil semua setoran dan titip setoran
            $setoranIds = $n->setorans->pluck('id');
            $titipIds = $n->titipSetorans->pluck('id');

            // Ambil update approved terbaru
            $requests = SetoranRequest::whereIn('setoranable_id', $setoranIds->concat($titipIds))
                ->where('status', 'approved')
                ->where('type', 'update')
                ->orderByDesc('created_at')
                ->get()
                ->groupBy(fn($r) => ($r->setoranable_type === TitipSetoran::class ? 'Titip_' : 'Reguler_') . $r->setoranable_id)
                ->map(fn($group) => $group->first());

            // Gabungkan setoran final (reguler + titip)
            $finalSetorans = collect();

            foreach ($n->setorans as $s) {
                $key = 'Reguler_' . $s->id;
                $lastRequest = $requests->get($key);

                if ($lastRequest) {
                    $finalSetorans->push([
                        'jumlah' => $lastRequest->jumlah_baru,
                        'tanggal' => $lastRequest->created_at,
                    ]);
                } elseif ($s->status !== 'batal') {
                    $finalSetorans->push([
                        'jumlah' => $s->jumlah,
                        'tanggal' => $s->tanggal,
                    ]);
                }
            }

            foreach ($n->titipSetorans as $t) {
                $key = 'Titip_' . $t->id;
                $lastRequest = $requests->get($key);

                if ($lastRequest) {
                    $finalSetorans->push([
                        'jumlah' => $lastRequest->jumlah_baru,
                        'tanggal' => $lastRequest->created_at,
                    ]);
                } elseif ($t->status !== 'batal') {
                    $finalSetorans->push([
                        'jumlah' => $t->jumlah,
                        'tanggal' => $t->tanggal_titip,
                    ]);
                }
            }

            // Hitung weeklyChange
            $thisWeek = $finalSetorans
                ->whereBetween('tanggal', [now()->startOfWeek(), now()->endOfWeek()])
                ->sum('jumlah');

            $lastWeek = $finalSetorans
                ->whereBetween('tanggal', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])
                ->sum('jumlah');

            $n->weeklyChange = $lastWeek > 0
                ? round((($thisWeek - $lastWeek) / $lastWeek) * 100, 2)
                : ($thisWeek > 0 ? 100 : 0);

            return $n;
        });

        return Inertia::render('Supervisor/NasabahIndex', [
            'nasabahs' => $nasabahs,
            'blokPasars' => BlokPasar::all(),
            'user' => $request->user(),
            'filters' => ['search' => $search],
        ]);
    }

    public function setSession(Request $request)
    {
        $request->validate([
            'nasabah_id' => 'required|exists:nasabahs,id', // kalau binding pakai id
        ]);

        session(['nasabah_id' => $request->nasabah_id]);

        return redirect()->route('supervisor.nasabah.show');
    }

    public function show()
    {
        $nasabahId = session('nasabah_id');

        if (!$nasabahId) {
            return redirect()->route('supervisor.nasabah.index')
                ->with('error', 'Nasabah belum dipilih.');
        }

        $nasabah = Nasabah::with(['setorans.petugas', 'titipSetorans.petugas', 'blokPasar'])->findOrFail($nasabahId);

        // Ambil semua request update approved terbaru per setoran
        $setoranIds = $nasabah->setorans->pluck('id');
        $titipIds = $nasabah->titipSetorans->pluck('id');

        $requests = SetoranRequest::whereIn('setoranable_id', $setoranIds->concat($titipIds))
            ->where('status', 'approved')
            ->where('type', 'update')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy(fn($r) => ($r->setoranable_type === TitipSetoran::class ? 'Titip_' : 'Reguler_') . $r->setoranable_id)
            ->map(fn($group) => $group->first()); // ambil yang terbaru

        // --- Ambil semua setoran dan update yang final ---
        $finalSetoran = collect();

        foreach ($nasabah->setorans as $s) {
            $key = 'Reguler_' . $s->id;
            $lastRequest = $requests->get($key);

            if ($lastRequest) {
                $finalSetoran->push([
                    'jumlah' => $lastRequest->jumlah_baru,
                    'tanggal' => $lastRequest->created_at,
                    'petugas' => $s->petugas?->name ?? '-',
                    'jenis' => 'Reguler',
                ]);
            } elseif ($s->status !== 'batal') {
                $finalSetoran->push([
                    'jumlah' => $s->jumlah,
                    'tanggal' => $s->tanggal,
                    'petugas' => $s->petugas?->name ?? '-',
                    'jenis' => 'Reguler',
                ]);
            }
        }

        foreach ($nasabah->titipSetorans as $t) {
            $key = 'Titip_' . $t->id;
            $lastRequest = $requests->get($key);

            if ($lastRequest) {
                $finalSetoran->push([
                    'jumlah' => $lastRequest->jumlah_baru,
                    'tanggal' => $lastRequest->created_at,
                    'petugas' => $t->petugas?->name ?? '-',
                    'jenis' => 'Titip',
                ]);
            } elseif ($t->status !== 'batal') {
                $finalSetoran->push([
                    'jumlah' => $t->jumlah,
                    'tanggal' => $t->tanggal_titip,
                    'petugas' => $t->petugas?->name ?? '-',
                    'jenis' => 'Titip',
                ]);
            }
        }

        // --- PERBAIKAN: Ambil setoran terakhir per jenis ---
        $tabelData = collect();
        $finalSetoran->groupBy('jenis')->each(function ($setorans, $jenis) use ($nasabah, &$tabelData) {
            $lastFinal = $setorans->sortByDesc('tanggal')->first();
            if ($lastFinal) {
                $tabelData->push([
                    'id' => $nasabah->id,
                    'tanggal' => Carbon::parse($lastFinal['tanggal'])->toDateString(),
                    'created_at' => Carbon::parse($lastFinal['tanggal'])->format('Y-m-d H:i:s'), // tambahan khusus tabel
                    'petugas' => $lastFinal['petugas'],
                    'nasabah' => $nasabah->nama,
                    'blok' => $nasabah->blokPasar?->nama_blok ?? '-',
                    'jumlah' => $lastFinal['jumlah'],
                    'jenis_setoran' => $jenis, // Reguler atau Titip
                    'status' => 'Approved',
                ]);
            }
        });

        // ===================== GRAFIK =====================
        $allSetoransForGraph = $tabelData
            ->groupBy('tanggal')
            ->map(fn($items, $tanggal) => (object) [
                'tanggal' => $tanggal,
                'jumlah' => collect($items)->sum('jumlah'),
            ])->sortBy('tanggal')->values();

        $grafik = [
            'harian' => $allSetoransForGraph,
            'mingguan' => $allSetoransForGraph
                ->groupBy(fn($s) => Carbon::parse($s->tanggal)->startOfWeek(Carbon::MONDAY)->toDateString())
                ->map(fn($items, $weekStart) => (object) [
                    'tanggal' => $weekStart,
                    'jumlah' => collect($items)->sum('jumlah'),
                ])->values(),
            'bulanan' => $allSetoransForGraph
                ->groupBy(fn($s) => Carbon::parse($s->tanggal)->format('Y-m'))
                ->map(fn($items, $ym) => (object) [
                    'tanggal' => $ym,
                    'jumlah' => collect($items)->sum('jumlah'),
                ])->values(),
            'tahunan' => $allSetoransForGraph
                ->groupBy(fn($s) => Carbon::parse($s->tanggal)->format('Y'))
                ->map(fn($items, $year) => (object) [
                    'tanggal' => $year,
                    'jumlah' => collect($items)->sum('jumlah'),
                ])->values(),
        ];

        return Inertia::render('Supervisor/NasabahDetail', [
            'auth' => ['user' => auth()->user()],
            'nasabah' => $nasabah,
            'grafik' => $grafik,
            'tabel' => $tabelData,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'nama_umplung' => 'required|string|max:255',
            'nomor_rekening' => 'required|digits_between:1,20',
            'alamat' => 'nullable|string|max:500',
            'nomor_hp' => 'nullable|digits_between:1,20',
            'blok_pasar_id' => 'required|exists:blok_pasars,id',
        ], [
            'nomor_rekening.digits_between' => 'Nomor rekening harus berupa angka!',
            'nomor_hp.digits_between' => 'Nomor HP harus berupa angka!',
        ]);

        if (Nasabah::where('nomor_rekening', $request->nomor_rekening)->exists()) {
            throw ValidationException::withMessages([
                'nomor_rekening' => ['Nomor rekening sudah terdaftar!'],
            ]);
        }

        $token = (string) Str::uuid();

        $nasabah = Nasabah::create(array_merge($request->all(), [
            'uuid' => $token,
            'qr_token' => $token,
        ]));

        GenerateQrJob::dispatch($nasabah->id);

        // Redirect kembali → Inertia akan tangani response
        return back()->with('success', 'Nasabah ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $nasabah = Nasabah::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:255',
            'nama_umplung' => 'required|string|max:255',
            'nomor_rekening' => 'required|digits_between:1,20',
            'alamat' => 'nullable|string|max:500',
            'nomor_hp' => 'nullable|digits_between:1,20',
            'blok_pasar_id' => 'required|exists:blok_pasars,id',
        ], [
            'nomor_rekening.digits_between' => 'Nomor rekening harus berupa angka!',
            'nomor_hp.digits_between' => 'Nomor HP harus berupa angka!',
        ]);

        if (
            Nasabah::where('nomor_rekening', $request->nomor_rekening)
                ->where('id', '!=', $nasabah->id)
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'nomor_rekening' => ['Nomor rekening sudah terdaftar!'],
            ]);
        }

        $nasabah->update($request->all());

        return back()->with('success', 'Nasabah diperbarui!');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        set_time_limit(300);
        ini_set('memory_limit', '1024M');

        $rows = Excel::toArray(new NasabahImport, $request->file('file'))[0];

        // Cache data untuk mengurangi query
        $blokCache = BlokPasar::pluck('id', 'nama_blok')->toArray();
        $existingRekening = Nasabah::pluck('nomor_rekening')->flip()->toArray(); // faster lookup
        $dataToInsert = [];

        foreach ($rows as $row) {
            if (empty(trim($row['nama'] ?? '')))
                continue;

            $nomorRekening = $row['nomor_rekening'] ?? null;
            if (!$nomorRekening || !is_numeric($nomorRekening))
                continue;

            // Cek duplikasi dengan isset (O(1) instead of in_array O(n))
            if (isset($existingRekening[$nomorRekening]))
                continue;

            $blokNama = $row['blok'] ?? 'Tanpa Blok';
            $blokId = $blokCache[$blokNama] ?? null;

            if (!$blokId) {
                $blok = BlokPasar::create(['nama_blok' => $blokNama]);
                $blokId = $blok->id;
                $blokCache[$blokNama] = $blokId;
            }

            $token = (string) Str::uuid();

            $dataToInsert[] = [
                'nama' => $row['nama'],
                'nama_umplung' => $row['nama_umplung'] ?? '',
                'alamat' => $row['alamat'] ?? '',
                'nomor_hp' => $row['nomor_hp'] ?? '',
                'nomor_rekening' => $nomorRekening,
                'blok_pasar_id' => $blokId,
                'uuid' => $token,
                'qr_token' => $token,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $existingRekening[$nomorRekening] = true;
        }

        if (!empty($dataToInsert)) {
            $totalInserted = 0;

            foreach (array_chunk($dataToInsert, 500) as $chunk) {
                Nasabah::insert($chunk);
                $totalInserted += count($chunk);
            }

            // Jalankan proses generate QR di background
            $artisanPath = base_path('artisan');
            exec("php $artisanPath nasabah:generate-qrcodes > /dev/null 2>&1 &");

            return back()->with('success', "Import nasabah berhasil. Total: {$totalInserted} data. QR code sedang diproses di background.");
        }


        return back()->withErrors(['file' => 'Tidak ada data valid yang bisa diimport.']);
    }

    public function destroy($id)
    {
        $nasabah = Nasabah::findOrFail($id);

        // hapus file qr langsung di controller
        if (!empty($nasabah->qr_path) && Storage::disk('local')->exists($nasabah->qr_path)) {
            Storage::disk('local')->delete($nasabah->qr_path);
        }

        // lalu hapus modelnya
        $nasabah->forceDelete(); // atau delete() biasa

        return back()->with('success', 'Nasabah dan QR code terhapus.');
    }

    /**
     * Generate QR PNG + teks nama_umplung, simpan permanen di storage/app/qrcodes.
     */
    // Hapus private, atau pindahkan ke service/trait
    public function generateQrWithText(Nasabah $nasabah): void
    {
        $qrDir = storage_path('app/qrcodes');

        if (!is_dir($qrDir)) {
            mkdir($qrDir, 0755, true);
        }

        $token = $nasabah->qr_token;
        $text = trim((string) ($nasabah->nama_umplung ?: $nasabah->nama)) ?: 'nasabah';
        $fileName = "{$token}.png";
        $filePath = $qrDir . '/' . $fileName;

        $tempFile = tempnam(sys_get_temp_dir(), 'qr_') . '.png';

        QrCode::format('png')
            ->size(200)
            ->margin(1)
            ->generate(url("/nasabah/by-qr/{$token}"), $tempFile);

        $this->addTextBelowQr($tempFile, $text, $filePath);

        if (file_exists($tempFile)) {
            unlink($tempFile);
        }

        $nasabah->update(['qr_path' => "qrcodes/{$fileName}"]);
    }

    /**
     * Tambahkan teks di bawah QR dan simpan ke $outputPath
     */
    private function addTextBelowQr(string $inputPath, string $text, string $outputPath): void
    {
        $qrImg = imagecreatefrompng($inputPath);
        if (!$qrImg) {
            throw new \Exception("Gagal membaca QR image dari $inputPath");
        }

        $qrW = imagesx($qrImg);
        $qrH = imagesy($qrImg);

        $bottomSpace = 60;
        $w = $qrW;
        $h = $qrH + $bottomSpace;

        $img = imagecreatetruecolor($w, $h);

        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 0, 0, 0);

        // Background putih
        imagefilledrectangle($img, 0, 0, $w, $h, $white);

        // Tempel QR di atas
        imagecopy($img, $qrImg, 0, 0, 0, 0, $qrW, $qrH);

        // Tulis teks di bawah
        $font = 5;
        $textWidth = imagefontwidth($font) * strlen($text);
        $textHeight = imagefontheight($font);
        $x = max(0, (int) (($w - $textWidth) / 2));
        $y = (int) ($qrH + ($bottomSpace - $textHeight) / 2);
        imagestring($img, $font, $x, $y, $text, $black);

        // Simpan ke file output permanen
        imagepng($img, $outputPath);

        imagedestroy($img);
        imagedestroy($qrImg);
    }

}
