<?php

namespace App\Exports;

use App\Models\Setoran;
use App\Models\TitipSetoran;
use App\Models\SetoranRequest;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Events\AfterSheet;

class LaporanFinalSheet implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize, WithStyles, WithTitle, WithEvents
{
    protected $start;
    protected $end;
    protected $petugasId;
    protected $blok;
    protected $nasabah;
    protected $supervisor;
    protected $status;
    protected $jenis_setoran;
    protected $dicetakOleh;
    protected $totalJumlah = 0;
    protected $records;

    public function __construct(
        $start,
        $end,
        $petugasId,
        $blok,
        $nasabah,
        $supervisor,
        $status = null,
        $jenis_setoran = null,
        $dicetakOleh
    ) {
        $this->start = $start;
        $this->end = $end;
        $this->petugasId = $petugasId;
        $this->blok = $blok;
        $this->nasabah = $nasabah;
        $this->supervisor = $supervisor;
        $this->status = $status;
        $this->jenis_setoran = $jenis_setoran;
        $this->dicetakOleh = $dicetakOleh;
    }

    public function collection()
    {
        if ($this->records) {
            return $this->records;
        }

        $allSetoran = collect();

        // Ambil Setoran Reguler & Titip
        $reguler = Setoran::with(['nasabah.blokPasar', 'petugas', 'supervisor'])
            ->when($this->petugasId, fn($q) => $q->where('user_id', $this->petugasId))
            ->when($this->blok, fn($q) => $q->whereHas('nasabah.blokPasar', fn($b) => $b->where('nama_blok', $this->blok)))
            ->when($this->nasabah, fn($q) => $q->whereHas('nasabah', fn($n) => $n->where('nama', 'like', "%{$this->nasabah}%")))
            ->when($this->supervisor, fn($q) => $q->where('supervisor_id', $this->supervisor))
            ->whereBetween('created_at', [$this->start, $this->end])
            ->get();

        $titip = TitipSetoran::with(['nasabah.blokPasar', 'petugas', 'supervisor'])
            ->when($this->petugasId, fn($q) => $q->where('petugas_id', $this->petugasId))
            ->when($this->blok, fn($q) => $q->whereHas('nasabah.blokPasar', fn($b) => $b->where('nama_blok', $this->blok)))
            ->when($this->nasabah, fn($q) => $q->whereHas('nasabah', fn($n) => $n->where('nama', 'like', "%{$this->nasabah}%")))
            ->when($this->supervisor, fn($q) => $q->where('supervisor_id', $this->supervisor))
            ->whereBetween('created_at', [$this->start, $this->end])
            ->get();

        foreach ($reguler as $s) {
            $allSetoran->put('Reguler_' . $s->id, [
                'id' => $s->id,
                'tanggal' => $s->created_at,
                'petugas' => $s->petugas?->name ?? '-',
                'nasabah' => $s->nasabah?->nama ?? '-',
                'rekening' => $s->nasabah?->nomor_rekening ?? '-',
                'blok' => $s->nasabah?->blokPasar?->nama_blok ?? '-',
                'umplung' => $s->nasabah?->nama_umplung ?? '-',
                'supervisor' => $s->supervisor?->name ?? '-',
                'jumlah' => $s->jumlah,
                'jenis_setoran' => 'Reguler',
                'status' => 'Normal',
            ]);
        }

        foreach ($titip as $t) {
            $allSetoran->put('Titip_' . $t->id, [
                'id' => $t->id,
                'tanggal' => $t->created_at,
                'petugas' => $t->petugas?->name ?? '-',
                'nasabah' => $t->nasabah?->nama ?? '-',
                'rekening' => $t->nasabah?->nomor_rekening ?? '-',
                'blok' => $t->nasabah?->blokPasar?->nama_blok ?? '-',
                'umplung' => $t->nasabah?->nama_umplung ?? '-',
                'supervisor' => $t->supervisor?->name ?? '-',
                'jumlah' => $t->jumlah,
                'jenis_setoran' => 'Titip',
                'status' => 'Normal',
            ]);
        }

        // Ambil semua request (update / batal) seperti di controller
        $requests = SetoranRequest::with('setoranable')
            ->whereIn('setoranable_id', $reguler->pluck('id')->concat($titip->pluck('id')))
            ->orderBy('created_at')
            ->get();

        foreach ($requests as $r) {
            $setoran = $r->setoranable;
            if (!$setoran)
                continue;

            $jenis = $setoran instanceof TitipSetoran ? 'Titip' : 'Reguler';
            $key = $jenis . '_' . $setoran->id;
            $base = $allSetoran->get($key);

            if (!$base)
                continue;

            $tanggalWaktu = Carbon::parse($r->created_at)->format('Y-m-d H:i:s');

            if ($r->type === 'update' && $r->status === 'approved') {
                $base['jumlah'] = $r->jumlah_baru ?? $base['jumlah'];
                $base['tanggal'] = $r->created_at;
                $base['status'] = 'Update Approved';
                $allSetoran->put($key, $base);
            }

            if ($r->type === 'batal' && $r->status === 'approved') {
                // batal approved → reset status
                $base['status'] = 'Batal Approved';
                $allSetoran->put($key, $base);
            }
        }

        // Ambil hanya record terakhir per jenis + id (final setoran)
        $allSetoran = $allSetoran
            ->filter(fn($x) => $x['status'] !== 'Batal Approved') // <--- tambahkan ini
            ->sortByDesc('tanggal')
            ->groupBy(fn($x) => $x['jenis_setoran'] . '_' . $x['id'])
            ->map(fn($group) => $group->last())
            ->values();

        $this->totalJumlah = $allSetoran->sum(fn($x) => $x['jumlah'] ?? 0);

        return $this->records = $allSetoran;
    }

