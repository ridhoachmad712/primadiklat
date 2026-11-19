{{-- Approximation of a Google result for the SEO panel. Inline styles: Filament's theme does not scan this file. --}}
<div style="font-family: Arial, sans-serif; max-width: 100%; padding: 12px 14px; border-radius: 10px; background: #fff; border: 1px solid #e5e7eb;">
    @if($noindex)
        <p style="margin: 0 0 8px; font-size: 12px; color: #b45309;">Halaman ini disembunyikan dari mesin pencari.</p>
    @endif
    <p style="margin: 0; font-size: 12px; color: #202124; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $url }}</p>
    <p style="margin: 2px 0 3px; font-size: 18px; line-height: 1.3; color: #1a0dab;">{{ $title }}</p>
    <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #4d5156;">{{ $description }}</p>
    @if($titleTooLong)
        <p style="margin: 8px 0 0; font-size: 12px; color: #b91c1c;">Judul kemungkinan terpotong di Google. Persingkat Judul SEO.</p>
    @endif
</div>
