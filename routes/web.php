<?php

use App\Http\Controllers\Admin\BookController as AdminBookController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BorrowingController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\Admin\AuthorController as AdminAuthorController;
use App\Http\Controllers\Admin\BorrowingController as AdminBorrowingController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\RecommendationController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------------
// Public routes: browsing, search, and auth screens. No sensitive data.
// ---------------------------------------------------------------------
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::get('/books/{book:slug}', [BookController::class, 'show'])->name('books.show');
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // -------------------------------------------------------------
    // Authenticated USER area. Every route below requires login;
    // none of it grants admin capability regardless of what a client
    // sends -- role is only ever read from the authenticated model.
    // -------------------------------------------------------------
    Route::get('/dashboard', UserDashboardController::class)->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/recommendations', [RecommendationController::class, 'index'])->name('recommendations.index');
    Route::get('/borrowings', [BorrowingController::class, 'index'])->name('borrowings.index');
    Route::post('/books/{book}/borrow', [BorrowingController::class, 'borrow'])->name('borrowings.borrow');
    Route::post('/borrowings/{borrowing}/return', [BorrowingController::class, 'return'])->name('borrowings.return');
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/books/{book}/favorite', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
    Route::delete('/favorites/{favorite}', [FavoriteController::class, 'destroy'])->name('favorites.destroy');

    Route::get('/chatbot', [ChatbotController::class, 'show'])->name('chatbot.show');
    Route::post('/chatbot/send', [ChatbotController::class, 'send'])->name('chatbot.send');

    // -------------------------------------------------------------
    // Admin-only area. Backend-enforced via the 'admin' middleware
    // (App\Http\Middleware\EnsureUserIsAdmin) -- a non-admin gets a
    // 403 even with a hand-crafted request or direct URL access.
    // -------------------------------------------------------------
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('users', AdminUserController::class)->except('show');
        Route::resource('books', AdminBookController::class)->except('show');
        Route::resource('categories', AdminCategoryController::class)->except('show');
        Route::resource('authors', AdminAuthorController::class)->except('show');
        Route::get('borrowings', [AdminBorrowingController::class, 'index'])->name('borrowings.index');
        Route::post('borrowings/{borrowing}/return', [AdminBorrowingController::class, 'return'])->name('borrowings.return');
    });
});
