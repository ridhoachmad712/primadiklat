<?php

namespace App\Support;

use App\Models\Post;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Illuminate\Support\Str;

/**
 * Writing checklist for articles, scored 0-100. A guide in the spirit of
 * Yoast / Rank Math, tuned for Indonesian text. It is not a Google signal.
 *
 * Each check is "good" (full weight), "ok" (half) or "bad" (zero).
 */
class SeoAnalyzer
{
    public const TITLE_MIN = 30;

    public const TITLE_MAX = 60;

    public const DESCRIPTION_MIN = 70;

    public const DESCRIPTION_MAX = 160;

    public const MIN_WORDS = 300;

    private array $checks = [];

    private string $html;

    private string $text;

    private array $words;

    /** First keyword: gets the full set of checks. */
    private ?string $keyword;

    /** @var array<int, string> Further comma-separated keywords: only need to appear in the text. */
    private array $secondary;

    public const MAX_SECONDARY = 4;

    public function __construct(private array $data)
    {
        $this->html = static::toHtml($data['content'] ?? '');
        $this->text = static::plain($this->html);
        $this->words = static::words($this->text);
        $keywords = static::keywords($data['focus_keyword'] ?? null);
        $this->keyword = $keywords[0] ?? null;
        $this->secondary = array_slice($keywords, 1, self::MAX_SECONDARY);
    }

    /** "Laporan Keuangan Daerah, bimtek; Pemerintah" -> ['laporan keuangan daerah', 'bimtek', 'pemerintah'] */
    public static function keywords(?string $value): array
    {
        $parts = preg_split('/[,;\n]+/u', Str::lower((string) $value)) ?: [];

        return array_values(array_unique(array_filter(array_map(
            fn ($k) => trim(preg_replace('/\s+/u', ' ', $k)),
            $parts,
        ), fn ($k) => $k !== '')));
    }

    public static function forPost(Post $post): array
    {
        return (new static([
            'title' => $post->title,
            'meta_title' => $post->meta_title,
            'meta_description' => $post->meta_description,
            'excerpt' => $post->excerpt,
            'slug' => $post->slug,
            'content' => $post->content,
            'focus_keyword' => $post->focus_keyword,
            'thumbnail' => $post->thumbnail,
            'image_alt' => $post->image_alt,
        ]))->analyze();
    }

    /** @return array{score:int, level:string, checks:array<int, array{group:string, status:string, label:string, hint:?string}>} */
    public function analyze(): array
    {
        $this->checks = [];
        $this->keywordChecks();
        $this->structureChecks();
        $this->readabilityChecks();

        $max = array_sum(array_column($this->checks, 'weight'));
        $got = array_sum(array_map(fn ($c) => match ($c['status']) {
            'good' => $c['weight'],
            'ok' => $c['weight'] / 2,
            default => 0,
        }, $this->checks));
        $score = $max ? (int) round($got / $max * 100) : 0;

        return [
            'score' => $score,
            'level' => static::level($score),
            'checks' => array_map(fn ($c) => collect($c)->except('weight')->all(), $this->checks),
        ];
    }

    public static function level(int $score): string
    {
        return $score >= 80 ? 'good' : ($score >= 50 ? 'ok' : 'bad');
    }

    // ---------------------------------------------------------------- checks

