<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Laravel\Fortify\Features;

use App\Http\Controllers\PostController;
use App\Http\Controllers\Admin\PostController as AdminPostController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');


/*
|--------------------------------------------------------------------------
| User Routes (Web Guard)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:web', 'verified'])->group(function () {

    // User Dashboard
   Route::get('/dashboard', [PostController::class, 'feed'])
    ->middleware(['auth:web', 'verified', 'redirect.admin'])
    ->name('dashboard');

    // User Posts CRUD
    Route::resource('posts', PostController::class);
});


/*
|--------------------------------------------------------------------------
| Admin Routes (Admin Guard)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->middleware(['auth:admin'])
    ->name('admin.')
    ->group(function () {

        // Admin Dashboard
        Route::get('/dashboard', function () {
            return Inertia::render('Admin/Dashboard', [
                'admin' => Auth::guard('admin')->user(),
            ]);
        })->name('dashboard');

        // Admin Post Management
        Route::get('/posts', [AdminPostController::class, 'index'])
            ->name('posts.index');

        Route::patch('/posts/{post}/approve', [AdminPostController::class, 'approve'])
            ->name('posts.approve');

        Route::patch('/posts/{post}/reject', [AdminPostController::class, 'reject'])
            ->name('posts.reject');

        Route::patch('/posts/{post}/pending', [AdminPostController::class, 'pending'])
            ->name('posts.pending');

        Route::delete('/posts/{post}', [AdminPostController::class, 'destroy'])
            ->name('posts.destroy');


        Route::get('/posts/stats', [AdminPostController::class, 'stats'])->name('posts.stats');});


/*
|--------------------------------------------------------------------------
| Logout (Handles both guards)
|--------------------------------------------------------------------------
*/

Route::post('/logout', function (Request $request) {

    Auth::guard('web')->logout();
    Auth::guard('admin')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
})->name('logout');

require __DIR__.'/settings.php';