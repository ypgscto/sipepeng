<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; }
        h1 { font-size: 14pt; margin-bottom: 4px; }
        .meta { font-size: 9pt; color: #444; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; }
        th { background: #f1f5f9; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="meta">
        STIKES Gunung Sari — SiPepeng LPPM<br>
        Tahun kalender: {{ $filter->calendarYear ?? '—' }} |
        Prodi: {{ $filter->prodiId ?? 'Semua' }} |
        Dicetak: {{ $generatedAt }}
    </div>
    <table>
        <thead><tr>@foreach($headings as $h)<th>{{ $h }}</th>@endforeach</tr></thead>
        <tbody>
            @foreach($rows as $row)
                <tr>@foreach($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
