<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Illuminate\Http\Request;
use App\Http\Controllers\PostController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    // Prevent admins from accessing user dashboard
    if (session('is_admin') === true || Auth::guard('admin')->check()) {
        return redirect('/admin/dashboard');
    }
    
    return Inertia::render('Dashboard');
})->middleware(['auth:web', 'verified'])->name('dashboard');

Route::middleware(['auth:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        if (session('is_admin') !== true && !Auth::guard('admin')->check()) {
            Auth::logout();
            return redirect('/login');
        }
        return Inertia::render('Admin/Dashboard', [
            'admin' => Auth::guard('admin')->user(),
        ]);
    })->name('admin.dashboard');

    // Admin Post Management
    Route::get('/posts', [App\Http\Controllers\Admin\PostController::class, 'index'])->name('admin.posts.index');
    Route::patch('/posts/{post}/approve', [App\Http\Controllers\Admin\PostController::class, 'approve'])->name('admin.posts.approve');
    Route::patch('/posts/{post}/reject', [App\Http\Controllers\Admin\PostController::class, 'reject'])->name('admin.posts.reject');
    Route::patch('/posts/{post}/pending', [App\Http\Controllers\Admin\PostController::class, 'pending'])->name('admin.posts.pending');
    Route::delete('/posts/{post}', [App\Http\Controllers\Admin\PostController::class, 'destroy'])->name('admin.posts.destroy');
});
Route::post('/logout', function (Request $request) {
    Auth::guard('web')->logout();
    Auth::guard('admin')->logout();
    
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    
    return redirect('/');
})->name('logout');
Route::middleware(['auth:web', 'verified'])->group(function () {
       Route::resource('posts', PostController::class);
   });

require __DIR__.'/settings.php';