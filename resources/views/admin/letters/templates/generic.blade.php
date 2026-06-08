@extends('admin.letters.templates._layout')

@section('body')
<div class="content">
    @if($letter->body_content)
        {!! nl2br(e($letter->body_content)) !!}
    @else
        <p>{!! nl2br(e($letter->perihal)) !!}</p>
    @endif
</div>
@endsection
