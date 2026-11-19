<?php

namespace App\Http\Controllers;

use App\Support\BrandPalette;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class SeoController extends Controller
{
    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /livewire/',
            'Disallow: /pendaftaran/',
            'Disallow: /cari',
            '',
            'Sitemap: '.route('sitemap'),
        ];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    /**
     * Default 1200x630 share image (WhatsApp, Facebook, X) built from the site
     * name, tagline and brand colour. Cached on disk per combination.
     */
    public function ogImage()
    {
        $name = (string) setting('site_name');
        $tagline = (string) setting('site_tagline');
        $shades = BrandPalette::shades(setting('brand_color'));
        $host = parse_url(config('app.url'), PHP_URL_HOST) ?: request()->getHost();
        $path = 'og/default-'.md5(implode('|', [$name, $tagline, $shades[700], $host, 'v1'])).'.png';
        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            $disk->put($path, $this->render($name, $tagline, $host, $shades));
        }

        return response($disk->get($path), 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    private function render(string $name, string $tagline, string $host, array $shades): string
    {
        $w = 1200;
        $h = 630;
        $img = imagecreatetruecolor($w, $h);
        $color = fn (string $hex, int $alpha = 0) => imagecolorallocatealpha($img, ...[...sscanf($hex, '#%02x%02x%02x'), $alpha]);
        $bold = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans-Bold.ttf');
        $regular = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf');

        imagefilledrectangle($img, 0, 0, $w, $h, $color($shades[700]));
        imagefilledrectangle($img, 0, $h - 110, $w, $h, $color($shades[900]));
        imagefilledrectangle($img, 80, 150, 200, 158, $color($shades[300]));

        $white = $color('#ffffff');
        $soft = $color($shades[100]);
        $y = 250;
        foreach ($this->wrap($name, $bold, 76, $w - 160) as $line) {
            imagettftext($img, 76, 0, 80, $y, $white, $bold, $line);
            $y += 96;
        }
        foreach (array_slice($this->wrap($tagline, $regular, 34, $w - 160), 0, 2) as $line) {
            imagettftext($img, 34, 0, 80, $y + 10, $soft, $regular, $line);
            $y += 50;
        }
        imagettftext($img, 26, 0, 80, $h - 45, $soft, $regular, $host);

        ob_start();
        imagepng($img);
        imagedestroy($img);

        return (string) ob_get_clean();
    }

    /** Greedy word wrap to a pixel width. */
    private function wrap(string $text, string $font, int $size, int $maxWidth): array
    {
        $lines = [];
        $line = '';
        foreach (preg_split('/\s+/', trim($text)) as $word) {
            $try = $line === '' ? $word : $line.' '.$word;
            $box = imagettfbbox($size, 0, $font, $try);
            if ($line !== '' && ($box[2] - $box[0]) > $maxWidth) {
                $lines[] = $line;
                $line = $word;
            } else {
                $line = $try;
            }
        }

        return $line === '' ? $lines : [...$lines, $line];
    }
}
