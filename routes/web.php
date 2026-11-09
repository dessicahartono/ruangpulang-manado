<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AdminSessionController;
use App\Http\Controllers\ArticleSubmissionController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JoinController;
use App\Http\Controllers\ManageBlogController;
use App\Http\Controllers\ManageDashboardController;
use App\Http\Controllers\ManageMemberController;
use App\Http\Controllers\PortofolioTeamController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/tentang', AboutController::class)->name('about');
Route::get('/kegiatan', ActivityController::class)->name('activity');
Route::get('/pengurus', PortofolioTeamController::class)->name('portofolioteam');
Route::get('/bergabung', JoinController::class)->name('join');
Route::get('/kontak', ContactController::class)->name('contact');

Route::controller(BlogController::class)->group(function () {
    Route::get('/blog', 'index')->name('blog.index');
    Route::get('/blog/{slug}', 'show')->name('blog.show');
});

// Siapa saja boleh mengirim artikel, tetapi baru tampil setelah disetujui admin.
Route::controller(ArticleSubmissionController::class)->group(function () {
    Route::get('/tulis-artikel', 'create')->name('articles.create');
    Route::post('/tulis-artikel', 'store')->middleware('throttle:article-submission')->name('articles.store');
});

Route::prefix('kelola')->name('manage.')->group(function () {
    Route::controller(AdminSessionController::class)->group(function () {
        Route::get('/masuk', 'create')->name('login');
        Route::post('/masuk', 'store')->middleware('throttle:admin-login')->name('login.store');
        Route::post('/keluar', 'destroy')->name('logout');
    });

    Route::middleware('admin')->group(function () {
        Route::get('/', ManageDashboardController::class)->name('dashboard');

        Route::controller(ManageBlogController::class)->prefix('blog')->name('blog.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/tambah', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{slug}/tinjau', 'show')->name('show');
            Route::get('/{slug}/ubah', 'edit')->name('edit');
            Route::put('/{slug}', 'update')->name('update');
            Route::post('/{slug}/setujui', 'approve')->name('approve');
            Route::delete('/{slug}', 'destroy')->name('destroy');
        });

        Route::controller(ManageMemberController::class)->prefix('pengurus')->name('members.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/tambah', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{member}/tinjau', 'show')->name('show');
            Route::get('/{member}/ubah', 'edit')->name('edit');
            Route::put('/{member}', 'update')->name('update');
            Route::delete('/{member}', 'destroy')->name('destroy');
        });
    });
});

// Dibatasi supaya endpoint publik tanpa login ini tidak bisa dipakai berlebihan.
Route::post('/teman-cerita/pesan', [ChatController::class, 'send'])->middleware('throttle:chat')->name('chat.send');