    private function keywordChecks(): void
    {
        if (! $this->keyword) {
            $this->add('Kata kunci', 15, 'bad', 'Kata kunci utama belum diisi', 'Tentukan frasa yang paling mungkin diketik orang di Google, mis. "laporan keuangan daerah". Kata kunci pendukung bisa ditambahkan setelah koma.');

            return;
        }
        $g = 'Kata kunci utama: '.$this->keyword;

        $title = Str::lower($this->effectiveTitle());
        $pos = mb_strpos($title, $this->keyword);
        $this->add($g, 10, $pos !== false ? 'good' : 'bad',
            $pos !== false ? 'Kata kunci ada di judul SEO' : 'Kata kunci belum ada di judul SEO',
            $pos !== false ? null : 'Masukkan frasa kata kunci utuh ke Judul SEO (atau judul artikel).');
        if ($pos !== false) {
            $early = $pos <= max(0, mb_strlen($title) / 2 - mb_strlen($this->keyword) / 2);
            $this->add($g, 4, $early ? 'good' : 'ok',
                $early ? 'Kata kunci berada di awal judul' : 'Kata kunci berada di akhir judul',
                $early ? null : 'Pindahkan kata kunci ke bagian awal judul agar lebih menonjol di hasil pencarian.');
        }

        $desc = Str::lower($this->effectiveDescription());
        $inDesc = str_contains($desc, $this->keyword);
        $this->add($g, 8, $inDesc ? 'good' : 'bad',
            $inDesc ? 'Kata kunci ada di deskripsi' : 'Kata kunci belum ada di deskripsi',
            $inDesc ? null : 'Sebutkan kata kunci di Deskripsi SEO. Google menebalkan kata yang dicari pengguna.');

        $slugKw = Str::slug($this->keyword);
        $inSlug = $slugKw !== '' && str_contains((string) ($this->data['slug'] ?? ''), $slugKw);
        $this->add($g, 6, $inSlug ? 'good' : 'bad',
            $inSlug ? 'Kata kunci ada di URL' : 'Kata kunci belum ada di URL',
            $inSlug ? null : 'Ubah slug agar memuat "'.$slugKw.'". Bila artikel sudah terbit, URL lama otomatis dialihkan.');

        $first = Str::lower($this->firstParagraph());
        $inFirst = $first !== '' && str_contains($first, $this->keyword);
        $this->add($g, 8, $inFirst ? 'good' : 'bad',
            $inFirst ? 'Kata kunci muncul di paragraf pertama' : 'Kata kunci belum muncul di paragraf pertama',
            $inFirst ? null : 'Sebutkan kata kunci sejak paragraf pembuka supaya pembaca dan Google langsung tahu topiknya.');

        $headings = Str::lower(implode(' ', $this->headings()));
        $inHeading = str_contains($headings, $this->keyword);
        $this->add($g, 6, $inHeading ? 'good' : 'bad',
            $inHeading ? 'Kata kunci ada di subjudul' : 'Kata kunci belum ada di subjudul',
            $inHeading ? null : 'Gunakan kata kunci (atau variasinya) di salah satu subjudul H2/H3.');

        $count = $this->occurrences();
        $total = count($this->words);
        $density = $total ? $count * count(static::words($this->keyword)) / $total * 100 : 0;
        $densityText = number_format($density, 1, ',', '.').'% ('.$count.'×)';
        [$status, $label, $hint] = match (true) {
            $count === 0 => ['bad', 'Kata kunci tidak muncul di isi', 'Sebutkan kata kunci beberapa kali secara wajar di isi artikel.'],
            $total < 100 => ['ok', 'Kepadatan kata kunci belum bisa dinilai (isi masih pendek)', 'Penilaian kepadatan berjalan setelah isi mencapai 100 kata.'],
            $density < 0.5 => ['ok', 'Kata kunci jarang muncul: '.$densityText, 'Tambahkan kata kunci 1–2 kali lagi di tempat yang alami.'],
            $density > 4 => ['bad', 'Kata kunci berlebihan: '.$densityText, 'Kurangi pengulangan. Terlalu sering mengulang kata kunci dianggap spam.'],
            $density > 2.5 => ['ok', 'Kata kunci agak sering: '.$densityText, 'Ganti sebagian pengulangan dengan sinonim atau kata ganti.'],
            default => ['good', 'Kepadatan kata kunci wajar: '.$densityText, null],
        };
        $this->add($g, 8, $status, $label, $hint);

        if ($this->secondary) {
            $haystack = Str::lower($this->effectiveTitle()).' '.Str::lower($this->text);
            $missing = array_values(array_filter($this->secondary, fn ($k) => $this->occurrencesOf($k, $haystack) === 0));
            $found = count($this->secondary) - count($missing);
            $this->add('Kata kunci pendukung', 6, ! $missing ? 'good' : ($found > 0 ? 'ok' : 'bad'),
                'Kata kunci pendukung muncul di artikel: '.$found.' dari '.count($this->secondary),
                $missing ? 'Belum muncul: "'.implode('", "', $missing).'". Periksa ejaannya atau sebutkan di isi artikel.' : null);
        }
    }

