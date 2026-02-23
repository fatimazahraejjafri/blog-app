<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\Admin\PostController as AdminPostController;

/*
|--------------------------------------------------------------------------
| Public Route
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    $user = Auth::user();

    return Inertia::render('Dashboard', [
        'posts' => [], // empty array for guests
        'auth' => [
            'user' => $user,
            'roles' => $user ? $user->getRoleNames() : [],
        ],
    ]);
})->name('home');

/*
|--------------------------------------------------------------------------
| User Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:web', 'verified', 'role:writer'])->group(function () {

    // User Dashboard
    Route::get('/dashboard', [PostController::class, 'feed'])
        ->middleware(['redirect.admin'])
        ->name('dashboard');

    // User Posts CRUD
    Route::resource('posts', PostController::class);

    // Media
    Route::get('/media/{media}', [MediaController::class, 'show'])
        ->name('media.show');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth:admin', 'role:admin'])
    ->name('admin.')
    ->group(function () {

        // Admin Dashboard
        Route::get('/dashboard', function () {
            $admin = Auth::guard('admin')->user();

            return Inertia::render('Admin/Dashboard', [
                'admin' => $admin,
                'roles' => $admin ? $admin->getRoleNames() : [],
            ]);
        })->name('dashboard');

        // Admin Post Management
        Route::get('/posts', [AdminPostController::class, 'index'])->name('posts.index');
        Route::patch('/posts/{post}/approve', [AdminPostController::class, 'approve'])->name('posts.approve');
        Route::patch('/posts/{post}/reject', [AdminPostController::class, 'reject'])->name('posts.reject');
        Route::patch('/posts/{post}/pending', [AdminPostController::class, 'pending'])->name('posts.pending');
        Route::delete('/posts/{post}', [AdminPostController::class, 'destroy'])->name('posts.destroy');
        Route::get('/posts/stats', [AdminPostController::class, 'stats'])->name('posts.stats');
    });

/*
|--------------------------------------------------------------------------
| Logout
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