<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    function index()
    {
        return response()->json(
            Store::all(),
            200
            , ['OK']);
    }

    function get_products($storeId)
    {

        $store = Store::with('products')->find($storeId);
        if ($store) {
            return response()->json([
                'store' => $store,
                //  'products' => $store->products()->get()
                //'store' => $store->products()->orderBy('price')->get(),
            ], 200, ['OK']);
        } else return response()->json(['error' => 'store not found'], 404);
    }

    function search(Request $request)
    {
        $query = $request->input('store');
        if (!$request->has('store')) {
            return [];
        }
        $stores = Store::query()
            ->where('name', 'like', '%' . $query . '%')
            ->orWhere('description', 'like', '%' . $query . '%')
            ->get();

        return response()->json($stores);
    }
}
