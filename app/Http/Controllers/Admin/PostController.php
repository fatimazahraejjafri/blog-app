<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::with(['user', 'category', 'tags', 'media']);

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $posts = $query->latest()->paginate(10)->through(function ($post) {
            return [
                'id' => $post->id,
                'title' => $post->title,
                'writer' => $post->writer,
                'status' => $post->status,
                'visibility' => $post->visibility,
                'content' => $post->content,
                'created_at' => $post->created_at->format('M d, Y'),
                'user' => ['name' => $post->user->name],
                'category' => $post->category?->name,
                'featured_image' => $post->getFirstMediaUrl('featured_image'),
            ];
        });

        return Inertia::render('Admin/Manage', [
            'posts' => $posts,
            'filters' => $request->only(['search', 'status']),
            'stats' => [
                'pending' => Post::where('status', 'pending')->count(),
                'published' => Post::where('status', 'published')->count(),
                'draft' => Post::where('status', 'draft')->count(),
                'archived' => Post::where('status', 'archived')->count(),
            ]
        ]);
    }

    public function approve(Post $post)
    {
        $post->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        return back()->with('success', 'Post approved and published!');
    }

    public function reject(Post $post)
    {
        $post->update([
            'status' => 'archived',
        ]);

        return back()->with('success', 'Post rejected!');
    }

    public function pending(Post $post)
    {
        $post->update([
            'status' => 'pending',
            'published_at' => null,
        ]);

        return back()->with('success', 'Post set back to pending!');
    }

    public function destroy(Post $post)
    {
        $post->delete();
        return back()->with('success', 'Post deleted!');
    }
    
    public function stats(): JsonResponse
    {
        $posts = Post::all();

        $stats = [
            'total'      => $posts->count(),
            'published'  => $posts->where('status', 'published')->count(),
            'pending'    => $posts->where('status', 'pending')->count(),
            'draft'      => $posts->where('status', 'draft')->count(),
            'archived'   => $posts->where('status', 'archived')->count(),
            'thisMonth'  => Post::whereMonth('created_at', now()->month)
                                ->whereYear('created_at', now()->year)
                                ->count(),
            'topAuthors' => Post::select('user_id', DB::raw('count(*) as count'))
                                ->with('user:id,name')
                                ->groupBy('user_id')
                                ->orderByDesc('count')
                                ->limit(3)
                                ->get()
                                ->map(fn($p) => [
                                    'id'    => $p->user_id,
                                    'name'  => $p->user->name,
                                    'count' => $p->count,
                                ]),
        ];

        $recent = Post::with('user')
            ->latest()
            ->limit(5)
            ->get(['id', 'title', 'status']);

        // Last 30 days trend
        $trend = Post::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as count')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'stats'  => $stats,
            'recent' => $recent,
            'trend'  => [
                'labels' => $trend->pluck('date'),
                'data'   => $trend->pluck('count'),
            ],
        ]);
    }
}