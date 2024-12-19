<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    function __construct()
    {
        //$this->middleware(['auth:sanctum', 'admin']);
    }


    public function make_user_admin(Request $request)
    {
        $request->validate(['phone_number' => 'required|digits:10']);

        $user = User::where('phone_number', $request->phone_number)->first();
        if (!$user) {
            return response([
                'message' => 'user not found.'
            ], 404);
        }
        if ($user['role'] == 'admin') {
            return response()->json([
                'message' => 'user is already admin.'
            ]);
        }
        $user->update([
            'role' => 'admin'
        ]);
        return response([
            'message' => 'User with phone number ' . $user->phone_number . ' is now admin'
        ], 200);
    }


    function create_store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description' => 'required|string',
        ]);

        $store = Store::create([
            'name' => $request->input('name'),
            'address' => $request->input('address'),
            'description' => $request->input('description'),
            'image' => $this->uploadImage($request, 'stores')
        ]);
        return response()->json($store, 200, ['OK']);
    }

    function edit_store(Request $request, $store_id)
    {
        $store = Store::where('id', $store_id)->first();
        if (!$store) {
            return response(['message' => 'store not found.'], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description' => 'required|string',
        ]);
        if ($store->image != null && Storage::disk('public')->exists($store->image)) {
            Storage::disk('public')->delete($store->image);
        }
        $store->update([
            'name' => $request->input('name'),
            'address' => $request->input('address'),
            'description' => $request->input('description'),
            'image' => $this->uploadImage($request, 'stores')
        ]);
        return response()->json($store, 200, ['OK']);
    }

    function delete_store($store_id)
    {
        $store = Store::where('id', $store_id)->first();
        if (!$store) {
            return response(['message' => 'store not found.'], 404);
        }
        $store->products()->delete();
        $store->delete();
        return response()->json(status: 200);
    }

    function create_product(Request $request, $store_id)
    {
        $store = Store::where('id', $store_id)->first();
        if (!$store) {
            return response()->json(['message' => 'store not found'], 404);
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'quantity' => 'required|numeric',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        $product = Product::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'price' => $request->input('price'),
            'quantity' => $request->input('quantity'),
            'store_id' => $store_id,
            'image' => $this->uploadImage($request, 'products')
        ]);
        return response()->json($product, 200, ['OK']);
    }


    function edit_products(Request $request,  $product_id)
    {
        $product = Product::where('id', $product_id)->first();
        if (!$product) {
            return response()->json(['message' => 'product not found'], 404);
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'quantity' => 'required|numeric',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        if ($product->image != null && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }
        $product->update([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'price' => $request->input('price'),
            'quantity' => $request->input('quantity'),
            'image' => $this->uploadImage($request, 'products')
        ]);
        return response()->json($product, 200, ['OK']);
    }

    function delete_product($product_id)
    {

        $product = Product::where('id', $product_id)->first();
        if (!$product) {
            return response()->json(['message' => 'product not found'], 404);
        }
        $product->delete();
        return response()->json(status: 200);

    }


}
