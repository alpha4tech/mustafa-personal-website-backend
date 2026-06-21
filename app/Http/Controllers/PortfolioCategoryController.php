<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\PortfolioCategoryResource;
use App\Models\PortfolioCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PortfolioCategoryController extends Controller
{
    public function index()
    {
        return PortfolioCategoryResource::collection(
            PortfolioCategory::withCount('items')->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name_ar' => 'required|string|max:100',
            'name_en' => 'required|string|max:100',
        ]);

        $data['slug'] = Str::slug($data['name_en']);

        $category = PortfolioCategory::create($data);

        return response()->json(new PortfolioCategoryResource($category), 201);
    }

    public function update(Request $request, PortfolioCategory $portfolioCategory)
    {
        $data = $request->validate([
            'name_ar' => 'required|string|max:100',
            'name_en' => 'required|string|max:100',
        ]);

        $data['slug'] = Str::slug($data['name_en']);
        $portfolioCategory->update($data);

        return response()->json(new PortfolioCategoryResource($portfolioCategory));
    }

    public function destroy(PortfolioCategory $portfolioCategory)
    {
        // Null out category_id on related items
        $portfolioCategory->items()->update(['category_id' => null]);
        $portfolioCategory->delete();

        return response()->json(['message' => 'Category deleted.']);
    }

    public function publicIndex()
    {
        return PortfolioCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }
}
