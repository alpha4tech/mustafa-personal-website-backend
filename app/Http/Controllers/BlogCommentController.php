<?php
namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\BlogComment;
use Illuminate\Http\Request;

class BlogCommentController extends Controller
{
    // عرض تعليقات منشور (عامة - المعتمدة فقط)
    public function index($postId)
    {
        $post = BlogPost::findOrFail($postId);

        $comments = BlogComment::where('blog_post_id', $postId)
            ->where('is_approved', true)
            ->whereNull('parent_id')
            ->with(['replies' => function ($q) {
                $q->where('is_approved', true)->oldest();
            }])
            ->latest()
            ->get()
            ->map(fn($c) => $this->formatComment($c));

        return response()->json(['data' => $comments]);
    }

    // إضافة تعليق جديد (عامة)
    public function store(Request $request, $postId)
    {
        $post = BlogPost::findOrFail($postId);

        if (!$post->allow_comments) {
            return response()->json(['message' => 'التعليقات مغلقة'], 403);
        }

        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'email'     => 'required|email|max:150',
            'comment'   => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:blog_comments,id',
        ]);

        $comment = BlogComment::create([
            'blog_post_id' => $postId,
            'parent_id'    => $data['parent_id'] ?? null,
            'name'         => $data['name'],
            'email'        => $data['email'],
            'comment'      => $data['comment'],
            'is_approved'  => false,
            'ip_address'   => $request->ip(),
        ]);

        return response()->json([
            'message' => 'تم إرسال تعليقك وسيظهر بعد المراجعة',
            'data'    => $this->formatComment($comment),
        ], 201);
    }

    // ── Admin ──────────────────────────────────

    // كل التعليقات (أدمن)

    public function adminIndex(Request $request)
{
    $comments = BlogComment::with('post')
        ->when($request->status === 'pending', fn($q) => $q->where('is_approved', false))
        ->when($request->status === 'approved', fn($q) => $q->where('is_approved', true))
        ->latest()
        ->paginate(20);

    return response()->json([
        'data'         => $comments->map(fn($c) => $this->formatComment($c, true)),
        'current_page' => $comments->currentPage(),
        'last_page'    => $comments->lastPage(),
        'total'        => $comments->total(),
        'from'         => $comments->firstItem(),
        'to'           => $comments->lastItem(),
        'per_page'     => $comments->perPage(),
    ]);
}
    // public function adminIndex(Request $request)
    // {
    //     $comments = BlogComment::with('post')
    //         ->when($request->status === 'pending',  fn($q) => $q->where('is_approved', false))
    //         ->when($request->status === 'approved', fn($q) => $q->where('is_approved', true))
    //         ->latest()
    //         ->paginate(20);

    //     return response()->json([
    //         'data'       => $comments->map(fn($c) => $this->formatComment($c, true)),
    //         'pagination' => [
    //             'current_page' => $comments->currentPage(),
    //             'last_page'    => $comments->lastPage(),
    //             'total'        => $comments->total(),
    //         ],
    //     ]);
    // }

    // اعتماد / إلغاء اعتماد
    public function approve($id)
    {
        $comment = BlogComment::findOrFail($id);
        $comment->update(['is_approved' => !$comment->is_approved]);

        return response()->json([
            'message'     => $comment->is_approved ? 'تم الاعتماد' : 'تم إلغاء الاعتماد',
            'is_approved' => $comment->is_approved,
        ]);
    }

    // حذف
    public function destroy($id)
    {
        BlogComment::findOrFail($id)->delete();
        return response()->json(['message' => 'تم الحذف']);
    }
    public function adminReply(Request $request, $id): JsonResponse
{
    $request->validate(['comment' => 'required|string|max:1000']);

    $parent = BlogComment::findOrFail($id);

    $reply = BlogComment::create([
        'blog_post_id' => $parent->blog_post_id,
        'parent_id'    => $parent->id,
        'name'         => auth()->user()->name,
        'email'        => auth()->user()->email,
        'comment'      => $request->comment,
        'is_approved'  => true,  // رد الأدمن يُنشر مباشرة
    ]);

    // سجّل النشاط
    \App\Services\ActivityLogger::log(
        'message_read',
        'رددت على تعليق <b>' . $parent->name . '</b>',
    );

    return response()->json(['data' => $reply, 'message' => 'تم الرد بنجاح'], 201);
}


    private function formatComment($comment, $withPost = false)
    {
        $data = [
            'id'          => $comment->id,
            'name'        => $comment->name,
            'email'       => $comment->email,
            'comment'     => $comment->comment,
            'is_approved' => $comment->is_approved,
            'parent_id'   => $comment->parent_id,
            'created_at' => $comment->created_at?->toISOString(),
            'replies'     => $comment->relationLoaded('replies')
                ? $comment->replies->map(fn($r) => $this->formatComment($r))
                : [],
        ];

        if ($withPost && $comment->relationLoaded('post')) {
            $data['post_title'] = $comment->post?->title_ar;
            $data['post_id']    = $comment->blog_post_id;
        }

        return $data;
    }
}
