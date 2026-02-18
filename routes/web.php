<?php
// In your routes/web.php — replace the admin dashboard route with this:

Route::get('/dashboard', function () {
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

        $posts = \App\Models\Post::query();

        return Inertia::render('Admin/Dashboard', [
            'admin' => Auth::guard('admin')->user()->only('name', 'email'),

            'stats' => [
                'total'     => $posts->count(),
                'pending'   => (clone $posts)->where('status', 'pending')->count(),
                'published' => (clone $posts)->where('status', 'published')->count(),
                'archived'  => (clone $posts)->where('status', 'archived')->count(),
                'draft'     => (clone $posts)->where('status', 'draft')->count(),
            ],

            'recentPosts' => \App\Models\Post::with(['user', 'category'])
                ->latest()
                ->take(8)
                ->get()
                ->map(fn ($post) => [
                    'id'             => $post->id,
                    'title'          => $post->title,
                    'status'         => $post->status,
                    'featured_image' => $post->featured_image,
                    'created_at'     => $post->created_at->diffForHumans(),
                    'writer'         => $post->writer,
                    'user'           => $post->user ? ['name' => $post->user->name] : null,
                ]),
        ]);
    })->name('admin.dashboard');

    // Post management routes (unchanged)
    Route::get('/posts', [\App\Http\Controllers\Admin\PostController::class, 'index'])->name('admin.posts.index');
    Route::patch('/posts/{post}/approve', [\App\Http\Controllers\Admin\PostController::class, 'approve'])->name('admin.posts.approve');
    Route::patch('/posts/{post}/reject', [\App\Http\Controllers\Admin\PostController::class, 'reject'])->name('admin.posts.reject');
    Route::patch('/posts/{post}/pending', [\App\Http\Controllers\Admin\PostController::class, 'pending'])->name('admin.posts.pending');
    Route::delete('/posts/{post}', [\App\Http\Controllers\Admin\PostController::class, 'destroy'])->name('admin.posts.destroy');
});