<?php

namespace App\Exports;

use App\Models\SetoranRequest;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;
use Maatwebsite\Excel\Events\AfterSheet;

class LaporanHistorySheet implements
    FromCollection,
    WithMapping,
    WithHeadings,
    ShouldAutoSize,
    WithStyles,
    WithTitle,
    WithEvents
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
    protected $records;

    public function __construct($start, $end, $petugasId, $blok, $nasabah, $supervisor, $status = null, $jenis_setoran = null, $dicetakOleh)
    {
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
        if ($this->records)
            return $this->records;

        $requests = SetoranRequest::with(['setoranable.nasabah.blokPasar', 'petugas', 'supervisor'])
            ->whereBetween('created_at', [$this->start, $this->end])
            ->when($this->petugasId, fn($q) => $q->where('petugas_id', $this->petugasId))
            ->when($this->supervisor, fn($q) => $q->where('supervisor_id', $this->supervisor))
            ->get()
            ->map(fn($r) => [
                'tanggal_request' => Carbon::parse($r->created_at)->format('Y-m-d H:i:s'),
                'nasabah' => $r->setoranable->nasabah->nama ?? '-',
                'rekening' => $r->setoranable->nasabah->nomor_rekening ?? '-',
                'petugas' => $r->petugas->name ?? '-',
                'supervisor' => $r->supervisor->name ?? '-',
                'jumlah_lama' => $r->jumlah_lama ?? 0,
                'jumlah_baru' => $r->jumlah_baru ?? 0,
                'jenis_setoran' => $r->setoranable instanceof \App\Models\TitipSetoran ? 'Titip' : 'Reguler',
                'tipe_request' => ucfirst($r->type),
                'status_request' => ucfirst($r->status),
            ]);

        // Filter jenis setoran
        if (!empty($this->jenis_setoran) && $this->jenis_setoran !== 'Semua') {
            $requests = $requests->filter(fn($item) => $item['jenis_setoran'] === $this->jenis_setoran);
        }

        // Filter status
        if (!empty($this->status)) {
            $requests = $requests->filter(fn($item) => strtolower($item['status_request']) === strtolower($this->status));
        }

        $this->records = $requests->sortByDesc('tanggal_request')->values();

        return $this->records;
    }

    public function map($row): array
    {
        return [
            $row['nasabah'] ?? '-',
            $row['petugas'] ?? '-',
            $row['tanggal_request'] ?? '-',
            $row['rekening'] ?? '-',
            $row['jumlah_lama'] ?? 0,
            $row['jumlah_baru'] ?? 0,
            $row['jenis_setoran'] ?? '-',
            $row['tipe_request'] ?? '-',
            $row['status_request'] ?? '-',
        ];
    }

    public function headings(): array
    {
        return [
            'Nama Penabung',
            'Nama Petugas Pickup',
            'Tanggal Request',
            'Nomor Rekening',
            'Jumlah Lama',
            'Jumlah Baru',
            'Jenis Setoran',
            'Tipe Request',
            'Status Request',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F81BD']],
                'alignment' => [
                    'wrapText' => true,
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Setoran History';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                // Judul
                $sheet->insertNewRowBefore(1, 3);
                $sheet->setCellValue('A1', "Riwayat Request " . ucfirst(session('laporan_filter.range', 'harian')));
                $sheet->setCellValue('A2', 'Penabung dengan layanan Pickup Service');
                $sheet->setCellValue('A3', 'Pasar Gabus Jatinom');
                $sheet->mergeCells('A1:I1');
                $sheet->mergeCells('A2:I2');
                $sheet->mergeCells('A3:I3');

                foreach (range(1, 3) as $row) {
                    $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
                    $sheet->getStyle('A' . $row)->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setWrapText(true);
                }

                // Border & alignment
                $headerRow = 4;
                $firstDataRow = $headerRow + 1;
                $dataRowsCount = $this->collection()->count();
                $lastDataRow = $headerRow + $dataRowsCount;

                $sheet->getStyle("A{$headerRow}:I{$lastDataRow}")
                    ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("A{$headerRow}:I{$lastDataRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);

                // Format nominal kolom E & F
                $sheet->getStyle("E{$firstDataRow}:F{$lastDataRow}")
                    ->getNumberFormat()->setFormatCode('"Rp"#,##0');

                // Highlight jenis setoran (G) & status request (I)
                foreach (range($firstDataRow, $lastDataRow) as $row) {
                    $jenis = strtolower(trim((string) $sheet->getCell("G{$row}")->getValue()));
                    $status = strtolower(trim((string) $sheet->getCell("I{$row}")->getValue()));

                    $jenisColor = match ($jenis) {
                        'reguler' => 'DBEAFE',
                        'titip' => 'FEF9C3',
                        default => null,
                    };
                    if ($jenisColor) {
                        $sheet->getStyle("G{$row}")->getFill()->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB($jenisColor);
                    }

                    $statusColor = match ($status) {
                        'pending' => 'EDEDED',
                        'approved' => 'C6EFCE',
                        'rejected' => 'F4CCCC',
                        default => null,
                    };
                    if ($statusColor) {
                        $sheet->getStyle("I{$row}")->getFill()->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB($statusColor);
                    }
                }
            }
        ];
    }
}
