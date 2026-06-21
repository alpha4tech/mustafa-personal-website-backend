<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ContactMeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\BlogPostController;
use App\Http\Controllers\BlogCategoryController;
use App\Http\Controllers\BlogTagController;
use App\Http\Controllers\PortfolioItemController;
use App\Http\Controllers\PortfolioCategoryController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\BlogCommentController;

use Illuminate\Support\Facades\Route;

/* ═══════════════════════════════════════════════
   Public routes (no auth required)
═══════════════════════════════════════════════ */
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
Route::post('/reset-password',  [ResetPasswordController::class, 'reset']);
Route::post('/register',        [AuthController::class, 'register']);
Route::post('/login',           [AuthController::class, 'login']);

Route::apiResource('contacts', ContactMeController::class);


// Blog — public routes
Route::prefix('blog')->group(function () {
    Route::get('/',           [BlogPostController::class, 'index']);
    Route::get('/featured',   [BlogPostController::class, 'featured']);
    Route::get('/categories', [BlogPostController::class, 'categories']);
    Route::get('/{slug}',     [BlogPostController::class, 'show']);
});

// public comment route
    Route::get('/blog/{postId}/comments', [BlogCommentController::class, 'index']);
    Route::post('/blog/{postId}/comments', [BlogCommentController::class, 'store']);

// Portfolio — public route
Route::get('/portfolio/', [PortfolioItemController::class, 'publicIndex']);
Route::get('/portfolio/categories', [PortfolioCategoryController::class, 'publicIndex']);


// Services — public route
Route::get('/services', [ServicesController::class, 'publicIndex']);

Route::post('/service-requests', [ServiceRequestController::class, 'store']);

Route::get('/dashboard', [DashboardController::class, 'index']);

