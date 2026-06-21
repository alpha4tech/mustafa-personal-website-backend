<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogCategoryResource;
use App\Models\BlogCategory;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BlogCategoryController extends Controller
{
    /* ──────────────────────────────────────────
       GET /api/admin/blog/categories
    ────────────────────────────────────────── */
    public function index(): JsonResponse
    {
        $categories = BlogCategory::withCount([
            'posts' => fn($q) => $q->where('is_published', true)
        ])
        ->orderBy('name_ar')
        ->get();

        return response()->json([
            'status' => 'ok',
            'data'   => BlogCategoryResource::collection($categories),
        ]);
    }

    /* ──────────────────────────────────────────
       POST /api/admin/blog/categories
    ────────────────────────────────────────── */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name_ar'   => ['required', 'string', 'max:255'],
            'name_en'   => ['required', 'string', 'max:255'],
            'slug'      => ['nullable', 'string', 'max:255', 'unique:blog_categories,slug'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['slug']      = $data['slug'] ?? Str::slug($data['name_en']);
        $data['is_active'] = $data['is_active'] ?? true;

        // تأكد من فرادة الـ slug
        $base  = $data['slug'];
        $count = 1;
        while (BlogCategory::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $base . '-' . $count++;
        }

        $category = BlogCategory::create($data);

        return response()->json([
            'status'  => 'ok',
            'message' => 'تم إضافة التصنيف بنجاح',
            'data'    => new BlogCategoryResource($category),
        ], 201);
    }

    /* ──────────────────────────────────────────
       PUT /api/admin/blog/categories/{category}
    ────────────────────────────────────────── */
    public function update(Request $request, BlogCategory $category): JsonResponse
    {
        $data = $request->validate([
            'name_ar'   => ['sometimes', 'string', 'max:255'],
            'name_en'   => ['sometimes', 'string', 'max:255'],
            'slug'      => ['nullable', 'string', 'max:255',
                            Rule::unique('blog_categories', 'slug')->ignore($category->id)],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // إذا تغير الاسم الإنجليزي وما في slug جديد، جدد الـ slug
        if (isset($data['name_en']) && !isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name_en']);
        }

        $category->update($data);

        return response()->json([
            'status'  => 'ok',
            'message' => 'تم تحديث التصنيف بنجاح',
            'data'    => new BlogCategoryResource($category->fresh()),
        ]);
    }

    /* ──────────────────────────────────────────
       DELETE /api/admin/blog/categories/{category}
    ────────────────────────────────────────── */
    public function destroy(BlogCategory $category): JsonResponse
    {
        // افصل المقالات عن هذا التصنيف قبل الحذف
        $category->posts()->detach();
        $category->delete();

        return response()->json([
            'status'  => 'ok',
            'message' => 'تم حذف التصنيف',
        ]);
    }
}

