<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('product');
        if (!$request->has('product')) {
            return [];
        }
        $products = Product::query()
            ->where('name', 'like', '%' . $query . '%')
            ->orWhere('description', 'like', '%' . $query . '%')
            ->get();

        return response()->json($products);
    }

    public function index()
    {
        return response()->json(Product::all(), 200, ["OK"]);
    }

    public function get_product($product_id)
    {
        $pruduct = Product::where('id', $product_id)->first();
        if (!$pruduct) {
            return response()->json([], 404, ["Not Found"]);
        }
        return response()->json($pruduct, 200, ["OK"]);

    }
}