    private function structureChecks(): void
    {
        $g = 'Struktur & isi';

        $len = mb_strlen($this->effectiveTitle());
        $this->add($g, 8, $this->range($len, self::TITLE_MIN, self::TITLE_MAX, 20, 70),
            'Panjang judul SEO: '.$len.' karakter',
            $len >= self::TITLE_MIN && $len <= self::TITLE_MAX ? null : 'Ideal '.self::TITLE_MIN.'–'.self::TITLE_MAX.' karakter agar tidak terpotong dan tetap informatif.');

        $ownDesc = trim((string) ($this->data['meta_description'] ?? '')) !== '';
        $dlen = mb_strlen($this->effectiveDescription());
        $status = $this->range($dlen, self::DESCRIPTION_MIN, self::DESCRIPTION_MAX, 50, self::DESCRIPTION_MAX);
        if (! $ownDesc && $status === 'good') {
            $status = 'ok';
        }
        $this->add($g, 8, $status,
            $ownDesc ? 'Panjang deskripsi: '.$dlen.' karakter' : 'Deskripsi SEO belum diisi (memakai ringkasan/awal isi)',
            $status === 'good' ? null : 'Tulis Deskripsi SEO sendiri, '.self::DESCRIPTION_MIN.'–'.self::DESCRIPTION_MAX.' karakter, yang menjelaskan isi dan mengajak orang mengklik.');

        $n = count($this->words);
        $this->add($g, 10, $n >= self::MIN_WORDS ? 'good' : ($n >= 150 ? 'ok' : 'bad'),
            'Panjang isi: '.number_format($n, 0, ',', '.').' kata',
            $n >= self::MIN_WORDS ? null : 'Usahakan minimal '.self::MIN_WORDS.' kata agar topik dibahas cukup lengkap.');

        $h = count($this->headings());
        $needed = max(1, intdiv($n, 300));
        [$status, $label] = match (true) {
            $n < 300 && $h === 0 => ['good', 'Artikel pendek, subjudul belum diperlukan'],
            $h >= $needed => ['good', 'Subjudul cukup: '.$h],
            $h > 0 => ['ok', 'Subjudul masih sedikit: '.$h],
            default => ['bad', 'Belum ada subjudul'],
        };
        $this->add($g, 6, $status, $label, $status === 'good' ? null : 'Pecah isi dengan subjudul H2 kira-kira setiap 300 kata.');

        $hasImage = filled($this->data['thumbnail'] ?? null);
        $hasAlt = trim((string) ($this->data['image_alt'] ?? '')) !== '';
        $this->add($g, 6, $hasImage && $hasAlt ? 'good' : ($hasImage ? 'ok' : 'bad'),
            match (true) { $hasImage && $hasAlt => 'Gambar dan teks alternatif ada', $hasImage => 'Gambar ada, teks alternatif belum diisi', default => 'Belum ada gambar' },
            $hasImage && $hasAlt ? null : ($hasImage ? 'Isi "Teks alternatif gambar" yang menggambarkan isi foto.' : 'Tambahkan gambar utama. Artikel bergambar lebih menarik saat dibagikan.'));

        $internal = $this->internalLinks();
        $this->add($g, 6, $internal > 0 ? 'good' : 'bad',
            $internal > 0 ? 'Tautan internal: '.$internal : 'Belum ada tautan ke halaman lain di situs ini',
            $internal > 0 ? null : 'Tautkan ke tema bimtek, jadwal, atau artikel terkait agar pembaca (dan Google) menemukan halaman lain.');
    }

