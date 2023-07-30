<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the products.
     *
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        if (Gate::allows('is-manager-or-agent')) {
            $name = $request->input('name');

            $query = Product::orderBy('created_at', 'desc');

            if ($name) {
                $query->where('name', 'like', '%' . $name . '%');
            }

            $totalCountQuery = $query->count();
            $query->paginate(10);

            return response()->json([
                'products' => $query->get(),
                'count' => $totalCountQuery,
            ]);
        } else {
            return response()->json(['message' => 'Access denied'], 403);
        }
    }

    /**
     * Store a newly created product in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        if (Gate::allows('is-manager-or-agent')) {
            $request->validate([
                'name' => 'required|string',
                'category_id' => 'exists:product_categories,id',
                'type' => ['required', 'in:product,cash'],
                'quantity' => 'required|integer',
                'description' => 'nullable|string',
            ]);

            $product = Product::create($request->all());

            return response()->json($product, 201);
        } else {
            return response()->json(['message' => 'Access denied'], 403);
        }
    }

    /**
     * Display the specified product.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id)
    {
        if (Gate::allows('is-manager-or-agent')) {
            $product = Product::findOrFail($id);

            return response()->json($product);
        } else {
            return response()->json(['message' => 'Access denied'], 403);
        }
    }

    /**
     * Update the specified product in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        if (Gate::allows('is-manager-or-agent')) {
            $request->validate([
                'name' => 'string',
                'category_id' => 'exists:product_categories,id',
                'type' => ['in:product,cash'],
                'quantity' => 'integer',
                'description' => 'nullable|string',
            ]);

            $product = Product::findOrFail($id);
            $product->update($request->all());

            return response()->json($product);
        } else {
            return response()->json(['message' => 'Access denied'], 403);
        }
    }

    /**
     * Remove the specified product from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        if (Gate::allows('is-manager-or-agent')) {
            $product = Product::findOrFail($id);
            $product->delete();

            return response()->json(null, 204);
        } else {
            return response()->json(['message' => 'Access denied'], 403);
        }
    }
}
