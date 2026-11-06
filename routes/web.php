<?php

use App\Http\Controllers\Admin\AppearanceController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FrameController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\PhotoboothController;
use App\Http\Middleware\AdminAuth;
use Illuminate\Support\Facades\Route;

Route::get('/', [PhotoboothController::class, 'welcome'])->name('home');
Route::get('/spa', [PhotoboothController::class, 'spa'])->name('spa');
Route::get('/spa-version', [PhotoboothController::class, 'version'])->name('spa.version');
Route::post('/mulai', [PhotoboothController::class, 'mulai'])->name('mulai');

Route::get('/tutorial', [PhotoboothController::class, 'tutorial'])->name('tutorial');

Route::get('/frame', [PhotoboothController::class, 'frame'])->name('frame');
Route::post('/frame', [PhotoboothController::class, 'frameStore'])->name('frame.store');

Route::get('/foto', [PhotoboothController::class, 'foto'])->name('foto');
Route::post('/foto', [PhotoboothController::class, 'fotoStore'])->name('foto.store');

Route::get('/filter', [PhotoboothController::class, 'filter'])->name('filter');
Route::post('/filter', [PhotoboothController::class, 'filterStore'])->name('filter.store');

Route::get('/metode', [PhotoboothController::class, 'metode'])->name('metode');
Route::post('/metode', [PhotoboothController::class, 'metodeStore'])->name('metode.store');

Route::get('/pembayaran', [PhotoboothController::class, 'pembayaran'])->name('pembayaran');
Route::post('/pembayaran', [PhotoboothController::class, 'pembayaranStore'])->name('pembayaran.store');

Route::get('/review', [PhotoboothController::class, 'review'])->name('review');
Route::post('/email', [PhotoboothController::class, 'emailStore'])->name('email.send');

Route::get('/selesai', [PhotoboothController::class, 'selesai'])->name('selesai');
Route::post('/selesai', [PhotoboothController::class, 'selesaiStore'])->name('selesai.store');

Route::get('/softfile', [PhotoboothController::class, 'download'])->name('softfile.download');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('login.post');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware(AdminAuth::class)->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('frames', FrameController::class)->except(['show']);
        Route::resource('vouchers', VoucherController::class)->except(['show']);
        Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
        Route::get('/logs/export', [LogController::class, 'export'])->name('logs.export');
        Route::get('/appearance', [AppearanceController::class, 'index'])->name('appearance.index');
        Route::put('/appearance', [AppearanceController::class, 'update'])->name('appearance.update');
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
});
