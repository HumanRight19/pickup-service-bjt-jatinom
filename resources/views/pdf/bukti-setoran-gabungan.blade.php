<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" name="csrf-token" content="{{ csrf_token() }}">
    <title>Bukti {{ $tipe === 'titip' ? 'Titip ' : '' }}Setoran</title>
    <style>
        @page { size: 58mm auto; margin: 0; }
        body, html {
            margin: 0; padding: 0; width: 220px;
            font-family: Arial, sans-serif; font-size: 10px; line-height: 1.5; color: #000;
        }
        .section { 
            padding: 4px 8px; 
        }
        table.header-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 8px; 
        }
        td.logo-cell { 
            width: 60%; 
            padding-right: 8px; 
            vertical-align: top; 
        }
        td.date-cell { 
            width: 40%; 
            text-align: right; 
            font-size: 12px; 
            white-space: nowrap; 
            vertical-align: top; 
            padding-left: 8px; 
        }
        table.data-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 0 0 12px 0; 
            word-wrap: break-word; 
            page-break-inside: avoid; 
        }
        td.label { 
            font-weight: bold; 
            width: 40%; 
            padding-right: 2px; 
            vertical-align: top; 
            font-size: 14px; 
        }
        td.value { 
            width: 60%; 
            white-space: normal; 
            vertical-align: top; 
            font-size: 14px; 
            text-align: right; 
        }
        td, th { 
            padding: 2px 0; 
        }
        .center.title-main { 
            text-align: center; 
            font-weight: bold; 
            margin: 12px 0 4px; 
            font-size: 14px; 
        }
        .copy-title { 
            font-size: 9px; 
            text-align: center; 
            font-weight: bold; 
            margin-bottom: 18px; 
        }
        .center.amount { 
            margin-bottom: 14px; 
            font-size: 14px; 
            font-weight: bold; 
            text-align: center; 
        }
        img.logo { 
            height: 40px; 
            width: auto; 
            display: block; 
            margin: 0 auto 4px; 
        }
        .line { 
            border-top: 1px dashed #000; 
            margin: 8px 0; 
        }
        .separator { 
            border-top: 1px dashed #000; 
            margin: 8px 0; 
            text-align: center; 
            font-size: 8px; 
            font-style: italic; 
        }
        .footer { 
            text-align: center; 
            font-size: 12px; 
            margin-top: 6px; 
            line-height: 1.2; }
    </style>
</head>
<body>

@php
    $tanggal = now()->format('d-m-Y');
    $jam = now()->format('H:i');

    // bedain field tanggal
    $tanggalSetoran = \Carbon\Carbon::parse(
        $tipe === 'titip' ? $model->tanggal_titip : $model->tanggal
    )->format('d/m/Y');

    $jumlahSetoran = $model->jumlah ?? 0;
    $nasabah = $model->nasabah; // 🔑 cukup ambil dari relasi
@endphp


{{-- RESI PETUGAS --}}
<div class="section">
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                @if(app()->runningInConsole())
                    {{-- kalau dipanggil lewat console (PDF/dompdf) --}}
                    <img src="{{ public_path('images/logo.png') }}" alt="Logo" width="80">
                @else
                    {{-- kalau preview di browser --}}
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" width="80">
                @endif
            </td>
            <td class="date-cell">
                {{ $tanggal }}<br>
                {{ $jam }}
            </td>
        </tr>
    </table>

    <div class="center title-main">
        TANDA TERIMA {{ strtoupper($tipe === 'titip' ? 'TITIP SETORAN' : 'SETORAN') }}
    </div>
    <div class="copy-title">Untuk PETUGAS</div>

    <div class="center amount">
        Rp{{ number_format($jumlahSetoran, 0, ',', '.') }}
    </div>

    <table class="data-table">
        <tr><td class="label">Nama</td><td class="value">{{ $nasabah->nama }}</td></tr>
        <tr><td class="label">Umplung</td><td class="value">{{ $nasabah->nama_umplung }}</td></tr>
        <tr><td class="label">No. Rekening</td><td class="value">{{ $nasabah->nomor_rekening }}</td></tr>
        <tr><td class="label">Blok</td><td class="value">{{ $nasabah->blokPasar->nama_blok ?? '-' }}</td></tr>
        <tr><td class="label">Tanggal</td><td class="value">{{ $tanggalSetoran }}</td></tr>
        <tr><td class="label">Petugas</td><td class="value">{{ $petugas->name }}</td></tr>
    </table>

    <div class="line"></div>
    <div class="footer">Cabang Pembantu Jatinom<br>Klaten</div>
</div>

{{-- PEMISAH --}}
<div class="separator">--- POTONG DI SINI ---</div>

{{-- RESI NASABAH --}}
<div class="section">
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                 @if(app()->runningInConsole())
                    {{-- kalau dipanggil lewat console (PDF/dompdf) --}}
                    <img src="{{ public_path('images/logo.png') }}" alt="Logo" width="80">
                @else
                    {{-- kalau preview di browser --}}
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" width="80">
                @endif
            </td>
            <td class="date-cell">
                {{ $tanggal }}<br>
                {{ $jam }}
            </td>
        </tr>
    </table>

    <div class="center title-main">
        TANDA TERIMA <br>
        {{ strtoupper($tipe === 'titip' ? 'TITIP SETORAN' : 'SETORAN') }}
    </div>

    <div class="copy-title">Untuk NASABAH</div>

    <div class="center amount">
        Rp{{ number_format($jumlahSetoran, 0, ',', '.') }}
    </div>

    <table class="data-table">
        <tr><td class="label">Nama</td><td class="value">{{ $nasabah->nama }}</td></tr>
        <tr><td class="label">Umplung</td><td class="value">{{ $nasabah->nama_umplung }}</td></tr>
        <tr><td class="label">No. Rekening</td><td class="value">{{ $nasabah->nomor_rekening }}</td></tr>
        <tr><td class="label">Blok</td><td class="value">{{ $nasabah->blokPasar->nama_blok ?? '-' }}</td></tr>
        <tr><td class="label">Tanggal</td><td class="value">{{ $tanggalSetoran }}</td></tr>
        <tr><td class="label">Petugas</td><td class="value">{{ $petugas->name }}</td></tr>
    </table>

    <div class="line"></div>
    <div class="footer">Cabang Pembantu Jatinom<br>Klaten</div>
</div>

<script>
    window.onload = function() { window.print(); };
</script>
</body>
</html>
