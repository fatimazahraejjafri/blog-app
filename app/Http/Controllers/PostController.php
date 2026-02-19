<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{

    public function index(Request $request)
    {
        $query = Post::with(['user', 'category', 'tags', 'media'])->where('user_id', Auth::id());
        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%');
            });
        }

        $posts = $query->latest()->paginate(10)->through(function ($post) {
            return [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'content' => $post->content,
                'writer' => $post->writer,
                'status' => $post->status,
                'visibility' => $post->visibility,
                'published_at' => $post->published_at?->format('M d, Y'),
                'created_at' => $post->created_at->format('M d, Y'),
                'user' => [
                    'name' => $post->user->name,
                ],
                'category' => $post->category ? [
                    'id' => $post->category->id,
                    'name' => $post->category->name,
                ] : null,
                'tags' => $post->tags->map(fn($tag) => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                ]),
                'featured_image' => $post->getFirstMediaUrl('featured_image'),
            ];
        });

        return Inertia::render('Posts/Index', [
            'posts' => $posts,
            'categories' => Category::all(['id', 'name']),
            'filters' => $request->only(['search', 'status', 'category_id']),
        ]);
    }
public function show(Post $post)
{
    $post->load(['user', 'category', 'tags', 'media']);

    return Inertia::render('Posts/Show', [
        'post' => [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'content' => $post->content,
            'writer' => $post->writer,
            'status' => $post->status,
            'visibility' => $post->visibility,
            'published_at' => $post->published_at?->format('M d, Y'),
            'created_at' => $post->created_at->format('M d, Y'),
            'user' => [
                'name' => $post->user->name,
            ],
            'category' => $post->category ? [
                'id' => $post->category->id,
                'name' => $post->category->name,
            ] : null,
            'tags' => $post->tags->map(fn($tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
            ]),
            'featured_image' => $post->getFirstMediaUrl('featured_image'),
        ],
    ]);
}

    public function create()
    {
        return Inertia::render('Posts/Create', [
            'categories' => Category::all(['id', 'name']),
            'tags' => Tag::all(['id', 'name']),
        ]);
    }

   public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'category_id' => 'nullable|exists:categories,id',
        'writer' => 'nullable|string|max:255',
        'visibility' => 'required|in:public,private,unlisted',
        'tags' => 'nullable|array',
        'tags.*' => 'exists:tags,id',
        'featured_image' => 'nullable|image|max:2048',
    ]);

    $post = Auth::user()->posts()->create([
        'title' => $validated['title'],
        'content' => $validated['content'],
        'category_id' => $validated['category_id'] ?? null,
        'writer' => $validated['writer'] ?? Auth::user()->name,
        'visibility' => $validated['visibility'],
        'status' => 'pending', // Always pending by default
        'published_at' => null, // Only set when admin approves
    ]);

    if (!empty($validated['tags'])) {
        $post->tags()->attach($validated['tags']);
    }

    if ($request->hasFile('featured_image')) {
        $post->addMediaFromRequest('featured_image')
            ->toMediaCollection('featured_image');
    }

    return redirect('/posts')->with('success', 'Post submitted for approval!');
}
    public function edit(Post $post)
    {
        $post->load(['category', 'tags', 'media']);
        
        return Inertia::render('Posts/Edit', [
            'post' => [
                'id' => $post->id,
                'title' => $post->title,
                'content' => $post->content,
                'category_id' => $post->category_id,
                'writer' => $post->writer,
                'visibility' => $post->visibility,
                'status' => $post->status,
                'tags' => $post->tags->pluck('id')->toArray(),
                'featured_image' => $post->getFirstMediaUrl('featured_image'),
            ],
            'categories' => Category::all(['id', 'name']),
            'tags' => Tag::all(['id', 'name']),
        ]);
    }

    public function update(Request $request, Post $post)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'category_id' => 'nullable|exists:categories,id',
        'writer' => 'nullable|string|max:255',
        'visibility' => 'required|in:public,private,unlisted',
        'tags' => 'nullable|array',
        'tags.*' => 'exists:tags,id',
        'featured_image' => 'nullable|image|max:2048',
    ]);

    $post->update([
        'title' => $validated['title'],
        'content' => $validated['content'],
        'category_id' => $validated['category_id'] ?? null,
        'writer' => $validated['writer'] ?? $post->writer,
        'visibility' => $validated['visibility'],
        'status' => 'pending', // Reset to pending after edit so admin re-approves
        'published_at' => null, // Reset published_at
    ]);

    // Sync tags
    if (isset($validated['tags'])) {
        $post->tags()->sync($validated['tags']);
    } else {
        $post->tags()->detach(); // Remove all tags if none selected
    }

    // Update featured image
    if ($request->hasFile('featured_image')) {
        $post->clearMediaCollection('featured_image');
        $post->addMediaFromRequest('featured_image')
            ->toMediaCollection('featured_image');
    }

    return redirect('/posts')->with('success', 'Post updated and resubmitted for approval!');
}
    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()->route('posts.index')
            ->with('success', 'Post deleted successfully!');
    }
 public function feed()
{
    $posts = Post::with(['user', 'category', 'tags', 'media'])
        ->where('status', 'published')
        ->where('visibility', 'public')
        ->latest('published_at')
        ->get()
        ->map(function ($post) {
            return [
                'id' => $post->id,
                'title' => $post->title,
                'content' => $post->content,
                'writer' => $post->writer,
                'published_at' => $post->published_at?->format('M d, Y'),
                'created_at' => $post->created_at->format('M d, Y'),
                'user' => ['name' => $post->user->name],
                'category' => $post->category ? ['id' => $post->category->id, 'name' => $post->category->name] : null,
                'tags' => $post->tags->map(fn($tag) => ['id' => $tag->id, 'name' => $tag->name]),
                'featured_image' => $post->getFirstMediaUrl('featured_image'),
            ];
        });

    return Inertia::render('Dashboard', ['posts' => $posts]);
}
}