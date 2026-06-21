<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
   public function index(Request $request)
{
    $query = ServiceRequest::query();

    if ($request->filled('search')) {

        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('service_title', 'like', "%{$search}%")
              ->orWhere('name', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    if ($request->filled('is_read')) {

        $query->where(
            'is_read',
            filter_var($request->is_read, FILTER_VALIDATE_BOOLEAN)
        );
    }
    return $query
        ->latest()
        ->paginate(20);
}


    public function store(Request $request)
    {
        $data = $request->validate([
            'service_id' => ['nullable', 'exists:services,id'],
            'service_title' => ['required'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email'],
            'message' => ['nullable', 'string'],
        ]);

        ServiceRequest::create($data);

        return response()->json([
            'message' => 'Request submitted successfully'
        ]);
    }

      public function show($id)
    {
        $request = ServiceRequest::findOrFail($id);

        $request->update([
            'is_read' => true
        ]);

        return $request;
    }

      public function destroy($id)
    {
        ServiceRequest::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Deleted'
        ]);
    }
}
