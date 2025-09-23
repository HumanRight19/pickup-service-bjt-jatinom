<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\LaporanFinalSheet;
use App\Exports\LaporanHistorySheet;

class LaporanSetoranExport implements WithMultipleSheets
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

    public function sheets(): array
    {
        return [
            new LaporanFinalSheet(
                $this->start,
                $this->end,
                $this->petugasId,
                $this->blok,
                $this->nasabah,
                $this->supervisor,
                $this->status,
                $this->jenis_setoran,
                $this->dicetakOleh
            ),
            new LaporanHistorySheet(
                $this->start,
                $this->end,
                $this->petugasId,
                $this->blok,
                $this->nasabah,
                $this->supervisor,
                $this->status,
                $this->jenis_setoran,
                $this->dicetakOleh
            ),
        ];
    }
}
