<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
Route::get('/search', [HomeController::class, 'search'])->name('posts.search');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        $panelId = resolvePanelIdFromUser();
        return Inertia::location(route('filament.' . $panelId . '.pages.dashboard'));
    })->name('dashboard');
});

Route::prefix('comments')->name('comments.')->middleware('auth')->group(function () {
    Route::post('/', [CommentController::class, 'store'])->name('store');
    Route::put('/{comment}', [CommentController::class, 'update'])->name('update');
    Route::delete('/{comment}', [CommentController::class, 'destroy'])->name('destroy');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
