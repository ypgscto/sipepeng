@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'mb-4 text-sm font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg px-4 py-3']) }}>
        {{ $status }}
    </div>
@endif
