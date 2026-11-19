<?php

namespace App\Filament\Forms;

use App\Support\SeoAnalyzer;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Shared SEO fields: meta title/description with live counters, a Google
 * result preview, optional image alt text, and a noindex switch.
 */
class SeoFields
{
    public const TITLE_LIMIT = 60;

    public const DESCRIPTION_LIMIT = 160;

    public static function make(
        string $route,
        string $bodyField = 'content',
        ?string $excerptField = 'excerpt',
        bool $metaTitle = true,
        bool $imageAlt = false,
        bool $analyzer = false,
        bool $wide = true,
    ): array {
        $count = fn (?string $state) => mb_strlen((string) $state);

        $fields = array_values(array_filter([
            $analyzer ? TextInput::make('focus_keyword')->label('Kata kunci')->maxLength(255)
                ->live(debounce: 600)
                ->placeholder('mis. laporan keuangan daerah, bimtek keuangan, SIPD')
                ->helperText('Kata kunci pertama adalah kata kunci utama dan dinilai lengkap. Tambahkan hingga '.SeoAnalyzer::MAX_SECONDARY.' kata kunci pendukung setelah koma. Tidak tampil di halaman.') : null,

            $metaTitle ? TextInput::make('meta_title')->label('Judul SEO')->maxLength(70)
                ->live(debounce: 500)
                ->placeholder('Kosongkan untuk memakai judul')
                ->hint(fn (?string $state) => $count($state).' / '.self::TITLE_LIMIT)
                ->hintColor(fn (?string $state) => $count($state) > self::TITLE_LIMIT ? 'danger' : 'gray')
                ->helperText('Sebaiknya maksimal '.self::TITLE_LIMIT.' karakter dan memuat kata kunci utama di awal.') : null,

            Textarea::make('meta_description')->label('Deskripsi SEO')->rows(3)->maxLength(self::DESCRIPTION_LIMIT)
                ->live(debounce: 500)
                ->placeholder('Kosongkan untuk memakai ringkasan atau awal isi')
                ->hint(fn (?string $state) => $count($state).' / '.self::DESCRIPTION_LIMIT)
                ->hintColor(fn (?string $state) => filled($state) && $count($state) < 70 ? 'warning' : 'gray')
                ->helperText('Ideal 70 sampai 160 karakter: jelaskan isi halaman dan ajak orang mengklik.'),

            $imageAlt ? TextInput::make('image_alt')->label('Teks alternatif gambar')->maxLength(150)
                ->live(debounce: 800)
                ->helperText('Deskripsikan isi gambar untuk Google Gambar dan pembaca layar. Kosong = memakai judul.') : null,

            Toggle::make('noindex')->label('Sembunyikan dari mesin pencari')
                ->helperText('Halaman tetap bisa dibuka lewat tautan, tetapi Google diminta tidak menampilkannya dan halaman dikeluarkan dari sitemap.'),
        ]));

        $insights = array_values(array_filter([
            $analyzer ? Placeholder::make('seo_score')->hiddenLabel()
                ->content(fn (Get $get) => new HtmlString(view('filament.seo-score', static::analysis($get, $bodyField, $excerptField))->render())) : null,

            Placeholder::make('seo_preview')->label('Pratinjau di Google')
                ->content(fn (Get $get) => static::preview($get, $route, $bodyField, $excerptField)),
        ]));

        // Wide screens: inputs on the left, score + Google preview on the right.
        return [
            Grid::make(['default' => 1, 'lg' => $wide ? 2 : 1])->schema([
                Group::make($fields),
                Group::make($insights),
            ]),
        ];
    }

    /**
     * Score badge fixed to the bottom-right corner of the screen. Place it in a
     * section that is never collapsed, at the same state level as the fields.
     */
    public static function badge(string $bodyField = 'content', ?string $excerptField = 'excerpt'): Placeholder
    {
        return Placeholder::make('seo_badge')->hiddenLabel()
            ->extraAttributes(['style' => 'height: 0; margin: 0; overflow: visible;'])
            ->content(fn (Get $get) => new HtmlString(view('filament.seo-badge', static::analysis($get, $bodyField, $excerptField))->render()));
    }

    /** One analysis per request, shared by the score card and the badge. */
    protected static function analysis(Get $get, string $bodyField, ?string $excerptField): array
    {
        static $memo = [];
        $data = [
            'title' => $get('title'),
            'meta_title' => $get('meta_title'),
            'meta_description' => $get('meta_description'),
            'excerpt' => $excerptField ? $get($excerptField) : null,
            'slug' => $get('slug'),
            'content' => $get($bodyField),
            'focus_keyword' => $get('focus_keyword'),
            'thumbnail' => $get('thumbnail'),
            'image_alt' => $get('image_alt'),
        ];
        $key = md5(serialize($data));

        return $memo[$key] ??= (new SeoAnalyzer($data))->analyze();
    }

    protected static function preview(Get $get, string $route, string $bodyField, ?string $excerptField): HtmlString
    {
        $siteName = (string) setting('site_name');
        $title = trim((string) ($get('meta_title') ?: $get('title'))) ?: 'Judul halaman';
        $fullTitle = $title.' | '.$siteName;
        $slug = (string) $get('slug') ?: 'alamat-halaman';
        $description = (string) ($get('meta_description')
            ?: ($excerptField ? $get($excerptField) : null)
            ?: Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags(SeoAnalyzer::toHtml($get($bodyField))))), self::DESCRIPTION_LIMIT));

        return new HtmlString(view('filament.seo-preview', [
            'url' => route($route, $slug),
            'title' => Str::limit($fullTitle, 65),
            'titleTooLong' => mb_strlen($fullTitle) > 65,
            'description' => Str::limit($description ?: 'Belum ada deskripsi.', self::DESCRIPTION_LIMIT),
            'noindex' => (bool) $get('noindex'),
        ])->render());
    }
}
