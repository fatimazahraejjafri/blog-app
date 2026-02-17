<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PostController extends Controller
{
    // List all posts (admin sees all statuses)
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
                'created_at' => $post->created_at->format('M d, Y'),
                'user' => ['name' => $post->user->name],
                'category' => $post->category?->name,
                'featured_image' => $post->getFirstMediaUrl('featured_image'),
            ];
        });

        return Inertia::render('Admin/Posts/Index', [
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

    // Approve a post
    public function approve(Post $post)
    {
        $post->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        return back()->with('success', 'Post approved and published!');
    }

    // Reject a post
    public function reject(Post $post)
    {
        $post->update([
            'status' => 'archived',
        ]);

        return back()->with('success', 'Post rejected!');
    }

    // Set back to pending
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
}