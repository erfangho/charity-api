<?php

namespace App\Http\Controllers\Aid;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageItem;
use App\Models\Product;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PackageItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the package items.
     *
     * @return JsonResponse
     */
    public function index()
    {
        if (Gate::allows('is-manager-or-agent')) {

            $packageItems = PackageItem::all();

            return response()->json(['packageItems' => $packageItems]);
        }
    }

    /**
     * Store a newly created package item in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        if (Gate::allows('is-manager-or-agent')) {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'package_id' => 'required|exists:packages,id',
                'quantity' => 'required|integer',
            ]);

            $product = Product::find($request['product_id']);

            if ($product->quantity >= $request['quantity']) {
                $product->quantity -= $request['quantity'];
                $product->update();
            } else {
                return response()->json(['message' => 'quantity is more than quantity of product'], 422);
            }

            $packageItem = PackageItem::create($request->all());

            return response()->json($packageItem, 201);
        } else {
            return response()->json(['message' => 'access denied'], 403);
        }
    }

    /**
     * Display the specified package item.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $package = Package::findOrFail($id);

        return response()->json(['packageitems' => $package->packageItems]);
    }

    /**
     * Update the specified package item in storage.
     *
     * @param Request $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        if (Gate::allows('is-manager-or-agent')) {
            $request->validate([
                'quantity' => 'integer',
            ]);

            $packageItem = PackageItem::findOrFail($id);
            $product = $packageItem->product;


            if ($packageItem->quantity > $request['quantity']) {
                $product->quantity += $packageItem->quantity - $request['quantity'];
            } else {
                $product->quantity -= $request['quantity'] - $packageItem->quantity ;
            }

            $packageItem->update($request->all());
            $product->update();

            return response()->json($packageItem);
        } else {
            return response()->json(['message' => 'access denied'], 403);
        }
    }

    /**
     * Remove the specified package item from storage.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        if (Gate::allows('is-manager-or-agent')) {
            $packageItem = PackageItem::findOrFail($id);
            $packageItem->delete();

            return response()->json(null, 204);
        } else {
            return response()->json(['message' => 'access denied'], 403);
        }
    }
}
