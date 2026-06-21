<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServicesRequest;
use App\Http\Requests\UpdateServicesRequest;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\ActivityLogger;


class ServicesController extends Controller
{
        // ─── Public ───────────────────────────────────────────────
    public function publicIndex(): JsonResponse
    {
        $services = Service::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => ServiceResource::collection($services),
        ]);
    }

    // ─── Admin ────────────────────────────────────────────────
      public function index(Request $request): JsonResponse
    {
        $query = Service::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title_ar', 'like', "%{$search}%")
                  ->orWhere('title_en', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $services = $query->orderBy('sort_order')->orderBy('created_at', 'desc')->paginate(12);

        return response()->json([
            'data'       => ServiceResource::collection($services->items()),
            'pagination' => [
                'current_page' => $services->currentPage(),
                'last_page'    => $services->lastPage(),
                'per_page'     => $services->perPage(),
                'total'        => $services->total(),
            ],
        ]);
    }

        public function store(StoreServicesRequest $request)
    {
       $data = $request->validated();
        $data['list_desc_ar'] = array_values(array_filter($data['list_desc_ar'] ?? []));
        $data['list_desc_en'] = array_values(array_filter($data['list_desc_en'] ?? []));

        $service = Service::create($data);

        return response()->json([
            'message' => 'تم إنشاء الخدمة بنجاح',
            'data'    => new ServiceResource($service),
        ], 201);
    }


      public function show(Service $service): JsonResponse
    {
        return response()->json(['data' => new ServiceResource($service)]);
    }

    public function update(UpdateServicesRequest $request, Service $service): JsonResponse
    {
        $data = $request->validated();
        $data['list_desc_ar'] = array_values(array_filter($data['list_desc_ar'] ?? []));
        $data['list_desc_en'] = array_values(array_filter($data['list_desc_en'] ?? []));

        $service->update($data);
    ActivityLogger::log('service_created', 'أضفت خدمة جديدة <b>' . $service->title_ar . '</b>', $service);

        return response()->json([
            'message' => 'تم تحديث الخدمة بنجاح',
            'data'    => new ServiceResource($service->fresh()),
        ]);
    }

       public function destroy(Service $service): JsonResponse
    {
        $service->delete();

        return response()->json(['message' => 'تم حذف الخدمة بنجاح']);
    }

     public function toggleActive(Service $service): JsonResponse
    {
        $service->update(['is_active' => !$service->is_active]);

        return response()->json([
            'message'   => $service->is_active ? 'تم تفعيل الخدمة' : 'تم إيقاف الخدمة',
            'is_active' => $service->is_active,
        ]);
    }


    public function reorder(Request $request): JsonResponse
    {
        $request->validate(['items' => 'required|array', 'items.*.id' => 'required|integer', 'items.*.sort_order' => 'required|integer']);

        foreach ($request->items as $item) {
            Service::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['message' => 'تم تحديث الترتيب بنجاح']);
    }

}
