<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePortfolioItemRequest;
use App\Http\Requests\UpdatePortfolioItemRequest;
use App\Http\Resources\PortfolioItemResource;
use App\Models\PortfolioItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\ActivityLogger;

class PortfolioItemController extends Controller
{
    // ─── Public: list published ──────────────────────────────────

    public function publicIndex(Request $request)
    {
        $items = PortfolioItem::with('category')
            ->published()
            ->ordered()
            ->when($request->category, fn($q) => $q->where('category_id', $request->category))
            ->when($request->featured, fn($q) => $q->featured())
            ->get();

        return PortfolioItemResource::collection($items);
    }

    // ─── Admin: paginated list ────────────────────────────────────

    public function index(Request $request)
    {
        $query = PortfolioItem::with('category')
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($q2) use ($request) {
                    $q2->where('title_ar', 'like', "%{$request->search}%")
                       ->orWhere('title_en', 'like', "%{$request->search}%")
                       ->orWhere('client_name', 'like', "%{$request->search}%");
                });
            })
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->category, fn($q) => $q->where('category_id', $request->category))
            ->when($request->featured !== null, fn($q) => $q->where('featured', (bool) $request->featured))
            ->ordered();

        $perPage = (int) ($request->per_page ?? 12);
        $items   = $query->paginate($perPage);

        return PortfolioItemResource::collection($items);
    }

    // ─── Admin: single item ───────────────────────────────────────

    public function show(PortfolioItem $portfolioItem)
    {
        return new PortfolioItemResource($portfolioItem->load('category'));
    }

    // ─── Admin: create ────────────────────────────────────────────

    public function store(StorePortfolioItemRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Thumbnail
        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')
                ->store('portfolio/thumbnails', 'public');
        }

        // Gallery
        if ($request->hasFile('gallery')) {
            $data['gallery'] = collect($request->file('gallery'))
                ->map(fn($file) => $file->store('portfolio/gallery', 'public'))
                ->values()
                ->toArray();
        }

        $item = PortfolioItem::create($data);

      ActivityLogger::log('project_created', 'أضفت مشروعاً جديداً <b>' . $item->title_ar . '</b>', $item);

        return response()->json(new PortfolioItemResource($item->load('category')), 201);
    }

    // ─── Admin: update ────────────────────────────────────────────

    public function update(UpdatePortfolioItemRequest $request, PortfolioItem $portfolioItem): JsonResponse
    {
        $data = $request->validated();

        // Remove thumbnail
        if ($request->boolean('remove_thumbnail') && $portfolioItem->thumbnail) {
            Storage::disk('public')->delete($portfolioItem->thumbnail);
            $data['thumbnail'] = null;
        }

        // Replace thumbnail
        if ($request->hasFile('thumbnail')) {
            if ($portfolioItem->thumbnail) {
                Storage::disk('public')->delete($portfolioItem->thumbnail);
            }
            $data['thumbnail'] = $request->file('thumbnail')
                ->store('portfolio/thumbnails', 'public');
        }

        // Remove specific gallery images
        $currentGallery = $portfolioItem->gallery ?? [];
        if ($request->remove_gallery) {
            foreach ($request->remove_gallery as $path) {
                // path could be full URL, extract storage path
                $storagePath = str_replace(asset('storage/'), '', $path);
                Storage::disk('public')->delete($storagePath);
                $currentGallery = array_filter(
                    $currentGallery,
                    fn($g) => $g !== $storagePath
                );
            }
            $data['gallery'] = array_values($currentGallery);
        }

        // Append new gallery images
        if ($request->hasFile('gallery')) {
            $newImages = collect($request->file('gallery'))
                ->map(fn($file) => $file->store('portfolio/gallery', 'public'))
                ->toArray();
            $data['gallery'] = array_merge($data['gallery'] ?? $currentGallery, $newImages);
        }

        $portfolioItem->update($data);

        ActivityLogger::log('project_updated', 'عدّلت مشروع <b>' . $portfolioItem->title_ar . '</b>', $portfolioItem);
        return response()->json(new PortfolioItemResource($portfolioItem->fresh()->load('category')));
    }

    // ─── Admin: soft delete ───────────────────────────────────────

    public function destroy(PortfolioItem $portfolioItem): JsonResponse
    {
        $portfolioItem->delete();
        return response()->json(['message' => 'Item moved to trash.']);
    }

    // ─── Admin: trashed ───────────────────────────────────────────

    public function trashed(): JsonResponse
    {
        $items = PortfolioItem::onlyTrashed()->with('category')->latest('deleted_at')->get();
        return response()->json(PortfolioItemResource::collection($items));
    }

    // ─── Admin: restore ───────────────────────────────────────────

    public function restore(int $id): JsonResponse
    {
        $item = PortfolioItem::onlyTrashed()->findOrFail($id);
        $item->restore();
        return response()->json(['message' => 'Item restored.']);
    }

    // ─── Admin: force delete ─────────────────────────────────────

    public function forceDelete(int $id): JsonResponse
    {
        $item = PortfolioItem::onlyTrashed()->findOrFail($id);

        if ($item->thumbnail) {
            Storage::disk('public')->delete($item->thumbnail);
        }
        foreach ($item->gallery ?? [] as $img) {
            Storage::disk('public')->delete($img);
        }

        $item->forceDelete();
        return response()->json(['message' => 'Item permanently deleted.']);
    }

    // ─── Admin: bulk actions ─────────────────────────────────────

    public function bulk(Request $request): JsonResponse
    {
        $request->validate([
            'action' => 'required|in:delete,publish,draft,archive,feature,unfeature',
            'ids'    => 'required|array|min:1',
            'ids.*'  => 'integer',
        ]);

        $ids    = $request->ids;
        $action = $request->action;

        match ($action) {
            'delete'    => PortfolioItem::whereIn('id', $ids)->delete(),
            'publish'   => PortfolioItem::whereIn('id', $ids)->update(['status' => 'published']),
            'draft'     => PortfolioItem::whereIn('id', $ids)->update(['status' => 'draft']),
            'archive'   => PortfolioItem::whereIn('id', $ids)->update(['status' => 'archived']),
            'feature'   => PortfolioItem::whereIn('id', $ids)->update(['featured' => true]),
            'unfeature' => PortfolioItem::whereIn('id', $ids)->update(['featured' => false]),
        };

        return response()->json(['message' => "Bulk action '{$action}' applied to " . count($ids) . " items."]);
    }

    // ─── Admin: reorder ──────────────────────────────────────────

    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'order'    => 'required|array',
            'order.*'  => 'integer',
        ]);

        foreach ($request->order as $sortOrder => $id) {
            PortfolioItem::where('id', $id)->update(['sort_order' => $sortOrder]);
        }

        return response()->json(['message' => 'Order updated.']);
    }
}
