@extends('admin.letters.templates._layout')

@section('body')
<div class="content">
    @if($letter->body_content)
        {!! nl2br(e($letter->body_content)) !!}
    @else
        <p>Kepada Yth.<br>Peserta Seminar<br>di Tempat</p>
        <p>Dengan hormat,</p>
        <p>Kami mengundang Bapak/Ibu untuk hadir pada kegiatan seminar dengan detail sebagai berikut:</p>
        <table style="margin:12px 0">
            @if($letter->event_date)<tr><td style="width:120px">Tanggal</td><td>: {{ $letter->event_date->translatedFormat('d F Y') }} @if($letter->event_time) pukul {{ $letter->event_time }} @endif</td></tr>@endif
            @if($letter->event_location)<tr><td>Tempat</td><td>: {{ $letter->event_location }}</td></tr>@endif
        </table>
        @if($letter->event_agenda)<p><strong>Agenda:</strong><br>{!! nl2br(e($letter->event_agenda)) !!}</p>@endif
        <p>Atas perhatian dan kehadiran Bapak/Ibu, kami ucapkan terima kasih.</p>
    @endif
    @if($letter->recipients->isNotEmpty())
        <p><strong>Daftar undangan:</strong></p>
        <ol>@foreach($letter->recipients as $r)<li>{{ $r->name }}@if($r->institution) — {{ $r->institution }}@endif</li>@endforeach</ol>
    @endif
</div>
@endsection
