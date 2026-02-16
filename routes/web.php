<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Illuminate\Http\Request;

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
        // Double check this is an admin
        if (session('is_admin') !== true && !Auth::guard('admin')->check()) {
            Auth::logout();
            return redirect('/login');
        }
        
        return Inertia::render('Admin/Dashboard', [
            'admin' => Auth::guard('admin')->user(),
        ]);
    })->name('admin.dashboard');
});
Route::post('/logout', function (Request $request) {
    Auth::guard('web')->logout();
    Auth::guard('admin')->logout();
    
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    
    return redirect('/');
})->name('logout');

require __DIR__.'/settings.php';