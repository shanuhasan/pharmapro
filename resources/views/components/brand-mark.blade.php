{{-- M PharmaPro monogram: gradient tile, single-stroke "M", gold cross nestled between the peaks. --}}
@php($uid = 'bm' . substr(md5(uniqid('', true)), 0, 6))
<svg viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="M PharmaPro" {{ $attributes->has('class') ? $attributes : $attributes->merge(['class' => 'w-9 h-9']) }}>
    <defs>
        <linearGradient id="{{ $uid }}-bg" x1="8" y1="4" x2="56" y2="60" gradientUnits="userSpaceOnUse">
            <stop offset="0" stop-color="#2DD4BF"/>
            <stop offset=".55" stop-color="#0D9488"/>
            <stop offset="1" stop-color="#065F46"/>
        </linearGradient>
        <linearGradient id="{{ $uid }}-shine" x1="32" y1="0" x2="32" y2="34" gradientUnits="userSpaceOnUse">
            <stop offset="0" stop-color="#fff" stop-opacity=".28"/>
            <stop offset="1" stop-color="#fff" stop-opacity="0"/>
        </linearGradient>
        <linearGradient id="{{ $uid }}-gold" x1="27" y1="11" x2="37" y2="25" gradientUnits="userSpaceOnUse">
            <stop offset="0" stop-color="#FEF3C7"/>
            <stop offset="1" stop-color="#F59E0B"/>
        </linearGradient>
    </defs>
    <rect width="64" height="64" rx="16" fill="url(#{{ $uid }}-bg)"/>
    <rect x="1" y="1" width="62" height="62" rx="15" fill="url(#{{ $uid }}-shine)"/>
    <rect x=".5" y=".5" width="63" height="63" rx="15.5" fill="none" stroke="#fff" stroke-opacity=".18"/>
    <path d="M17 46V22.5L32 38L47 22.5V46" fill="none" stroke="#fff" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M30 12.5h4v4.5h4.5v4h-4.5v4.5h-4v-4.5h-4.5v-4h4.5z" fill="url(#{{ $uid }}-gold)"/>
</svg>
