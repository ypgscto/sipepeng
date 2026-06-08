@extends('admin.letters.templates._layout')

@section('body')
<div class="content">
    @if($letter->body_content)
        {!! nl2br(e($letter->body_content)) !!}
    @else
        <p>Kepada Yth.<br><strong>{{ $letter->mitra_nama_snapshot ?? 'Mitra' }}</strong><br>{{ $letter->mitra_alamat_snapshot ?? '' }}</p>
        <p>Dengan hormat,</p>
        <p>Bersama surat ini kami sampaikan pengantar kegiatan kerjasama
            @if($letter->proposal_judul_snapshot) terkait <strong>{{ $letter->proposal_judul_snapshot }}</strong> @endif
            yang melibatkan LPPM {{ $institution ?? 'STIKES Gunung Sari' }}.</p>
        <p>Demikian surat pengantar ini kami sampaikan. Atas perhatian dan kerjasamanya kami ucapkan terima kasih.</p>
    @endif
</div>
@endsection