    public function map($setoran): array
    {
        return [
            $setoran['umplung'] ?? '-',
            $setoran['petugas'] ?? '-',
            $setoran['tanggal'] ? Carbon::parse($setoran['tanggal'])->format('Y-m-d H:i:s') : '-',
            $setoran['nasabah'] ?? '-',
            $setoran['rekening'] ?? '-',
            $setoran['jumlah'] ?? 0,
            $setoran['blok'] ?? '-',
            $setoran['jenis_setoran'] ?? '-',
        ];
    }

    public function headings(): array
    {
        return [
            'Nomor Umplung',
            'Nama Petugas Pickup',
            'Tanggal Layanan',
            'Nama Penabung',
            'Nomor Rekening',
            'Nominal',
            'Blok Pasar',
            'Keterangan',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F81BD']],
                'alignment' => ['wrapText' => true, 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            ],
        ];
    }

    public function title(): string
    {
        return 'Setoran Final';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                // ---- Judul ----
                $sheet->insertNewRowBefore(1, 3);
                $sheet->setCellValue('A1', "Rekap Data " . ucfirst(session('laporan_filter.range', 'harian')));
                $sheet->setCellValue('A2', 'Penabung dengan layanan Pickup Service');
                $sheet->setCellValue('A3', 'Pasar Gabus Jatinom');
                $sheet->mergeCells('A1:H1');
                $sheet->mergeCells('A2:H2');
                $sheet->mergeCells('A3:H3');

                foreach (range(1, 3) as $row) {
                    $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
                    $sheet->getStyle('A' . $row)->getAlignment()
                        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                        ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                        ->setWrapText(true);
                }

                $headerRow = 4;
                $firstDataRow = $headerRow + 1;
                $dataRowsCount = $this->collection()->count();
                $lastDataRow = $headerRow + $dataRowsCount;

                // ---- Border & Alignment (hanya sampai kolom H) ----
                $sheet->getStyle("A{$headerRow}:H{$lastDataRow}")
                    ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                $sheet->getStyle("A{$headerRow}:H{$lastDataRow}")
                    ->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);

