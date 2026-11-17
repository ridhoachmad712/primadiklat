<?php

namespace Tests\Feature;

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\Settings;
use App\Filament\Resources;
use App\Filament\Widgets;
use App\Models\Gallery;
use App\Models\Post;
use App\Models\Registration;
use App\Models\Training;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
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
            '/kontak', '/faq', '/cari?q=bimtek', '/halaman/tentang-kami', '/sitemap.xml',
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
            'name' => 'Peserta Uji',
            'institution' => 'BPKAD Uji',
            'email' => 'uji@example.com',
            'phone' => '081234567890',
            'package' => $training->prices[0]['name'],
            'agree' => '1',
        ]);

        $registration = Registration::where('email', 'uji@example.com')->firstOrFail();
        $response->assertRedirect(route('registrations.show', $registration->code));
        $this->assertSame((int) $training->prices[0]['price'], (int) $registration->price);
        Mail::assertSent(\App\Mail\RegistrationReceived::class);
        $this->get(route('registrations.show', $registration->code))->assertOk()->assertSee($registration->code);
    }

    public function test_registration_rejects_unknown_package_and_missing_consent(): void
    {
        $training = Training::upcoming()->get()->first(fn ($t) => $t->can_register);

        $this->post(route('registrations.store', $training), [
            'name' => 'X', 'institution' => 'Y', 'email' => 'x@example.com', 'phone' => '081234567890', 'package' => 'Paket Palsu',
        ])->assertSessionHasErrors(['package', 'agree']);
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
