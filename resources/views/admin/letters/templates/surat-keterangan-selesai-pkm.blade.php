@extends('admin.letters.templates._layout')

@section('body')
<div class="content">
    @if($letter->body_content)
        {!! nl2br(e($letter->body_content)) !!}
    @else
        <p>Yang bertanda tangan di bawah ini, Ketua LPPM {{ $institution ?? 'STIKES Gunung Sari' }}, menerangkan bahwa:</p>
        <p>Kegiatan pengabdian masyarakat berjudul <strong>{{ $letter->proposal_judul_snapshot ?? '—' }}</strong>
            @if($letter->proposal_number_snapshot) ({{ $letter->proposal_number_snapshot }}) @endif
            yang dipimpin oleh <strong>{{ $letter->ketua_dosen_nama_snapshot ?? '—' }}</strong>
            telah selesai dilaksanakan.</p>
        <p>Demikian surat keterangan ini dibuat untuk dipergunakan sebagaimana mestinya.</p>
    @endif
</div>
@endsection
