<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Partner;
use App\Models\Post;
use App\Models\Registration;
use App\Models\Testimonial;
use App\Models\Training;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(['email' => 'admin@primadiklat.test'], [
            'name' => 'Administrator',
            'password' => 'password',
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $categories = collect([
            'Pengelolaan Keuangan Daerah' => 'Penatausahaan, pelaporan, dan pertanggungjawaban keuangan daerah.',
            'Pengadaan Barang & Jasa' => 'Pengadaan pemerintah, e-katalog, dan sertifikasi PBJ.',
            'Kearsipan' => 'Pengelolaan arsip dinamis, statis, dan digitalisasi dokumen.',
            'Pemerintahan Desa' => 'Pengelolaan dana desa, BUMDes, dan administrasi desa.',
            'Kepegawaian & SDM' => 'Manajemen ASN, SKP, dan pengembangan kompetensi.',
            'Perencanaan & Penganggaran' => 'SIPD, RKPD, Renstra, dan penyusunan anggaran.',
            'Kesehatan' => 'Manajemen puskesmas, BLUD, dan akreditasi fasyankes.',
            'Perguruan Tinggi' => 'Akreditasi, tata kelola, dan penjaminan mutu PT.',
        ])->map(fn ($desc, $name) => Category::firstOrCreate(['name' => $name], ['description' => $desc]))->values();

        $priceSets = [
            [['name' => 'Tanpa Penginapan', 'price' => 4000000, 'note' => 'Kelas 2 hari, tanpa hotel'],
             ['name' => 'Penginapan (Twin Sharing)', 'price' => 4800000, 'note' => '4 hari 3 malam, 1 kamar 2 orang'],
             ['name' => 'Penginapan (Single)', 'price' => 5700000, 'note' => '4 hari 3 malam, 1 kamar 1 orang']],
            [['name' => 'Paket Reguler', 'price' => 3500000, 'note' => 'Tanpa penginapan'],
             ['name' => 'Paket Hotel', 'price' => 4500000, 'note' => 'Termasuk hotel 3 malam']],
            [['name' => 'Peserta Online', 'price' => 1500000, 'note' => 'Via Zoom + e-sertifikat']],
        ];
        $facilities = ['Modul / bahan ajar', 'Training kit', 'Sertifikat', 'Makan siang', 'Coffee break 2x'];
        $cities = [['Jakarta', 'Hotel Grand Sahid Jaya', 'Jl. Jend. Sudirman Kav. 86, Jakarta Pusat'],
                   ['Bandung', 'Hotel Horison Bandung', 'Jl. Pelajar Pejuang 45 No. 121, Bandung'],
                   ['Yogyakarta', 'Hotel Tentrem Yogyakarta', 'Jl. P. Mangkubumi No. 72A, Yogyakarta'],
                   ['Makassar', 'Hotel Claro Makassar', 'Jl. A.P. Pettarani No. 3, Makassar'],
                   ['Surabaya', 'Hotel Bumi Surabaya', 'Jl. Basuki Rahmat No. 106-128, Surabaya'],
                   ['Bali', 'Hotel Aston Kuta', 'Jl. Wana Segara No. 2-4, Kuta, Bali']];

        $titles = [
            ['Bimtek Penyusunan Laporan Keuangan Pemerintah Daerah Berbasis SIPD', 0, 'bimtek'],
            ['Diklat Sertifikasi Pengadaan Barang/Jasa Pemerintah Tingkat Dasar', 1, 'sertifikasi'],
            ['Bimtek Digitalisasi Arsip dan Pengelolaan Arsip Elektronik', 2, 'bimtek'],
            ['Bimtek Pengelolaan Dana Desa dan Pelaporan Siskeudes', 3, 'bimtek'],
            ['Diklat Penyusunan SKP dan Manajemen Kinerja ASN', 4, 'diklat'],
            ['Bimtek Penyusunan RKPD dan Renja Perangkat Daerah', 5, 'bimtek'],
            ['Pelatihan Manajemen BLUD Puskesmas', 6, 'pelatihan'],
            ['Workshop Persiapan Akreditasi Program Studi (IAPS 5.0)', 7, 'workshop'],
            ['Bimtek E-Katalog Lokal dan Toko Daring Pemerintah', 1, 'bimtek'],
            ['Bimtek Pengelolaan BUMDes yang Profesional dan Akuntabel', 3, 'bimtek'],
            ['Bimtek Penatausahaan Barang Milik Daerah (BMD)', 0, 'bimtek'],
            ['Diklat Kepemimpinan dan Manajemen Perubahan bagi Pejabat Pengawas', 4, 'diklat'],
        ];

        $trainings = collect();
        foreach ($titles as $i => [$title, $catIdx, $type]) {
            $start = Carbon::today()->addDays(-30 + $i * 9)->next(Carbon::TUESDAY);
            $online = $i % 5 === 4;
            [$city, $venue, $address] = $cities[$i % count($cities)];
            $trainings->push(Training::updateOrCreate(['title' => $title], [
                'category_id' => $categories[$catIdx]->id,
                'type' => $type,
                'excerpt' => "Program {$type} intensif untuk meningkatkan pemahaman dan keterampilan teknis peserta sesuai regulasi terbaru.",
                'description' => '<p>Kegiatan ini dirancang untuk membekali aparatur dengan pemahaman regulasi terbaru, praktik terbaik, dan keterampilan teknis yang langsung dapat diterapkan di instansi masing-masing.</p><p>Metode pembelajaran meliputi paparan narasumber, diskusi kasus, simulasi, dan tanya jawab interaktif bersama praktisi berpengalaman.</p><h3>Dasar Hukum</h3><ul><li>Undang-Undang Nomor 23 Tahun 2014 tentang Pemerintahan Daerah</li><li>Peraturan Pemerintah Nomor 12 Tahun 2019 tentang Pengelolaan Keuangan Daerah</li><li>Peraturan perundang-undangan terkait lainnya</li></ul>',
                'materials' => '<ol><li>Kebijakan dan regulasi terbaru</li><li>Konsep dasar dan ruang lingkup</li><li>Teknis pelaksanaan dan tahapan kerja</li><li>Studi kasus dan permasalahan di lapangan</li><li>Praktik / simulasi aplikasi</li><li>Diskusi dan konsultasi</li></ol>',
                'target_participants' => 'Kepala OPD, Sekretaris, Kepala Bidang/Bagian, Kasubbag, Bendahara, dan staf pelaksana terkait di lingkungan pemerintah provinsi, kabupaten/kota, dan desa.',
                'start_date' => $start,
                'end_date' => $start->copy()->addDays($online ? 1 : 3),
                'registration_deadline' => $start->copy()->subDays(3),
                'method' => $online ? 'online' : 'offline',
                'city' => $online ? null : $city,
                'venue' => $online ? null : $venue,
                'venue_address' => $online ? null : $address,
                'quota' => [40, 50, 60, null][$i % 4],
                'prices' => $online ? $priceSets[2] : $priceSets[$i % 2],
                'facilities' => $online ? ['E-modul', 'E-sertifikat', 'Rekaman sesi'] : $facilities,
                'status' => 'published',
                'is_featured' => $i % 3 === 1,
                'registration_open' => true,
                'views' => random_int(40, 900),
            ]));
        }

        if (Registration::count() === 0) {
            $names = ['Andi Saputra', 'Siti Rahmawati', 'Budi Santoso', 'Dewi Lestari', 'Ahmad Fauzi', 'Rina Marlina', 'Hendra Wijaya', 'Nurul Hidayah', 'Muh. Ilham', 'Fitri Handayani', 'Yusuf Maulana', 'Sri Wahyuni', 'Rahmat Hidayat', 'Ratna Sari', 'Agus Salim', 'Wulan Dari'];
            $insts = ['BPKAD Kab. Gowa', 'Dinas Pendidikan Prov. Sulsel', 'Bappeda Kota Bandung', 'Sekretariat DPRD Kab. Bogor', 'Pemerintah Desa Sukamaju', 'RSUD Kota Makassar', 'Inspektorat Kab. Maros', 'Universitas Negeri Contoh'];
            foreach ($trainings as $t) {
                $n = $t->is_finished ? random_int(18, 30) : random_int(3, 14);
                for ($k = 0; $k < $n; $k++) {
                    $pkg = collect($t->prices)->random();
                    $name = $names[array_rand($names)];
                    Registration::create([
                        'training_id' => $t->id,
                        'name' => $name,
                        'nip' => '19'.random_int(70, 99).str_pad((string) random_int(1, 999999999999), 14, '0', STR_PAD_LEFT),
                        'position' => ['Kepala Bidang', 'Kasubbag Keuangan', 'Bendahara', 'Staf Pelaksana', 'Analis Kebijakan'][array_rand([0, 1, 2, 3, 4])],
                        'institution' => $insts[array_rand($insts)],
                        'city' => ['Makassar', 'Gowa', 'Bandung', 'Bogor', 'Maros'][array_rand([0, 1, 2, 3, 4])],
                        'email' => str($name)->slug('.').random_int(1, 99).'@example.com',
                        'phone' => '08'.random_int(1100000000, 1399999999),
                        'package' => $pkg['name'],
                        'price' => $pkg['price'],
                        'status' => $t->is_finished ? 'attended' : ['pending', 'confirmed', 'confirmed'][array_rand([0, 1, 2])],
                        'payment_status' => $t->is_finished ? 'paid' : ['unpaid', 'paid'][array_rand([0, 1])],
                        'certificate_number' => $t->is_finished ? sprintf('PD/%s/%04d', $t->end_date->format('Y'), Registration::count() + 1) : null,
                        'certificate_issued_at' => $t->is_finished ? $t->end_date : null,
                        'created_at' => $t->start_date->copy()->subDays(random_int(3, 120))->min(now())->setTime(random_int(8, 20), random_int(0, 59)),
                    ]);
                }
            }
        }

        $posts = [
            ['Pemerintah Terbitkan Aturan Baru Pengelolaan Keuangan Daerah 2027', 'regulasi'],
            ['Tips Lolos Ujian Sertifikasi Pengadaan Barang/Jasa Tingkat Dasar', 'artikel'],
            ['Prima Diklat Gelar Bimtek Digitalisasi Arsip di Makassar', 'berita'],
            ['Jadwal Bimtek dan Diklat Semester I Tahun 2027 Telah Dibuka', 'pengumuman'],
            ['Mengenal SIPD RI dan Perannya dalam Perencanaan Daerah', 'artikel'],
            ['5 Kesalahan Umum dalam Penyusunan SKP ASN', 'artikel'],
        ];
        foreach ($posts as $i => [$title, $cat]) {
            Post::updateOrCreate(['title' => $title], [
                'user_id' => $admin->id,
                'category' => $cat,
                'excerpt' => 'Ringkasan informasi terbaru seputar peningkatan kompetensi aparatur dan regulasi yang perlu Anda ketahui.',
                'content' => '<p>Peningkatan kompetensi aparatur menjadi kunci terwujudnya tata kelola pemerintahan yang baik. Artikel ini membahas poin-poin penting yang perlu diketahui oleh pengelola di instansi pemerintah.</p><h2>Poin Penting</h2><ul><li>Pemahaman regulasi yang berlaku</li><li>Penerapan praktik terbaik</li><li>Pemanfaatan teknologi informasi</li></ul><p>Untuk pendalaman materi, ikuti bimtek dan diklat yang diselenggarakan Prima Diklat bersama narasumber berpengalaman.</p>',
                'is_published' => true,
                'published_at' => now()->subDays($i * 5 + 1),
                'views' => random_int(30, 600),
            ]);
        }

        $testimonials = [
            ['Drs. H. Syamsul Bahri', 'Kepala Bidang Anggaran', 'BPKAD Kab. Gowa', 'Materi sangat aplikatif dan narasumber menguasai regulasi terbaru. Langsung bisa kami terapkan di kantor.'],
            ['Rina Kartika, S.E.', 'Bendahara Pengeluaran', 'Dinas Kesehatan Kota Bandung', 'Pelayanan panitia ramah dan responsif sejak pendaftaran hingga sertifikat. Sangat direkomendasikan.'],
            ['Ir. Bambang Setiawan, M.T.', 'Sekretaris Dinas', 'Dinas PUPR Prov. Jawa Tengah', 'Penyelenggaraan rapi, tempat nyaman, dan diskusinya hidup. Kami akan kirim peserta lagi tahun depan.'],
            ['Muh. Arief, S.Kom.', 'Arsiparis Ahli Muda', 'Sekretariat Daerah Kab. Maros', 'Praktik digitalisasi arsipnya detail. Kami pulang dengan SOP yang siap dipakai.'],
        ];
        foreach ($testimonials as $i => [$name, $pos, $inst, $content]) {
            Testimonial::updateOrCreate(['name' => $name], ['position' => $pos, 'institution' => $inst, 'content' => $content, 'rating' => 5, 'sort' => $i]);
        }

        foreach (['Pemprov Sulawesi Selatan', 'Pemkab Gowa', 'Pemkot Bandung', 'Kementerian Dalam Negeri', 'RSUD Daya', 'Universitas Negeri Makassar', 'Pemkab Bogor', 'BPKP'] as $i => $p) {
            Partner::firstOrCreate(['name' => $p], ['sort' => $i]);
        }

        $faqs = [
            ['Bagaimana cara mendaftar kegiatan?', 'Pilih kegiatan pada menu Jadwal, klik tombol "Daftar Sekarang", lalu isi formulir. Anda akan menerima kode pendaftaran dan konfirmasi melalui email dan WhatsApp.'],
            ['Apakah tersedia surat undangan resmi untuk SPPD?', 'Ya. Setiap halaman kegiatan memiliki tombol "Unduh Undangan" yang menghasilkan surat undangan PDF resmi untuk keperluan administrasi perjalanan dinas.'],
            ['Kapan pembayaran dilakukan?', 'Pembayaran dapat dilakukan melalui transfer sebelum kegiatan atau saat registrasi ulang di lokasi. Informasi rekening tercantum di email konfirmasi.'],
            ['Apakah peserta mendapatkan sertifikat?', 'Ya. Peserta yang mengikuti kegiatan hingga selesai memperoleh sertifikat. Sertifikat dapat dicek dan diunduh melalui menu Cek Sertifikat.'],
            ['Apakah bisa mengadakan pelatihan di instansi kami (in-house)?', 'Bisa. Silakan isi formulir In-House Training, tim kami akan menghubungi Anda untuk menyesuaikan materi, jadwal, dan biaya.'],
        ];
        foreach ($faqs as $i => [$q, $a]) {
            Faq::updateOrCreate(['question' => $q], ['answer' => $a, 'sort' => $i]);
        }

        $pages = [
            'tentang-kami' => ['Tentang Kami', '<p><strong>Prima Diklat</strong> adalah lembaga penyelenggara bimbingan teknis (bimtek), pendidikan dan pelatihan (diklat), serta workshop bagi aparatur pemerintah pusat dan daerah, BUMN/BUMD, perguruan tinggi, dan sektor swasta.</p><h2>Visi</h2><p>Menjadi mitra terpercaya dalam pengembangan kompetensi sumber daya manusia aparatur yang profesional dan berintegritas.</p><h2>Misi</h2><ul><li>Menyelenggarakan pelatihan berkualitas sesuai kebutuhan dan regulasi terkini.</li><li>Menghadirkan narasumber praktisi dan akademisi berpengalaman.</li><li>Memberikan layanan yang ramah, cepat, dan transparan.</li></ul>'],
            'syarat-dan-ketentuan' => ['Syarat dan Ketentuan', '<p>Dengan mendaftar kegiatan di Prima Diklat, peserta dianggap telah menyetujui ketentuan berikut.</p><ol><li>Pendaftaran dianggap sah setelah peserta menerima kode pendaftaran.</li><li>Pembatalan paling lambat 3 hari sebelum kegiatan.</li><li>Penyelenggara berhak mengubah jadwal atau tempat dengan pemberitahuan sebelumnya.</li></ol>'],
            'kebijakan-privasi' => ['Kebijakan Privasi', '<p>Data pribadi yang Anda isi hanya digunakan untuk keperluan administrasi kegiatan dan tidak dibagikan kepada pihak ketiga tanpa persetujuan Anda.</p>'],
        ];
        foreach ($pages as $slug => [$title, $content]) {
            Page::updateOrCreate(['slug' => $slug], ['title' => $title, 'content' => $content]);
        }

        $this->call(Schedule2027Seeder::class);
    }
}
