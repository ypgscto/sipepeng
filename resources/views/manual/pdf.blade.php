<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>SOP {{ $meta['title'] }} — SiPepeng</title>
    <style>
        @include('manual.partials.pdf-styles')
    </style>
</head>
<body>
    <header class="pdf-header">
        <h1>{{ $meta['title'] }}</h1>
        <p>{{ config('sipepeng_manual.title') }} · Versi {{ $manualMeta['version'] }} · {{ $manualMeta['updated_at'] }}</p>
        <p class="muted">{{ $meta['summary'] }}</p>
    </header>

    <main class="manual-prose">
        @include("manual.modules.{$module}")
        @include('manual.partials.footer-note')
    </main>
</body>
</html>
