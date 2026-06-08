@props([
    'label',
    'status' => null,
    'variant' => null,
])

@php
    use App\Support\Ui\StatusBadgeVariant;

    $resolvedVariant = $variant ?? StatusBadgeVariant::resolve($status);
    $classes = StatusBadgeVariant::classes($resolvedVariant);
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold whitespace-nowrap {$classes}"]) }}>
    {{ $label }}
</span>
