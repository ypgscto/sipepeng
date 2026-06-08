<footer {{ $attributes->merge(['class' => 'sipeng-footer w-full']) }}>
    <div class="px-4 py-2.5 sm:py-3 text-center">
        <p class="text-sm sm:text-base font-bold text-white tracking-wide uppercase">
            {{ $sipengBranding['footer_credit'] ?? 'YPGS IT DIVISION' }}
        </p>
    </div>
</footer>
