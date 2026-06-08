@props(['href', 'label' => 'Detail'])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'sipeng-btn-table']) }}>
    {{ $label }}
</a>
