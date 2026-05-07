<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::post('/contact', [HomeController::class, 'sendContact'])->name('contact.send');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    Route::get('/artikel/create', [ArticleController::class, 'create'])->name('articles.create');
    Route::post('/artikel', [ArticleController::class, 'store'])->name('articles.store');
    Route::get('/artikel/{article}/edit', [ArticleController::class, 'edit'])->name('articles.edit');
    Route::put('/artikel/{article}', [ArticleController::class, 'update'])->name('articles.update');
    Route::delete('/artikel/{article}', [ArticleController::class, 'destroy'])->name('articles.destroy');

    Route::resource('kategori', CategoryController::class)->parameters(['kategori' => 'category'])->except(['show']);
});

Route::middleware('auth')->group(function () {
    Route::get('/artikel/{slug}', [ArticleController::class, 'show'])->name('articles.show');
});
