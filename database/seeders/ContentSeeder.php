<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Faq;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Real, production-safe base content: categories, FAQ (matching the open
 * 2027 schedule flow) and the static pages linked from the footer.
 * Idempotent: safe to run again.
 */
class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Pengelolaan Keuangan Daerah' => 'Penatausahaan, pelaporan, dan pertanggungjawaban keuangan daerah.',
            'Pengadaan Barang & Jasa' => 'Pengadaan pemerintah, e-katalog, dan sertifikasi PBJ.',
            'Kearsipan' => 'Pengelolaan arsip dinamis, statis, dan digitalisasi dokumen.',
            'Pemerintahan Desa' => 'Pengelolaan dana desa, BUMDes, dan administrasi desa.',
            'Kepegawaian & SDM' => 'Manajemen ASN, SKP, dan pengembangan kompetensi.',
            'Perencanaan & Penganggaran' => 'SIPD, RKPD, Renstra, dan penyusunan anggaran.',
            'Kesehatan' => 'Manajemen puskesmas, BLUD, dan akreditasi fasyankes.',
            'Perguruan Tinggi' => 'Akreditasi, tata kelola, dan penjaminan mutu PT.',
        ];
        foreach ($categories as $name => $description) {
            Category::firstOrCreate(['name' => $name], ['description' => $description]);
        }

        $faqs = [
            ['Bagaimana cara mendaftar bimtek?', 'Buka menu Daftar, pilih tema, tanggal, dan kota pelaksanaan, lalu isi data peserta. Satu formulir bisa untuk beberapa peserta dari instansi yang sama. Kode pendaftaran langsung terbit dan konfirmasi dikirim ke email.'],
            ['Apakah tema, tanggal, dan kota bisa dipilih bebas?', 'Bisa. Setiap tema dapat diikuti pada seluruh tanggal dan kota yang tercantum di halaman Jadwal. Bila tema yang dibutuhkan belum ada, pilih "Tema lain" dan tuliskan usulan Anda.'],
            ['Berapa biaya kontribusinya?', 'Informasi biaya kontribusi disampaikan admin melalui WhatsApp setelah pendaftaran diterima. Setelah itu tagihan resmi dikirim ke email.'],
            ['Bagaimana cara membayar?', 'Lakukan transfer sesuai tagihan, lalu unggah bukti transfer di halaman status pendaftaran. Kuitansi resmi dapat diunduh setelah pembayaran diverifikasi.'],
            ['Apakah tersedia surat undangan resmi untuk SPPD?', 'Ya. Surat undangan PDF dapat diunduh dari halaman status pendaftaran setelah kelas Anda ditetapkan.'],
            ['Kapan pendaftaran ditutup?', 'Pendaftaran setiap jadwal ditutup beberapa hari sebelum kegiatan. Batas waktunya tercantum di formulir pendaftaran.'],
            ['Apakah peserta mendapatkan sertifikat?', 'Ya. Peserta yang mengikuti kegiatan hingga selesai memperoleh sertifikat ber-QR yang dapat dicek dan diunduh melalui menu Cek Sertifikat.'],
            ['Apakah bisa mengadakan pelatihan di instansi kami (in-house)?', 'Bisa. Silakan isi formulir In-House Training, tim kami akan menghubungi Anda untuk menyesuaikan materi, jadwal, dan biaya.'],
        ];
        foreach ($faqs as $i => [$question, $answer]) {
            Faq::updateOrCreate(['question' => $question], ['answer' => $answer, 'sort' => $i]);
        }

        $pages = [
            'tentang-kami' => ['Tentang Kami', '<p><strong>Prima Diklat</strong> adalah lembaga penyelenggara bimbingan teknis (bimtek), pendidikan dan pelatihan (diklat), serta workshop bagi aparatur pemerintah pusat dan daerah, BUMN/BUMD, perguruan tinggi, dan sektor swasta.</p><h2>Visi</h2><p>Menjadi mitra terpercaya dalam pengembangan kompetensi sumber daya manusia aparatur yang profesional dan berintegritas.</p><h2>Misi</h2><ul><li>Menyelenggarakan pelatihan berkualitas sesuai kebutuhan dan regulasi terkini.</li><li>Menghadirkan narasumber praktisi dan akademisi berpengalaman.</li><li>Memberikan layanan yang ramah, cepat, dan transparan.</li></ul>'],
            'syarat-dan-ketentuan' => ['Syarat dan Ketentuan', '<p>Dengan mendaftar kegiatan di Prima Diklat, peserta dianggap telah menyetujui ketentuan berikut.</p><ol><li>Pendaftaran dianggap sah setelah peserta menerima kode pendaftaran.</li><li>Biaya kontribusi disampaikan admin dan ditetapkan dalam tagihan resmi.</li><li>Pembatalan paling lambat 3 hari sebelum kegiatan.</li><li>Penyelenggara berhak mengubah jadwal atau tempat dengan pemberitahuan sebelumnya.</li></ol>'],
            'kebijakan-privasi' => ['Kebijakan Privasi', '<p>Data pribadi yang Anda isi hanya digunakan untuk keperluan administrasi kegiatan dan tidak dibagikan kepada pihak ketiga tanpa persetujuan Anda.</p>'],
        ];
        foreach ($pages as $slug => [$title, $content]) {
            Page::firstOrCreate(['slug' => $slug], ['title' => $title, 'content' => $content]);
        }
    }
}
