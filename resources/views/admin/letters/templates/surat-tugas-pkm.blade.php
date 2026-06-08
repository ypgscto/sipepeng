@extends('admin.letters.templates._layout')

@section('body')
<div class="content">
    @if($letter->body_content)
        {!! nl2br(e($letter->body_content)) !!}
    @else
        <p>Dengan hormat,</p>
        <p>Sehubungan dengan kegiatan Pengabdian Masyarakat berjudul <strong>{{ $letter->proposal_judul_snapshot ?? '—' }}</strong>
            @if($letter->proposal_number_snapshot) (Nomor: {{ $letter->proposal_number_snapshot }}) @endif
            yang dipimpin oleh <strong>{{ $letter->ketua_dosen_nama_snapshot ?? '—' }}</strong>,
            dengan ini kami memberikan surat tugas pengabdian kepada yang bersangkutan.</p>
        @if($letter->mitra_nama_snapshot)
            <p>Mitra: <strong>{{ $letter->mitra_nama_snapshot }}</strong>@if($letter->mitra_alamat_snapshot), {{ $letter->mitra_alamat_snapshot }}@endif.</p>
        @endif
        <p>Demikian surat tugas ini dibuat untuk dipergunakan sebagaimana mestinya.</p>
    @endif
</div>
@endsection