    private function readabilityChecks(): void
    {
        $g = 'Keterbacaan';

        $long = array_filter($this->paragraphs(), fn ($p) => count(static::words($p)) > 150);
        $this->add($g, 3, $long ? 'ok' : 'good',
            $long ? count($long).' paragraf terlalu panjang (> 150 kata)' : 'Panjang paragraf nyaman dibaca',
            $long ? 'Pecah paragraf panjang menjadi 2–3 paragraf pendek, terutama untuk pembaca di HP.' : null);

        $sentences = array_values(array_filter(preg_split('/(?<=[.!?])\s+/u', $this->text), fn ($s) => count(static::words($s)) > 0));
        $longCount = count(array_filter($sentences, fn ($s) => count(static::words($s)) > 25));
        $ratio = $sentences ? $longCount / count($sentences) * 100 : 0;
        $this->add($g, 3, $ratio <= 25 ? 'good' : ($ratio <= 40 ? 'ok' : 'bad'),
            $sentences ? round($ratio).'% kalimat terlalu panjang (> 25 kata)' : 'Belum ada kalimat untuk dinilai',
            $ratio <= 25 ? null : 'Usahakan maksimal 25% kalimat yang lebih dari 25 kata. Pecah kalimat panjang.');
    }

    // --------------------------------------------------------------- helpers

    private function add(string $group, int $weight, string $status, string $label, ?string $hint = null): void
    {
        $this->checks[] = compact('group', 'weight', 'status', 'label', 'hint');
    }

    private function range(int $value, int $min, int $max, int $softMin, int $softMax): string
    {
        return match (true) {
            $value >= $min && $value <= $max => 'good',
            $value >= $softMin && $value <= $softMax => 'ok',
            default => 'bad',
        };
    }

    private function effectiveTitle(): string
    {
        return trim((string) (($this->data['meta_title'] ?? null) ?: ($this->data['title'] ?? '')));
    }

    private function effectiveDescription(): string
    {
        return trim((string) (($this->data['meta_description'] ?? null)
            ?: ($this->data['excerpt'] ?? null)
            ?: Str::limit($this->text, self::DESCRIPTION_MAX, '')));
    }

    private function paragraphs(): array
    {
        preg_match_all('#<p[^>]*>(.*?)</p>#is', $this->html, $m);

        return array_values(array_filter(array_map(fn ($p) => static::plain($p), $m[1]), fn ($p) => $p !== ''));
    }

    private function firstParagraph(): string
    {
        return $this->paragraphs()[0] ?? Str::limit($this->text, 300, '');
    }

    private function headings(): array
    {
        preg_match_all('#<h[2-4][^>]*>(.*?)</h[2-4]>#is', $this->html, $m);

        return array_map(fn ($h) => static::plain($h), $m[1]);
    }

    private function occurrences(): int
    {
        return $this->occurrencesOf($this->keyword, Str::lower($this->text));
    }

    /** Whole-phrase matches (no partial words) in an already lower-cased text. */
    private function occurrencesOf(string $keyword, string $haystack): int
    {
        $pattern = '/(?<![\p{L}\p{N}])'.preg_quote($keyword, '/').'(?![\p{L}\p{N}])/u';

        return preg_match_all($pattern, $haystack);
    }

    private function internalLinks(): int
    {
        preg_match_all('#<a\s[^>]*href=["\']([^"\']+)["\']#i', $this->html, $m);
        $host = parse_url(config('app.url'), PHP_URL_HOST);

        return count(array_filter($m[1], function ($href) use ($host) {
            if (str_starts_with($href, '#') || str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:')) {
                return false;
            }
            $linkHost = parse_url($href, PHP_URL_HOST);

            return $linkHost === null || $linkHost === $host || $linkHost === request()?->getHost();
        }));
    }

    public static function toHtml(mixed $content): string
    {
        if (is_array($content)) {
            try {
                return RichContentRenderer::make($content)->toHtml();
            } catch (\Throwable) {
                return '';
            }
        }

        return (string) $content;
    }

    private static function plain(string $html): string
    {
        $html = preg_replace('#<(br|/p|/h[1-6]|/li|/div)[^>]*>#i', "$0 ", $html);

        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    private static function words(string $text): array
    {
        return preg_split('/[^\p{L}\p{N}\-]+/u', Str::lower($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }
}
