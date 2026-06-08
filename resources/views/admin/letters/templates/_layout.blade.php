<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 2cm 2.5cm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12pt; line-height: 1.5; color: #111; }
        .watermark { position: fixed; top: 40%; left: 15%; font-size: 72pt; color: rgba(200,200,200,0.35); transform: rotate(-30deg); z-index: -1; }
        .kop { text-align: center; border-bottom: 3px double #333; padding-bottom: 8px; margin-bottom: 20px; }
        .kop h1 { font-size: 14pt; margin: 0; text-transform: uppercase; }
        .kop p { margin: 2px 0; font-size: 10pt; }
        .meta { margin-bottom: 20px; }
        .meta table { width: 100%; }
        .meta td { vertical-align: top; padding: 2px 0; }
        .meta .label { width: 80px; }
        .content { text-align: justify; margin: 16px 0; }
        .content p { margin: 0 0 10px; }
        .ttd { margin-top: 40px; width: 100%; }
        .ttd .right { float: right; width: 260px; text-align: center; }
        .ttd .right p { margin: 4px 0; }
        .clear { clear: both; }
    </style>
</head>
<body>
    @if(!empty($watermark))
        <div class="watermark">{{ $watermark }}</div>
    @endif

    <div class="kop">
        <h1>{{ $institution ?? 'STIKES Gunung Sari' }}</h1>
        <p>Lembaga Penelitian dan Pengabdian Masyarakat (LPPM)</p>
        <p>Jl. Pendidikan No. 1, Mataram — Telp. (0370) 000000</p>
    </div>

    <div class="meta">
        <table>
            <tr>
                <td class="label">Nomor</td>
                <td>: {{ $displayNumber }}</td>
                <td style="text-align:right">{{ $placeOfIssue }}, {{ $letterDate }}</td>
            </tr>
            <tr>
                <td class="label">Perihal</td>
                <td colspan="2">: <strong>{{ $letter->perihal }}</strong></td>
            </tr>
        </table>
    </div>

    @yield('body')

    <div class="ttd">
        <div class="right">
            <p>Ketua LPPM,</p>
            <br><br><br>
            <p><strong>________________________</strong></p>
            <p>NIDN. ........................</p>
        </div>
        <div class="clear"></div>
    </div>
</body>
</html>
