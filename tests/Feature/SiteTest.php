<?php

namespace Tests\Feature;

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\Settings;
use App\Filament\Resources;
use App\Filament\Widgets;
use App\Models\Gallery;
use App\Models\Invoice;
use App\Models\ScheduleDate;
use App\Models\Topic;
use App\Models\Venue;
use App\Models\Post;
use App\Models\Registration;
use App\Models\Training;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        Gallery::create(['title' => 'Dokumentasi Uji', 'images' => [], 'event_date' => now()]);
    }

    public function test_public_pages_render(): void
    {
        $training = Training::first();
        $post = Post::first();

        foreach ([
            '/', '/jadwal', '/jadwal?status=selesai', '/jadwal?kategori=kearsipan&bulan='.now()->format('Y-m'),
            '/kalender', '/kalender?bulan=2027-01', '/berita', '/berita?kategori=artikel', '/galeri',
            '/galeri/dokumentasi-uji', '/sertifikat', '/sertifikat?kode=TIDAK-ADA', '/in-house-training',
            '/kontak', '/faq', '/cari?q=bimtek', '/cari?q=makassar', '/halaman/tentang-kami', '/sitemap.xml',
            '/kegiatan', '/tema', '/tema?kategori=kearsipan&q=arsip', '/tema/'.Topic::first()->slug,
            '/daftar', '/daftar?tema='.Topic::first()->slug.'&tanggal=2027-01-07&kota=Makassar', '/jadwal?tahun=2027',
            route('trainings.show', $training, false), route('registrations.create', $training, false),
            route('posts.show', $post, false),
        ] as $url) {
            $this->get($url)->assertOk();
        }

        $this->get(route('trainings.invitation', $training))->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function test_certificate_lookup_finds_attended_participant(): void
    {
        $r = Registration::whereNotNull('certificate_number')->first();

        $this->get('/sertifikat?kode='.urlencode($r->certificate_number))->assertOk()->assertSee('Sertifikat valid')->assertSee($r->name);
    }

    public function test_participant_can_register(): void
    {
        Mail::fake();
        $training = Training::upcoming()->get()->first(fn ($t) => $t->can_register);

        $response = $this->post(route('registrations.store', $training), [
            'institution' => 'BPKAD Uji',
            'participants' => [['name' => 'Peserta Uji', 'email' => 'uji@example.com', 'phone' => '081234567890']],
            'package' => $training->prices[0]['name'],
            'agree' => '1',
        ]);

        $registration = Registration::where('email', 'uji@example.com')->firstOrFail();
        $response->assertRedirect(route('registrations.show', $registration->code));
        $this->assertSame((int) $training->prices[0]['price'], (int) $registration->price);
        $this->assertSame($registration->price, $registration->invoice->amount);
        Mail::assertSent(\App\Mail\RegistrationReceived::class);
        $this->get(route('registrations.show', $registration->code))->assertOk()->assertSee($registration->code)->assertSee($registration->invoice->number);
    }

    public function test_group_registration_shares_one_invoice(): void
    {
        Mail::fake();
        $training = Training::upcoming()->get()->first(fn ($t) => $t->can_register && $t->remaining_quota >= 3);
        $price = (int) $training->prices[0]['price'];

        $this->post(route('registrations.store', $training), [
            'institution' => 'Pemkab Rombongan',
            'participants' => [
                ['name' => 'Ketua Rombongan', 'email' => 'ketua@example.com', 'phone' => '081234567890'],
                ['name' => 'Anggota Satu', 'nip' => '1990', 'position' => 'Bendahara'],
                ['name' => 'Anggota Dua', 'email' => 'dua@example.com'],
            ],
            'package' => $training->prices[0]['name'],
            'agree' => '1',
        ])->assertSessionHasNoErrors();

        $invoice = Invoice::where('institution', 'Pemkab Rombongan')->firstOrFail();
        $this->assertSame(3, $invoice->registrations()->count());
        $this->assertSame($price * 3, $invoice->amount);
        $this->assertSame('Ketua Rombongan', $invoice->contact_name);
        // Missing contact details fall back to the group contact.
        $this->assertSame('ketua@example.com', Registration::where('name', 'Anggota Satu')->value('email'));
        $this->assertSame('dua@example.com', Registration::where('name', 'Anggota Dua')->value('email'));
        Mail::assertSent(\App\Mail\RegistrationReceived::class, 1);
    }

    public function test_registration_rejects_unknown_package_missing_consent_and_contact(): void
    {
        $training = Training::upcoming()->get()->first(fn ($t) => $t->can_register);

        $this->post(route('registrations.store', $training), [
            'institution' => 'Y',
            'participants' => [['name' => 'X'], ['name' => '']],
            'package' => 'Paket Palsu',
        ])->assertSessionHasErrors(['package', 'agree', 'participants.0.email', 'participants.0.phone', 'participants.1.name']);
    }

    public function test_payment_proof_upload_verification_and_documents(): void
    {
        Mail::fake();
        Storage::fake('public');
        $registration = Registration::where('payment_status', 'unpaid')->where('status', '!=', 'cancelled')->firstOrFail();
        $invoice = $registration->invoice;
        $this->assertSame('unpaid', $invoice->status);

        $this->get(route('registrations.invoice', $registration->code))->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->get(route('registrations.receipt', $registration->code))->assertNotFound();

        $this->post(route('registrations.proof', $registration->code), ['proof' => UploadedFile::fake()->image('bukti.jpg')])
            ->assertRedirect(route('registrations.show', $registration->code));
        $invoice->refresh();
        $this->assertSame('waiting', $invoice->status);
        Storage::disk('public')->assertExists($invoice->payment_proof);
        Mail::assertSent(\App\Mail\PaymentProofUploaded::class);

        // Admin rejects, participant re-uploads, admin verifies.
        $this->actingAs(User::first());
        Livewire::test(Resources\Invoices\Pages\ManageInvoices::class)
            ->callTableAction('reject', $invoice, ['reason' => 'Nominal kurang'])
            ->assertHasNoTableActionErrors();
        $this->assertSame('unpaid', $invoice->fresh()->status);
        $this->get(route('registrations.show', $registration->code))->assertSee('Nominal kurang');

        $this->post(route('registrations.proof', $registration->code), ['proof' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf')]);
        Livewire::test(Resources\Invoices\Pages\ManageInvoices::class)
            ->callTableAction('verify', $invoice->fresh());

        $invoice->refresh();
        $this->assertSame('paid', $invoice->status);
        $this->assertNotNull($invoice->receipt_number);
        $this->assertSame('paid', $registration->fresh()->payment_status);
        Mail::assertSent(\App\Mail\PaymentVerified::class);
        $this->get(route('registrations.receipt', $registration->code))->assertOk()->assertHeader('content-type', 'application/pdf');

        // Paid invoices no longer accept proofs.
        $this->post(route('registrations.proof', $registration->code), ['proof' => UploadedFile::fake()->image('lagi.jpg')])->assertForbidden();
    }

    public function test_certificates_are_numbered_and_downloadable(): void
    {
        $registration = Registration::where('status', 'confirmed')->firstOrFail();
        $registration->invoice->markPaid(notify: false);
        $registration->update(['status' => 'attended']);

        $this->assertTrue($registration->fresh()->issueCertificate());
        $registration->refresh();
        $this->assertMatchesRegularExpression('#^\d{4}/PD-SRT/[IVX]+/\d{4}$#', $registration->certificate_number);
        $this->assertFalse($registration->issueCertificate(), 'Issuing twice must keep the first number.');

        $this->get('/sertifikat?kode='.urlencode($registration->certificate_number))->assertSee($registration->certificate_download_url);
        $this->get(route('certificates.download', $registration->code))->assertOk()->assertHeader('content-type', 'application/pdf');

        // Unpaid participants cannot download until payment is verified.
        $unpaid = Registration::where('payment_status', 'unpaid')->where('status', '!=', 'cancelled')->firstOrFail();
        $unpaid->update(['status' => 'attended']);
        $unpaid->issueCertificate();
        $this->get(route('certificates.download', $unpaid->code))->assertNotFound();
        $this->actingAs(User::first())->get(route('certificates.download', ['code' => $unpaid->code, 'preview' => 1]))->assertOk();
    }

    public function test_schedule_2027_is_seeded_from_pdf(): void
    {
        $this->assertSame(55, ScheduleDate::whereYear('start_date', 2027)->count());
        $this->assertSame(14, Venue::count());
        $this->assertSame(13, Venue::distinct()->count('city'));
        $slot = ScheduleDate::whereDate('start_date', '2027-01-07')->firstOrFail();
        $this->assertSame('2027-01-08', $slot->end_date->toDateString());

        $this->get('/jadwal')->assertOk()->assertSee('Jadwal Bimtek 2027')->assertSee('Swiss-Belinn Panakkukang Makassar')->assertSee('07 – 08 Januari 2027');
        $this->get('/')->assertOk()->assertSee('Januari – Desember 2027')->assertDontSee('Mulai Rp');
    }

    public function test_open_schedule_registration_without_price(): void
    {
        Mail::fake();
        $topic = Topic::first();
        $date = ScheduleDate::open()->first();
        $venue = Venue::where('city', 'Makassar')->firstOrFail();

        $payload = [
            'topic' => (string) $topic->id,
            'schedule_date_id' => $date->id,
            'venue_id' => $venue->id,
            'institution' => 'Dinas Terbuka',
            'participants' => [
                ['name' => 'Kontak Terbuka', 'email' => 'terbuka@example.com', 'phone' => '081234567890'],
                ['name' => 'Rekan Terbuka'],
            ],
            'agree' => '1',
        ];
        $this->post('/daftar', $payload)->assertSessionHasNoErrors();

        $invoice = Invoice::where('institution', 'Dinas Terbuka')->firstOrFail();
        $training = $invoice->training;
        $this->assertSame($topic->id, $training->topic_id);
        $this->assertSame($date->id, $training->schedule_date_id);
        $this->assertSame('Makassar', $training->city);
        $this->assertSame('published', $training->status);
        $this->assertSame(0, $invoice->amount);
        $this->assertFalse($invoice->can_upload_proof);

        $code = $invoice->public_code;
        $this->get(route('registrations.show', $code))->assertOk()->assertSee('Menunggu informasi biaya')->assertDontSee('Kirim Bukti Pembayaran');

        // A second institution choosing the same combination joins the same class.
        $this->post('/daftar', array_merge($payload, ['institution' => 'Dinas Kedua', 'participants' => [['name' => 'B', 'email' => 'b@example.com', 'phone' => '081234567891']]]));
        $this->assertSame($training->id, Invoice::where('institution', 'Dinas Kedua')->value('training_id'));

        // Admin sets the price after the WhatsApp follow-up → invoice + upload become available.
        $this->actingAs(User::first());
        Livewire::test(Resources\Invoices\Pages\ManageInvoices::class)
            ->callTableAction('set_price', $invoice, ['package' => 'Fullboard', 'price' => 4500000, 'notify' => true])
            ->assertHasNoTableActionErrors();
        $invoice->refresh();
        $this->assertSame(9000000, $invoice->amount);
        $this->assertTrue($invoice->can_upload_proof);
        Mail::assertSent(\App\Mail\InvoiceReady::class);
        $this->get(route('registrations.show', $code))->assertSee('Rp 9.000.000')->assertSee('Kirim Bukti Pembayaran');
    }

    public function test_open_schedule_custom_topic_and_closed_dates(): void
    {
        Mail::fake();
        $venue = Venue::first();
        $base = [
            'venue_id' => $venue->id,
            'institution' => 'Pemdes Usul',
            'participants' => [['name' => 'Pengusul', 'email' => 'usul@example.com', 'phone' => '081234567890']],
            'agree' => '1',
        ];

        $this->post('/daftar', $base + ['topic' => 'other', 'schedule_date_id' => ScheduleDate::open()->first()->id])
            ->assertSessionHasErrors('custom_topic');

        $this->post('/daftar', $base + ['topic' => 'other', 'custom_topic' => 'Pengelolaan BUMDes Modern', 'schedule_date_id' => ScheduleDate::open()->first()->id])
            ->assertSessionHasNoErrors();
        $training = Training::where('title', 'Pengelolaan BUMDes Modern')->firstOrFail();
        $this->assertNull($training->topic_id);
        $this->assertSame('draft', $training->status);

        // A slot inside the closing window is rejected.
        $soon = ScheduleDate::create(['start_date' => today()->addDay(), 'end_date' => today()->addDays(2)]);
        $this->post('/daftar', $base + ['topic' => (string) Topic::first()->id, 'schedule_date_id' => $soon->id])
            ->assertSessionHasErrors('schedule_date_id');
    }

    public function test_terbilang_helper(): void
    {
        $this->assertSame('satu juta lima ratus ribu', terbilang(1500000));
        $this->assertSame('dua belas juta tiga ratus empat puluh lima ribu enam ratus tujuh puluh delapan', terbilang(12345678));
        $this->assertSame('seribu seratus sebelas', terbilang(1111));
    }

    public function test_inhouse_and_contact_forms_store(): void
    {
        $this->post('/in-house-training', [
            'institution' => 'Pemkab Uji', 'contact_name' => 'Budi', 'email' => 'b@example.com', 'phone' => '081234567890', 'topic' => 'Keuangan',
        ])->assertSessionHas('success');
        $this->post('/kontak', ['name' => 'A', 'email' => 'a@example.com', 'subject' => 'Tanya', 'message' => 'Halo'])->assertSessionHas('success');

        $this->assertDatabaseCount('inhouse_requests', 1);
        $this->assertDatabaseCount('contact_messages', 1);
    }

    public function test_admin_pages_and_widgets_render(): void
    {
        $admin = User::where('role', User::ROLE_SUPER_ADMIN)->first();
        $this->actingAs($admin);

        $training = Training::first();
        $urls = [
            '/admin',
            Resources\Trainings\TrainingResource::getUrl('index'),
            Resources\Trainings\TrainingResource::getUrl('create'),
            Resources\Trainings\TrainingResource::getUrl('edit', ['record' => $training]),
            Resources\Registrations\RegistrationResource::getUrl('index'),
            Resources\Registrations\RegistrationResource::getUrl('edit', ['record' => Registration::first()]),
            Resources\Invoices\InvoiceResource::getUrl('index'),
            Resources\Topics\TopicResource::getUrl('index'),
            Resources\ScheduleDates\ScheduleDateResource::getUrl('index'),
            Resources\Venues\VenueResource::getUrl('index'),
            Resources\Posts\PostResource::getUrl('index'),
            Resources\Posts\PostResource::getUrl('create'),
            Resources\Posts\PostResource::getUrl('edit', ['record' => Post::first()]),
            Resources\Categories\CategoryResource::getUrl('index'),
            Resources\Galleries\GalleryResource::getUrl('index'),
            Resources\Testimonials\TestimonialResource::getUrl('index'),
            Resources\Partners\PartnerResource::getUrl('index'),
            Resources\Faqs\FaqResource::getUrl('index'),
            Resources\Pages\PageResource::getUrl('index'),
            Resources\InhouseRequests\InhouseRequestResource::getUrl('index'),
            Resources\ContactMessages\ContactMessageResource::getUrl('index'),
            Resources\Users\UserResource::getUrl('index'),
            Settings::getUrl(),
        ];
        foreach ($urls as $url) {
            $this->get($url)->assertOk();
        }

        foreach ([Widgets\StatsOverview::class, Widgets\RegistrationsChart::class, Widgets\UpcomingTrainings::class, Widgets\LatestRegistrations::class] as $widget) {
            Livewire::test($widget)->assertOk();
        }

        Livewire::test(Resources\Trainings\Pages\EditTraining::class, ['record' => $training->getRouteKey()])->assertOk();
        Livewire::test(Resources\Trainings\RelationManagers\RegistrationsRelationManager::class, [
            'ownerRecord' => $training, 'pageClass' => Resources\Trainings\Pages\EditTraining::class,
        ])->loadTable()->assertCanSeeTableRecords($training->registrations()->latest()->take(5)->get());
    }

    public function test_admin_list_tabs_filter_records(): void
    {
        $this->actingAs(User::first());

        $pending = Registration::where('status', 'pending')->latest()->take(5)->get();
        $attended = Registration::where('status', 'attended')->take(5)->get();

        Livewire::test(Resources\Registrations\Pages\ListRegistrations::class, ['activeTab' => 'pending'])
            ->loadTable()
            ->assertCanSeeTableRecords($pending)
            ->assertCanNotSeeTableRecords($attended);

        $past = Training::whereDate('end_date', '<', today())->get();
        Livewire::test(Resources\Trainings\Pages\ListTrainings::class, ['activeTab' => 'upcoming'])
            ->loadTable()
            ->assertCanSeeTableRecords(Training::whereDate('end_date', '>=', today())->orderByDesc('start_date')->take(5)->get())
            ->assertCanNotSeeTableRecords($past);
    }

    public function test_admin_can_export_registrations(): void
    {
        $this->actingAs(User::first());

        Livewire::test(Resources\Registrations\Pages\ListRegistrations::class)
            ->callAction('export')
            ->assertFileDownloaded();
    }

    public function test_settings_can_be_saved(): void
    {
        $this->actingAs(User::first());

        Livewire::test(Settings::class)
            ->fillForm(['site_name' => 'Prima Diklat Baru', 'whatsapp' => '6281111111111'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Prima Diklat Baru', setting('site_name'));
    }

    public function test_editor_cannot_manage_users_or_settings(): void
    {
        $editor = User::create(['name' => 'Ed', 'email' => 'ed@example.com', 'password' => 'password', 'role' => User::ROLE_EDITOR]);
        $this->actingAs($editor);

        $this->get(Resources\Trainings\TrainingResource::getUrl('index'))->assertOk();
        $this->get(Resources\Users\UserResource::getUrl('index'))->assertForbidden();
        $this->get(Settings::getUrl())->assertForbidden();
        $this->get(Resources\Registrations\RegistrationResource::getUrl('index'))->assertForbidden();
    }

    public function test_inactive_user_cannot_access_panel(): void
    {
        $user = User::create(['name' => 'Off', 'email' => 'off@example.com', 'password' => 'password', 'role' => User::ROLE_EDITOR, 'is_active' => false]);

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }
}
