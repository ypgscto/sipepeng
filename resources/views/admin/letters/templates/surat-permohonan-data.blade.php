@extends('admin.letters.templates._layout')

@section('body')
<div class="content">
    @if($letter->body_content)
        {!! nl2br(e($letter->body_content)) !!}
    @else
        <p>Kepada Yth.<br><strong>{{ $letter->recipient_external_name ?? '—' }}</strong><br>{{ $letter->recipient_external_institution ?? '' }}<br>{{ $letter->recipient_external_address ?? '' }}</p>
        <p>Dengan hormat,</p>
        <p>Sehubungan dengan keperluan penelitian/pengabdian di {{ $institution ?? 'STIKES Gunung Sari' }}, kami mohon bantuan data/informasi sebagaimana diperlukan.</p>
        <p>Atas kerjasama dan perkenan Bapak/Ibu, kami ucapkan terima kasih.</p>
    @endif
</div>
@endsection
