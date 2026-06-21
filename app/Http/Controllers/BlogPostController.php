<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBlogPostRequest;
use App\Http\Requests\UpdateBlogPostRequest;
use App\Http\Resources\BlogCategoryResource;
use App\Http\Resources\BlogPostResource;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\ActivityLogger;


class BlogPostController extends Controller
{
    /* ──────────────────────────────────────────
       GET /api/blog  (public)
    ────────────────────────────────────────── */
    public function index(Request $request): JsonResponse
    {
        $query = BlogPost::with(['categories', 'tags'])
            ->published()
            ->orderByDesc('published_at');

        if ($request->filled('category') && $request->category !== 'all') {
            $query->byCategory($request->category);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('title_ar', 'like', "%{$s}%")
                  ->orWhere('title_en', 'like', "%{$s}%")
                  ->orWhere('excerpt_ar', 'like', "%{$s}%")
                  ->orWhere('excerpt_en', 'like', "%{$s}%");
            });
        }

        $perPage = min((int) $request->get('per_page', 6), 24);
        $posts   = $query->paginate($perPage);

        return response()->json(
            BlogPostResource::collection($posts)->additional(['status' => 'ok'])
        );
    }

    /* ──────────────────────────────────────────
       GET /api/blog/featured  (public)
    ────────────────────────────────────────── */
    public function featured(): JsonResponse
    {
        $featured = BlogPost::with(['categories', 'tags'])
            ->published()
            ->featured()
            ->orderByDesc('published_at')
            ->first();

        $side = BlogPost::with(['categories', 'tags'])
            ->published()
            ->when($featured, fn($q) => $q->where('id', '!=', $featured->id))
            ->orderByDesc('published_at')
            ->take(2)
            ->get();

        return response()->json([
            'status'   => 'ok',
            'featured' => $featured ? new BlogPostResource($featured) : null,
            'side'     => BlogPostResource::collection($side),
        ]);
    }

    /* ──────────────────────────────────────────
       GET /api/admin/blog  (admin)
    ────────────────────────────────────────── */
    public function adminIndex(Request $request): JsonResponse
    {
        $query = BlogPost::with(['categories', 'tags'])      // ✅ كان: 'category'
            ->orderByDesc('created_at');

        if ($request->filled('status') && $request->status !== 'all') {
            if ($request->status === 'published') {
                $query->published();
            } elseif ($request->status === 'draft') {
                $query->where('is_published', false);
            } elseif ($request->status === 'scheduled') {
                $query->where('is_published', false)
                      ->whereNotNull('published_at')
                      ->where('published_at', '>', now());
            }
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('title_ar', 'like', "%{$s}%")
                  ->orWhere('title_en', 'like', "%{$s}%")
                  ->orWhere('slug',     'like', "%{$s}%");
            });
        }

        $perPage = min((int) $request->get('per_page', 15), 50);
        $posts   = $query->paginate($perPage);

        $stats = [
            'total'      => BlogPost::count(),
            'published'  => BlogPost::published()->count(),
            'draft'      => BlogPost::where('is_published', false)->count(),
            'totalViews' => BlogPost::sum('views_count'),
        ];

        return response()->json([
            'data'         => BlogPostResource::collection($posts->items()),
            'current_page' => $posts->currentPage(),
            'last_page'    => $posts->lastPage(),
            'total'        => $posts->total(),
            'from'         => $posts->firstItem(),
            'to'           => $posts->lastItem(),
            'per_page'     => $posts->perPage(),
            'stats'        => $stats,
        ]);
    }

    /* ──────────────────────────────────────────
       GET /api/admin/blog/{post}  (admin)
    ────────────────────────────────────────── */
    public function adminShow(BlogPost $post): JsonResponse
    {
        $post->load(['categories', 'tags']);                 // ✅ كان: 'category'

        return response()->json([
            'status' => 'ok',
            'data'   => new BlogPostResource($post),
        ]);
    }

    /* ──────────────────────────────────────────
       GET /api/blog/{slug}  (public)
    ────────────────────────────────────────── */
    public function show(string $slug): JsonResponse
    {
        $post = BlogPost::with(['categories', 'tags'])       // ✅ كان: 'category'
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $post->incrementViews();

        return response()->json([
            'status' => 'ok',
            'data'   => new BlogPostResource($post),
        ]);
    }

    /* ──────────────────────────────────────────
       POST /api/admin/blog  (admin)
    ────────────────────────────────────────── */
    public function store(StoreBlogPostRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['featured_image'] = $request->file('image')->store('blog', 'public');
        }

        $data['slug']    = $data['slug'] ?? Str::slug($data['title_en']) . '-' . time();
        $data['user_id'] = auth()->id();

        if (!empty($data['is_published']) && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $tagIds      = $data['tags']         ?? [];
        $categoryIds = $data['category_ids'] ?? [];
        unset($data['tags'], $data['category_ids']);

        $post = BlogPost::create($data);

        ActivityLogger::log('post_created', 'نشرت مقالاً جديداً <b>' . $post->title_ar . '</b>', $post);

        if (!empty($tagIds))      $post->tags()->sync($tagIds);
        if (!empty($categoryIds)) $post->categories()->sync($categoryIds);

        return response()->json([
            'status'  => 'ok',
            'message' => 'تم نشر المقال بنجاح',
            'data'    => new BlogPostResource($post->load(['categories', 'tags'])),
        ], 201);
    }

    /* ──────────────────────────────────────────
       PUT /api/admin/blog/{post}  (admin)
    ────────────────────────────────────────── */
    public function update(UpdateBlogPostRequest $request, BlogPost $post): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($post->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }
            $data['featured_image'] = $request->file('image')->store('blog', 'public');
        }

        if (!empty($data['is_published']) && !$post->is_published && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $categoryIds = $data['category_ids'] ?? null;
        $tagIds      = $data['tags']         ?? null;
        unset($data['category_ids'], $data['tags']);

        $post->update($data);

        ActivityLogger::log('post_updated', 'عدّلت مقال <b>' . $post->title_ar . '</b>', $post);

        if (!is_null($tagIds))      $post->tags()->sync($tagIds);
        if (!is_null($categoryIds)) $post->categories()->sync($categoryIds);

        return response()->json([
            'status'  => 'ok',
            'message' => 'تم تحديث المقال بنجاح',
            'data'    => new BlogPostResource($post->fresh(['categories', 'tags'])), // ✅
        ]);
    }

    /* ──────────────────────────────────────────
       DELETE /api/admin/blog/{post}  (admin)
    ────────────────────────────────────────── */
    public function destroy(BlogPost $post): JsonResponse
    {
        $post->delete();

        ActivityLogger::log('post_deleted', 'حذفت مقال <b>' . $post->title_ar . '</b>');

        return response()->json(['status' => 'ok', 'message' => 'تم حذف المقال']);
    }

    /* ──────────────────────────────────────────
       PATCH /api/admin/blog/{post}/toggle
    ────────────────────────────────────────── */
    public function toggleStatus(BlogPost $post): JsonResponse
    {
        $post->update([
            'is_published' => !$post->is_published,
            'published_at' => !$post->is_published ? now() : $post->published_at,
        ]);

        return response()->json([
            'status'  => 'ok',
            'message' => $post->is_published ? 'تم نشر المقال' : 'تم إلغاء نشر المقال',
            'data'    => new BlogPostResource($post),
        ]);
    }

    /* ──────────────────────────────────────────
       POST /api/admin/blog/bulk
    ────────────────────────────────────────── */
    public function bulk(Request $request): JsonResponse
    {
        $request->validate([
            'ids'    => 'required|array',
            'ids.*'  => 'integer|exists:blog_posts,id',
            'action' => 'required|in:publish,draft,delete',
        ]);

        $posts = BlogPost::whereIn('id', $request->ids);

        match ($request->action) {
            'publish' => $posts->update(['is_published' => true,  'published_at' => now()]),
            'draft'   => $posts->update(['is_published' => false]),
            'delete'  => $posts->delete(),
        };

        return response()->json(['status' => 'ok', 'message' => 'تم تنفيذ الإجراء بنجاح']);
    }

    /* ──────────────────────────────────────────
       GET /api/admin/blog/trashed
    ────────────────────────────────────────── */
    public function trashed(): JsonResponse
    {
        $posts = BlogPost::onlyTrashed()
            ->with(['categories'])                           // ✅ كان: 'category'
            ->orderByDesc('deleted_at')
            ->paginate(10);

        return response()->json(BlogPostResource::collection($posts));
    }

    /* ──────────────────────────────────────────
       PATCH /api/admin/blog/{id}/restore
    ────────────────────────────────────────── */
    public function restore(int $id): JsonResponse
    {
        $post = BlogPost::onlyTrashed()->findOrFail($id);
        $post->restore();

        return response()->json(['status' => 'ok', 'message' => 'تم استعادة المقال']);
    }

    /* ──────────────────────────────────────────
       GET /api/blog/categories  (public)
    ────────────────────────────────────────── */
    public function categories(): JsonResponse
    {
        $categories = BlogCategory::active()
            ->withCount(['posts' => fn($q) => $q->published()])
            ->get();

        return response()->json([
            'status' => 'ok',
            'data'   => BlogCategoryResource::collection($categories),
        ]);
    }
}