                // ---- Format nominal ----
                if ($lastDataRow >= $firstDataRow) {
                    $sheet->getStyle("F{$firstDataRow}:F{$lastDataRow}")
                        ->getNumberFormat()->setFormatCode('"Rp"#,##0');
                }

                // ---- Highlight berdasarkan Jenis (kolom H) ----
                if ($lastDataRow >= $firstDataRow) {
                    foreach (range($firstDataRow, $lastDataRow) as $row) {
                        $jenis = strtolower(trim((string) $sheet->getCell("H{$row}")->getValue()));

                        $jenisColor = match ($jenis) {
                            'reguler' => 'DBEAFE',
                            'titip' => 'FEF9C3',
                            default => null,
                        };
                        if ($jenisColor) {
                            $sheet->getStyle("H{$row}")->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setRGB($jenisColor);
                        }
                    }
                }

                // ---- Total ----
                $totalRow = $lastDataRow + 1;
                $sheet->mergeCells("A{$totalRow}:E{$totalRow}");
                $sheet->setCellValue("A{$totalRow}", 'Total');
                $sheet->setCellValue("F{$totalRow}", $this->totalJumlah);

                // Bold + format angka
                $sheet->getStyle("A{$totalRow}:F{$totalRow}")->getFont()->setBold(true);
                $sheet->getStyle("F{$totalRow}")->getNumberFormat()->setFormatCode('"Rp"#,##0');

                // Border
                $sheet->getStyle("A{$totalRow}:H{$totalRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // 🔥 Alignment Total -> Center
                $sheet->getStyle("A{$totalRow}:H{$totalRow}")
                    ->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);

                // ---- Signature (rapi & sejajar) ----
                // Struktur:
                // - baris BASE: tanggal cetak (F / G)
                // - baris LABELS: 'Petugas' di B dan 'Supervisor' di G (pada baris yang sama)
                // - setelah baris label, beri 3 baris kosong, lalu tulis nama (bold)
                // ---- Signature (rapi & sejajar, semua isi cell center + wrap text) ----
                $base = $totalRow + 3;
                $dateRow = $base;
                $labelsRow = $base + 1;
                $nameRow = $labelsRow + 4;

                $supervisorName = $this->dicetakOleh ?? 'Supervisor';
                $petugasNames = $this->collection()->pluck('petugas')->unique()->values();

                // Tanggal cetak
                $sheet->setCellValue("F{$dateRow}", "Tanggal Cetak:");
                $sheet->setCellValue("G{$dateRow}", now()->format('d-m-Y'));
                $sheet->getStyle("G{$dateRow}")->getFont()->setBold(true);

                // Labels
                $sheet->setCellValue("B" . ($labelsRow - 1), "Mengetahui");
                $sheet->setCellValue("B{$labelsRow}", "Petugas");
                $sheet->setCellValue("G{$labelsRow}", "Supervisor");

                // Nama supervisor
                $sheet->setCellValue("G{$nameRow}", $supervisorName);
                $sheet->getStyle("G{$nameRow}")->getFont()->setBold(true);

                // Petugas
                foreach ($petugasNames as $i => $petugasName) {
                    $blockTop = $labelsRow + ($i * 6);

                    if ($i > 0) {
                        $sheet->setCellValue("B" . ($blockTop - 1), "Mengetahui");
                    }

                    $sheet->setCellValue("B{$blockTop}", "Petugas");
                    $sheet->setCellValue("B" . ($blockTop + 4), $petugasName);
                    $sheet->getStyle("B" . ($blockTop + 4))->getFont()->setBold(true);
                }

                // ---- Alignment semua cell signature ke CENTER + wrap text ----
                $endRowForPetugas = $labelsRow + max(0, ($petugasNames->count() - 1)) * 6 + 4;

                $sheet->getStyle("B" . ($labelsRow - 1) . ":B{$endRowForPetugas}")
                    ->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);

                $sheet->getStyle("F{$dateRow}:G{$endRowForPetugas}")
                    ->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);
            }
        ];
    }

}
