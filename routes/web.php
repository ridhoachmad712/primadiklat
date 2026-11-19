<?php

use App\Http\Controllers\CertificateController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TrainingController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

// Yearly open schedule: any topic, any date slot, any city/hotel.
Route::get('/jadwal', [ScheduleController::class, 'index'])->name('schedule.index');
Route::get('/tema', [TopicController::class, 'index'])->name('topics.index');
Route::get('/tema/{topic}', [TopicController::class, 'show'])->name('topics.show');
Route::get('/daftar', [RegistrationController::class, 'openCreate'])->name('register.create');
Route::post('/daftar', [RegistrationController::class, 'openStore'])->middleware('throttle:10,1')->name('register.store');

// Concrete classes (formed from registrations or created by admin).
Route::get('/kegiatan', [TrainingController::class, 'index'])->name('trainings.index');
Route::get('/kalender', [TrainingController::class, 'calendar'])->name('trainings.calendar');
Route::get('/kegiatan/{training}', [TrainingController::class, 'show'])->name('trainings.show');
Route::get('/kegiatan/{training}/undangan.pdf', [TrainingController::class, 'invitation'])->name('trainings.invitation');

Route::get('/kegiatan/{training}/daftar', [RegistrationController::class, 'create'])->name('registrations.create');
Route::post('/kegiatan/{training}/daftar', [RegistrationController::class, 'store'])->middleware('throttle:10,1')->name('registrations.store');
Route::get('/pendaftaran/{code}', [RegistrationController::class, 'show'])->name('registrations.show');
Route::post('/pendaftaran/{code}/bukti-bayar', [RegistrationController::class, 'uploadProof'])->middleware('throttle:6,1')->name('registrations.proof');
Route::get('/pendaftaran/{code}/invoice.pdf', [RegistrationController::class, 'invoice'])->name('registrations.invoice');
Route::get('/pendaftaran/{code}/kuitansi.pdf', [RegistrationController::class, 'receipt'])->name('registrations.receipt');

Route::get('/berita', [PostController::class, 'index'])->name('posts.index');
Route::get('/berita/{post}', [PostController::class, 'show'])->name('posts.show');

Route::get('/galeri', [GalleryController::class, 'index'])->name('galleries.index');
Route::get('/galeri/{gallery}', [GalleryController::class, 'show'])->name('galleries.show');

Route::get('/sertifikat', [CertificateController::class, 'index'])->middleware('throttle:30,1')->name('certificates.index');
Route::get('/sertifikat/{code}/unduh', [CertificateController::class, 'download'])->middleware('throttle:30,1')->name('certificates.download');

Route::get('/in-house-training', [ContactController::class, 'inhouse'])->name('inhouse.create');
Route::post('/in-house-training', [ContactController::class, 'storeInhouse'])->middleware('throttle:5,1')->name('inhouse.store');
Route::get('/kontak', [ContactController::class, 'contact'])->name('contact');
Route::post('/kontak', [ContactController::class, 'storeContact'])->middleware('throttle:5,1')->name('contact.store');

Route::get('/faq', [PageController::class, 'faq'])->name('faq');
Route::get('/cari', [PageController::class, 'search'])->name('search');
Route::get('/halaman/{page}', [PageController::class, 'show'])->name('pages.show');
Route::redirect('/tentang-kami', '/halaman/tentang-kami')->name('about');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('/og-image.png', [SeoController::class, 'ogImage'])->name('og.default');
