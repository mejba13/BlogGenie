<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\PostTitleController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\HomePageController;
use Illuminate\Support\Facades\Route;


require __DIR__.'/auth.php';

// Route for the homepage, showing posts on the welcome page
Route::get('/', [HomePageController::class, 'index'])->name('home');

Route::middleware('auth')->group(function () {

    // Admin dashboard
    Route::get('admin/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile management
    Route::get('admin/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('admin/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('admin/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Resource routes for Post Titles (includes create, edit, update, destroy, etc.)
    Route::resource('admin/post_titles', PostTitleController::class);

    // Post routes for authenticated users (create, store, etc.)
    Route::resource('admin/posts', PostController::class, ['as' => 'admin']); // This defines all CRUD routes for posts

    // Category management routes
    Route::resource('admin/categories', CategoryController::class);

    // Tags management routes
    Route::resource('admin/tags', TagController::class);
});

// Fallback route if the page is not found
Route::fallback(function () {
    return redirect()->route('home');
});
