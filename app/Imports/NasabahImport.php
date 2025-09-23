<?php

namespace App\Imports;

use Illuminate\Support\Str;
use App\Models\Nasabah;
use App\Models\BlokPasar;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Validation\ValidationException;

class NasabahImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // 1. Validasi semua kolom wajib
        $requiredFields = ['nama', 'nama_umplung', 'alamat', 'nomor_hp', 'nomor_rekening', 'blok'];
        foreach ($requiredFields as $field) {
            if (!isset($row[$field]) || trim($row[$field]) === '') {
                throw ValidationException::withMessages([
                    $field => "Kolom '$field' wajib diisi di setiap baris."
                ]);
            }
        }

        // 2. Normalisasi data dari Excel (trim + string cast)
        $nama = trim((string) $row['nama']);
        $namaUmplung = trim((string) $row['nama_umplung']);
        $alamat = trim((string) $row['alamat']);
        $nomorHp = trim((string) $row['nomor_hp']);
        $nomorRekening = trim((string) $row['nomor_rekening']);
        $blokNama = trim((string) $row['blok']);

        // 3. Pastikan blok pasar ada
        $blok = BlokPasar::firstOrCreate(
            ['nama_blok' => $blokNama],
            ['keterangan' => null]
        );

        // 4. Cari berdasarkan nomor rekening (unik)
        $existing = Nasabah::where('nomor_rekening', $nomorRekening)->first();

        if ($existing) {
            $isDifferent =
                $existing->nama !== $nama ||
                $existing->nama_umplung !== $namaUmplung ||
                $existing->alamat !== $alamat ||
                $existing->nomor_hp !== $nomorHp ||
                $existing->blok_pasar_id !== $blok->id;

            if ($isDifferent) {
                $existing->update([
                    'nama' => $nama,
                    'nama_umplung' => $namaUmplung,
                    'alamat' => $alamat,
                    'nomor_hp' => $nomorHp,
                    'blok_pasar_id' => $blok->id,
                ]);
            }

            return null; // Skip insert baru
        }

        // 5. Insert baru
        return new Nasabah([
            'nama' => $nama,
            'nama_umplung' => $namaUmplung,
            'alamat' => $alamat,
            'nomor_hp' => $nomorHp,
            'nomor_rekening' => $nomorRekening,
            'blok_pasar_id' => $blok->id,
            'uuid' => Str::uuid(), // Asumsi kamu pakai UUID
        ]);
    }
}
