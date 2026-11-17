<?php

use App\Http\Controllers\CertificateController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TrainingController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/jadwal', [TrainingController::class, 'index'])->name('trainings.index');
Route::get('/kalender', [TrainingController::class, 'calendar'])->name('trainings.calendar');
Route::get('/kegiatan/{training}', [TrainingController::class, 'show'])->name('trainings.show');
Route::get('/kegiatan/{training}/undangan.pdf', [TrainingController::class, 'invitation'])->name('trainings.invitation');

Route::get('/kegiatan/{training}/daftar', [RegistrationController::class, 'create'])->name('registrations.create');
Route::post('/kegiatan/{training}/daftar', [RegistrationController::class, 'store'])->middleware('throttle:10,1')->name('registrations.store');
Route::get('/pendaftaran/{code}', [RegistrationController::class, 'show'])->name('registrations.show');

Route::get('/berita', [PostController::class, 'index'])->name('posts.index');
Route::get('/berita/{post}', [PostController::class, 'show'])->name('posts.show');

Route::get('/galeri', [GalleryController::class, 'index'])->name('galleries.index');
Route::get('/galeri/{gallery}', [GalleryController::class, 'show'])->name('galleries.show');

Route::get('/sertifikat', [CertificateController::class, 'index'])->middleware('throttle:30,1')->name('certificates.index');

Route::get('/in-house-training', [ContactController::class, 'inhouse'])->name('inhouse.create');
Route::post('/in-house-training', [ContactController::class, 'storeInhouse'])->middleware('throttle:5,1')->name('inhouse.store');
Route::get('/kontak', [ContactController::class, 'contact'])->name('contact');
Route::post('/kontak', [ContactController::class, 'storeContact'])->middleware('throttle:5,1')->name('contact.store');

Route::get('/faq', [PageController::class, 'faq'])->name('faq');
Route::get('/cari', [PageController::class, 'search'])->name('search');
Route::get('/halaman/{page}', [PageController::class, 'show'])->name('pages.show');
Route::redirect('/tentang-kami', '/halaman/tentang-kami')->name('about');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
