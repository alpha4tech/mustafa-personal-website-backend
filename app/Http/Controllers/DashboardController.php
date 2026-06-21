<?php
namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Contact;
use App\Models\PortfolioItem;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        // ── إحصاءات قاعدة البيانات ──────────────────────────────
        $statistics = [
            'total_posts'        => BlogPost::count(),
            'total_Services'     => Service::count(),
            'published_posts'    => BlogPost::where('is_published', true)->count(),
            'draft_posts'        => BlogPost::where('is_published', false)->count(),
            'total_messages'     => Contact::count(),
            'unread_messages'    => Contact::whereNull('read_at')->count(),
            'portfolio_projects' => PortfolioItem::count(),
        ];

        // ── آخر النشاطات ─────────────────────────────────────────
        // إذا الجدول موجود نجلب منه، وإلا نرجع array فارغ
        $activityLog = [];
        try {
            if (Schema::hasTable('activity_logs')) {
                $activityLog = \App\Models\ActivityLog::with('user')
                    ->latest()
                    ->limit(8)
                    ->get()
                    ->map(fn ($log) => [
                        'type' => $log->type,
                        'icon' => $log->icon,
                        'text' => $log->action,
                        'time' => $log->created_at->diffForHumans(),
                    ])
                    ->toArray();
            }
        } catch (\Exception $e) {
            Log::error('ActivityLog error: ' . $e->getMessage());
        }

        // ── Google Analytics ──────────────────────────────────────
        $ga = null;
        try {
            if (
                config('services.google_analytics.property_id') &&
                config('services.google_analytics.credentials_path') &&
                file_exists(config('services.google_analytics.credentials_path'))
            ) {
                $ga = Cache::remember('ga_dashboard', 1800, function () {
                    $service = new \App\Services\GoogleAnalyticsService();
                    return [
                        'traffic_sources' => $service->getTrafficSources(),
                        'site_health'     => $service->getSiteHealth(),
                        'weekly_visits'   => $service->getWeeklyVisits(),
                    ];
                });
            }
        } catch (\Exception $e) {
            Log::error('GA Dashboard Error: ' . $e->getMessage());
            Cache::forget('ga_dashboard'); // امسح الـ cache إذا فيه خطأ
            $ga = null;
        }

        // ── أحدث الرسائل ─────────────────────────────────────────
        $messages = Contact::latest()
            ->limit(5)
            ->get()
            ->map(fn ($m) => [
                'id'               => $m->id,
                'name'             => $m->name,
                'message'          => $m->message,
                'read_at'          => $m->read_at,
                'created_at_human' => $m->created_at->diffForHumans(),
            ]);

        // ── Services ─────────────────────────────────────────
        $messages = Service::latest()
            ->limit(5)
            ->get()
            ->map(fn ($m) => [
                'id'               => $m->id,
                'title_ar'             => $m->title_ar,
                'title_en'          => $m->title_en,
                'desc_service_ar'          => $m->desc_service_ar,
                'desc_service_en'          => $m->desc_service_en,
                'created_at_human' => $m->created_at->diffForHumans(),
            ]);

        // ── أحدث المنشورات ───────────────────────────────────────
        $trendingPosts = BlogPost::where('is_published', true)
            ->orderByDesc('views')
            ->limit(5)
            ->get()
            ->map(fn ($p) => [
                'id'                 => $p->id,
                'title'              => $p->title_ar,
                'thumbnail'          => $p->thumbnail
                    ? asset('storage/' . $p->thumbnail)
                    : null,
                'views'              => $p->views ?? 0,
                'likes'              => $p->likes  ?? 0,
                'published_at_human' => $p->created_at->diffForHumans(),
            ]);

        return response()->json([
            'statistics'      => $statistics,
            'messages'        => $messages,
            'trending_posts'  => $trendingPosts,
            'activity_log'    => $activityLog,
            'traffic_sources' => $ga['traffic_sources'] ?? null,
            'site_health'     => $ga['site_health']     ?? null,
            'weekly_visits'   => $ga['weekly_visits']   ?? null,
        ]);
    }
}
