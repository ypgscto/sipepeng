@extends('admin.letters.templates._layout')

@section('body')
<div class="content">
    @if($letter->body_content)
        {!! nl2br(e($letter->body_content)) !!}
    @else
        <p>Yang bertanda tangan di bawah ini, Ketua LPPM {{ $institution ?? 'STIKES Gunung Sari' }}, menerangkan bahwa:</p>
        <p>Luaran ilmiah/HKI terkait kegiatan
            @if($letter->proposal_judul_snapshot) <strong>{{ $letter->proposal_judul_snapshot }}</strong> @endif
            telah terdaftar dan diverifikasi oleh LPPM.</p>
        <p>Demikian surat keterangan luaran ini dibuat untuk dipergunakan sebagaimana mestinya.</p>
    @endif
</div>
@endsection
