@extends('admin.letters.templates._layout')

@section('body')
<div class="content">
    @if($letter->body_content)
        {!! nl2br(e($letter->body_content)) !!}
    @else
        <p>Dengan hormat,</p>
        <p>Sehubungan dengan kegiatan <strong>{{ $letter->proposal_judul_snapshot ?? '—' }}</strong>
            @if($letter->proposal_number_snapshot) (Nomor: {{ $letter->proposal_number_snapshot }}) @endif
            yang dilaksanakan oleh <strong>{{ $letter->ketua_dosen_nama_snapshot ?? '—' }}</strong>
            @if($letter->prodi_nama_snapshot) dari Prodi {{ $letter->prodi_nama_snapshot }} @endif,
            dengan ini kami memberikan surat tugas kepada yang bersangkutan untuk melaksanakan kegiatan tersebut.</p>
        <p>Demikian surat tugas ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p>
    @endif
</div>
@endsection