/* ═══════════════════════════════════════════════
   Protected routes (auth:sanctum)
═══════════════════════════════════════════════ */
Route::middleware('auth:sanctum')->group(function () {


    Route::get('/user',    [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user',                  [ProfileController::class, 'getUser']);
    Route::put('/admin/profile',         [ProfileController::class, 'updateProfile']);
    Route::post('/admin/profile/avatar', [ProfileController::class, 'updateAvatar']);
    Route::put('/user/password',         [ProfileController::class, 'changePassword']);

    Route::get('notifications',                           [NotificationController::class, 'index']);
    Route::post('notifications/mark-all-read',            [NotificationController::class, 'markAllRead']);
    Route::post('notifications/{notification}/mark-read', [NotificationController::class, 'markOneRead']);

    /* ─────────────────────────────────────────
       Admin routes
    ───────────────────────────────────────── */
    Route::prefix('admin')->group(function () {
       // admin comments management
      Route::get('/comments',              [BlogCommentController::class, 'adminIndex']);
      Route::patch('/comments/{id}/approve', [BlogCommentController::class, 'approve']);
      Route::delete('/comments/{id}',      [BlogCommentController::class, 'destroy']);

        // ── Blogs ──────────────────────────────
        Route::prefix('blog')->group(function () {

            Route::get('/trashed',  [BlogPostController::class, 'trashed']);
            Route::post('/bulk',    [BlogPostController::class, 'bulk']);

            Route::get('/categories',               [BlogCategoryController::class, 'index']);
            Route::post('/categories',              [BlogCategoryController::class, 'store']);
            Route::put('/categories/{category}',    [BlogCategoryController::class, 'update']);
            Route::delete('/categories/{category}', [BlogCategoryController::class, 'destroy']);

            Route::get('/tags',          [BlogTagController::class, 'index']);
            Route::post('/tags',         [BlogTagController::class, 'store']);
            Route::put('/tags/{tag}',    [BlogTagController::class, 'update']);
            Route::delete('/tags/{tag}', [BlogTagController::class, 'destroy']);

            Route::get('/',    [BlogPostController::class, 'adminIndex']);
            Route::post('/',   [BlogPostController::class, 'store']);

            Route::get('/{post}',          [BlogPostController::class, 'adminShow']);
            Route::post('/{post}',         [BlogPostController::class, 'update']);
            Route::put('/{post}',          [BlogPostController::class, 'update']);
            Route::delete('/{post}',       [BlogPostController::class, 'destroy']);
            Route::patch('/{id}/restore',  [BlogPostController::class, 'restore']);
            Route::patch('/{post}/toggle', [BlogPostController::class, 'toggleStatus']);

             // public comments for a post
             Route::post('/comments/{id}/reply', [BlogCommentController::class, 'adminReply']);

            // Route::get('/{postId}/comments',  [BlogCommentController::class, 'index']);
            // Route::post('/{postId}/comments', [BlogCommentController::class, 'store']);

            Route::get('/blog/{postId}/comments', [BlogCommentController::class, 'index']);
            Route::get('/comments', [BlogCommentController::class, 'adminIndex']);
            Route::patch('/comments/{id}/approve', [BlogCommentController::class, 'approve']);
            Route::delete('/comments/{id}', [BlogCommentController::class, 'destroy']);

        });

        // ── Users ──────────────────────────────
        Route::middleware('role:admin')->group(function () {
            Route::get('/users',              [UserController::class, 'index']);
            Route::post('/users',             [UserController::class, 'store']);
            Route::put('/users/{id}/promote', [UserController::class, 'promote']);
            Route::put('/users/{id}/demote',  [UserController::class, 'demote']);
            Route::delete('/users/{id}',      [UserController::class, 'destroy']);
        });

        // ── Portfolio ──────────────────────────
        Route::prefix('portfolio')->group(function () {

            // Categories
            Route::get('/categories',                        [PortfolioCategoryController::class, 'index']);
            Route::post('/categories',                       [PortfolioCategoryController::class, 'store']);
            Route::put('/categories/{portfolioCategory}',    [PortfolioCategoryController::class, 'update']);
            Route::delete('/categories/{portfolioCategory}', [PortfolioCategoryController::class, 'destroy']);

            // Static routes BEFORE dynamic {portfolioItem}
            Route::get('/trashed',       [PortfolioItemController::class, 'trashed']);
            Route::post('/bulk',         [PortfolioItemController::class, 'bulk']);
            Route::post('/reorder',      [PortfolioItemController::class, 'reorder']);
            Route::post('/{id}/restore', [PortfolioItemController::class, 'restore']);
            Route::delete('/{id}/force', [PortfolioItemController::class, 'forceDelete']);

            // CRUD
            Route::get('/',                    [PortfolioItemController::class, 'index']);
            Route::post('/',                   [PortfolioItemController::class, 'store']);
            Route::get('/{portfolioItem}',     [PortfolioItemController::class, 'show']);
            Route::post('/{portfolioItem}',    [PortfolioItemController::class, 'update']);
            Route::delete('/{portfolioItem}',  [PortfolioItemController::class, 'destroy']);
        });

     // ── Services  ──────────────────────────
Route::prefix('services')->group(function () {
    // ✅ أولاً: المسارات الثابتة
    Route::get('/',             [ServicesController::class, 'index']);
    Route::post('/',            [ServicesController::class, 'store']);
    Route::post('/reorder',     [ServicesController::class, 'reorder']);

    // ✅ ثانياً: المسارات الديناميكية
    Route::get('/{service}',    [ServicesController::class, 'show']);
    Route::put('/{service}',    [ServicesController::class, 'update']);
    Route::delete('/{service}', [ServicesController::class, 'destroy']);
    Route::patch('/{service}/toggle-active', [ServicesController::class, 'toggleActive']);
});

     // ── Services  ──────────────────────────
    Route::get('/service-requests', [ServiceRequestController::class, 'index']);
    Route::get('/service-requests/{id}', [ServiceRequestController::class, 'show']);
    Route::delete('/service-requests/{id}', [ServiceRequestController::class, 'destroy']);

    }); // end prefix('admin')

}); // end auth:sanctum
