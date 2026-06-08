@extends('admin.letters.templates._layout')

@section('body')
<div class="content">
    @if($letter->body_content)
        {!! nl2br(e($letter->body_content)) !!}
    @else
        <p>Kepada Yth.<br>Instansi/Pihak Terkait<br>di Tempat</p>
        <p>Dengan hormat,</p>
        <p>Bersama ini kami mohon izin untuk melaksanakan penelitian berjudul <strong>{{ $letter->proposal_judul_snapshot ?? '—' }}</strong>
            oleh <strong>{{ $letter->ketua_dosen_nama_snapshot ?? '—' }}</strong>.</p>
        <p>Atas perkenan dan izin Bapak/Ibu, kami ucapkan terima kasih.</p>
    @endif
</div>
@endsection
