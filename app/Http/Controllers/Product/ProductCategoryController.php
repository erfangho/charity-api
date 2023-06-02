<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the product categories.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $productCategories = ProductCategory::all();

        return response()->json($productCategories);
    }

    /**
     * Store a newly created product category in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $productCategory = ProductCategory::create($request->all());

        return response()->json($productCategory, 201);
    }

    /**
     * Display the specified product category.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $productCategory = ProductCategory::findOrFail($id);

        return response()->json($productCategory);
    }

    /**
     * Update the specified product category in storage.
     *
     * @param Request $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $productCategory = ProductCategory::findOrFail($id);
        $productCategory->update($request->all());

        return response()->json($productCategory);
    }

    /**
     * Remove the specified product category from storage.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $productCategory = ProductCategory::findOrFail($id);
        $productCategory->delete();

        return response()->json(null, 204);
    }
}
