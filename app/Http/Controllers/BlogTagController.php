<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBlogTagRequest;
use App\Http\Resources\BlogTagResource;
use App\Models\BlogTag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BlogTagController extends Controller
{
    /* ──────────────────────────────────────────
       GET /api/admin/blog/tags
    ────────────────────────────────────────── */
    public function index(): JsonResponse
    {
        $tags = BlogTag::withCount(['posts' => fn($q) => $q->published()])
            ->orderBy('name_ar')
            ->get();

        return response()->json([
            'status' => 'ok',
            'data'   => BlogTagResource::collection($tags),
        ]);
    }

    /* ──────────────────────────────────────────
       POST /api/admin/blog/tags
    ────────────────────────────────────────── */
    public function store(StoreBlogTagRequest $request): JsonResponse
    {
        $data = $request->validated();

        // auto-generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name_en']);
        }

        // ensure unique slug
        $base  = $data['slug'];
        $count = 1;
        while (BlogTag::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $base . '-' . $count++;
        }

        $tag = BlogTag::create($data);

        return response()->json([
            'status'  => 'ok',
            'message' => 'تم إنشاء الوسم بنجاح',
            'data'    => new BlogTagResource($tag),
        ], 201);
    }

    /* ──────────────────────────────────────────
       PUT /api/admin/blog/tags/{tag}
    ────────────────────────────────────────── */
    public function update(Request $request, BlogTag $tag): JsonResponse
    {
        $data = $request->validate([
            'name_ar' => ['sometimes', 'string', 'max:255'],
            'name_en' => ['sometimes', 'string', 'max:255'],
            'slug'    => ['nullable', 'string', 'max:255',
                          Rule::unique('blog_tags', 'slug')->ignore($tag->id)],
        ]);

        // إذا تغير الاسم الإنجليزي بدون slug جديد — جدد الـ slug
        if (isset($data['name_en']) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name_en']);
        }

        $tag->update($data);

        return response()->json([
            'status'  => 'ok',
            'message' => 'تم تحديث الوسم بنجاح',
            'data'    => new BlogTagResource($tag->fresh()),
        ]);
    }

    /* ──────────────────────────────────────────
       DELETE /api/admin/blog/tags/{tag}
    ────────────────────────────────────────── */
    public function destroy(BlogTag $tag): JsonResponse
    {
        $tag->posts()->detach();
        $tag->delete();

        return response()->json([
            'status'  => 'ok',
            'message' => 'تم حذف الوسم بنجاح',
        ]);
    }
}
