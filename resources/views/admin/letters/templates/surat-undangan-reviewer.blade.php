@extends('admin.letters.templates._layout')

@section('body')
<div class="content">
    @if($letter->body_content)
        {!! nl2br(e($letter->body_content)) !!}
    @else
        <p>Kepada Yth.<br><strong>{{ $letter->reviewer_nama_snapshot ?? 'Reviewer' }}</strong><br>di Tempat</p>
        <p>Dengan hormat,</p>
        <p>Kami mengundang Bapak/Ibu untuk menjadi reviewer proposal
            @if($letter->proposal_judul_snapshot) berjudul <strong>{{ $letter->proposal_judul_snapshot }}</strong> @endif
            @if($letter->proposal_number_snapshot) ({{ $letter->proposal_number_snapshot }}) @endif.</p>
        <p>Atas kesediaan dan kerjasama Bapak/Ibu, kami ucapkan terima kasih.</p>
    @endif
</div>
@endsection
