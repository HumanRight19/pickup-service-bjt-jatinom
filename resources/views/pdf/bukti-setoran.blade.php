<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Bukti Setoran Gabungan</title>
    <style>
        body, table, tr, td, div {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: sans-serif;
            font-size: 10px;
            margin: 0;
            width: 100%;
        }
        .center { text-align: center; }
        .line { border-top: 1px dashed #000; margin: 6px 0; }
        .info { width: 100%; border-collapse: collapse; margin-top: 6px; }
        .info td { padding: 3px 0; vertical-align: top; font-size: 10px; }
        .info td.label { font-weight: bold; width: 65%; }
        .footer { font-size: 9px; margin-top: 10px; }
        .copy-title { font-size: 9px; text-align: center; margin: 4px 0 8px; font-style: italic; }
        img.logo { height: 30px; }
        .small { font-size: 9px; }
        .text-right { text-align: right; }
        .title { margin: 6px 0; }
        .amount { margin-bottom: 8px; }
        .separator {
            border-top: 1px dashed #000;
            margin: 10px 0;
            text-align: center;
            font-size: 9px;
            font-style: italic;
        }
    </style>
</head>

<body style="margin:0;padding:0;overflow:hidden">

@php
    $tanggal = now()->format('d-m-Y');
    $jam = now()->format('H:i');
@endphp

{{-- ====== RESI PETUGAS ====== --}}
<div class="section">
    <table class="info">
        <tr>
            <td style="width: 60%">
                <img src="{{ public_path('images/logo.png') }}" alt="Logo" class="logo">
            </td>
            <td class="text-right small">
                {{ $tanggal }}<br>
                {{ $jam }}
            </td>
        </tr>
    </table>

    <div class="center title"><strong>TANDA TERIMA PICKUP</strong></div>
    <div class="copy-title">Untuk PETUGAS</div>

    <div class="center amount"><strong>Rp{{ number_format($setoran->jumlah, 0, ',', '.') }}</strong></div>

    <table class="info">
        <tr><td class="label">Nama</td><td>{{ $nasabah->nama }}</td></tr>
        <tr><td class="label">Umplung</td><td>{{ $nasabah->nama_umplung }}</td></tr>
        <tr><td class="label">No. Rekening</td><td>{{ $nasabah->nomor_rekening }}</td></tr>
        <tr><td class="label">Blok</td><td>{{ $nasabah->blokPasar->nama_blok ?? '-' }}</td></tr>
        <tr><td class="label">Tanggal</td><td>{{ \Carbon\Carbon::parse($setoran->tanggal)->format('d/m/Y') }}</td></tr>
        <tr><td class="label">Petugas</td><td>{{ $petugas->name }}</td></tr>
    </table>

    <div class="line"></div>
    <div class="center footer">Cabang Pembantu Jatinom Klaten</div>
</div>

{{-- ====== PEMISAH ====== --}}
<div class="separator">--- POTONG DI SINI ---</div>

{{-- ====== RESI NASABAH ====== --}}
<div class="section">
    <table class="info">
        <tr>
            <td style="width: 60%">
                <img src="{{ public_path('images/logo.png') }}" alt="Logo" class="logo">
            </td>
            <td class="text-right small">
                {{ $tanggal }}<br>
                {{ $jam }}
            </td>
        </tr>
    </table>

    <div class="center title"><strong>TANDA TERIMA PICKUP</strong></div>
    <div class="copy-title">Untuk NASABAH</div>

    <div class="center amount"><strong>Rp{{ number_format($setoran->jumlah, 0, ',', '.') }}</strong></div>

    <table class="info">
        <tr><td class="label">Nama</td><td>{{ $nasabah->nama }}</td></tr>
        <tr><td class="label">Umplung</td><td>{{ $nasabah->nama_umplung }}</td></tr>
        <tr><td class="label">No. Rekening</td><td>{{ $nasabah->nomor_rekening }}</td></tr>
        <tr><td class="label">Blok</td><td>{{ $nasabah->blokPasar->nama_blok ?? '-' }}</td></tr>
        <tr><td class="label">Tanggal</td><td>{{ \Carbon\Carbon::parse($setoran->tanggal)->format('d/m/Y') }}</td></tr>
        <tr><td class="label">Petugas</td><td>{{ $petugas->name }}</td></tr>
    </table>

    <div class="line"></div>
    <div class="center footer">Cabang Pembantu Jatinom Klaten</div>
</div>

</body>
</html>
