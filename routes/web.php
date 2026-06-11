<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LearningController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/jelajahi/{section}', [HomeController::class, 'explore'])
    ->whereIn('section', ['recommended', 'latest', 'popular', 'discovery'])
    ->name('articles.explore');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::post('/contact', [HomeController::class, 'sendContact'])
    ->middleware('throttle:5,1')
    ->name('contact.send');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'account.active', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('/admin/artikel/{article}/approve', [AdminController::class, 'approve'])->name('admin.articles.approve');
    Route::post('/admin/artikel/{article}/reject', [AdminController::class, 'reject'])->name('admin.articles.reject');
    Route::post('/admin/artikel/{article}/revision/{revision}/approve', [AdminController::class, 'approveRevision'])->name('admin.articles.revisions.approve');
    Route::post('/admin/artikel/{article}/revision/{revision}/reject', [AdminController::class, 'rejectRevision'])->name('admin.articles.revisions.reject');
    Route::get('/admin/pesan', [AdminController::class, 'messages'])->name('admin.messages.index');
    Route::get('/admin/pesan/{contactMessage}', [AdminController::class, 'showMessage'])->name('admin.messages.show');
    Route::delete('/admin/pesan/{contactMessage}', [AdminController::class, 'destroyMessage'])->name('admin.messages.destroy');
    Route::patch('/admin/pesan/{contactMessage}/read', [AdminController::class, 'updateMessageReadStatus'])->name('admin.messages.read');
    Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users.index');
    Route::patch('/admin/users/{user}/role', [AdminController::class, 'updateUserRole'])->name('admin.users.role');
    Route::patch('/admin/users/{user}/status', [AdminController::class, 'updateUserStatus'])->name('admin.users.status');
    Route::get('/admin/activity', [AdminController::class, 'activity'])->name('admin.activity.index');

    Route::resource('kategori', CategoryController::class)->parameters(['kategori' => 'category'])->except(['show']);
});

Route::get('/kategori/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');

Route::middleware(['auth', 'account.active', 'author'])->group(function () {
    Route::get('/author/dashboard', [AuthorController::class, 'dashboard'])->name('author.dashboard');
});

Route::middleware(['auth', 'account.active', 'writer'])->group(function () {
    Route::get('/artikel/create', [ArticleController::class, 'create'])->name('articles.create');
    Route::post('/artikel', [ArticleController::class, 'store'])->name('articles.store');
    Route::post('/artikel/media', [ArticleController::class, 'uploadContentMedia'])->name('articles.media.store');
    Route::get('/artikel/{article}/edit', [ArticleController::class, 'edit'])->name('articles.edit');
    Route::post('/artikel/{article}/revision', [ArticleController::class, 'storeRevision'])->name('articles.revisions.store');
    Route::put('/artikel/{article}', [ArticleController::class, 'update'])->name('articles.update');
    Route::post('/artikel/{article}/submit-review', [ArticleController::class, 'submitForReview'])->name('articles.submit-review');
    Route::delete('/artikel/{article}', [ArticleController::class, 'destroy'])->name('articles.destroy');
});

Route::get('/artikel/{slug}', [ArticleController::class, 'show'])->name('articles.show');

Route::middleware(['auth', 'account.active'])->group(function () {
    Route::get('/profil', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profil/foto', [ProfileController::class, 'updatePhoto'])->name('profile.photo.update');
    Route::delete('/profil/foto', [ProfileController::class, 'destroyPhoto'])->name('profile.photo.destroy');
    Route::put('/profil/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    Route::get('/belajar/histori', [LearningController::class, 'history'])->name('learning.history');
    Route::get('/belajar/favorit', [LearningController::class, 'favorites'])->name('learning.favorites');
    Route::get('/belajar/koleksi', [LearningController::class, 'collections'])->name('learning.collections');
    Route::post('/belajar/koleksi', [LearningController::class, 'storeCollection'])->name('learning.collections.store');
    Route::put('/belajar/koleksi/{collection}', [LearningController::class, 'updateCollection'])->name('learning.collections.update');
    Route::delete('/belajar/koleksi/{collection}', [LearningController::class, 'destroyCollection'])->name('learning.collections.destroy');
    Route::post('/artikel/{article}/favorite', [ArticleController::class, 'toggleFavorite'])->name('articles.favorite.toggle');
    Route::post('/artikel/{article}/collection', [ArticleController::class, 'toggleCollection'])->name('articles.collection.toggle');
    Route::post('/artikel/{article}/quiz-attempt', [ArticleController::class, 'submitQuizAttempt'])->name('articles.quiz-attempts.store');
});
