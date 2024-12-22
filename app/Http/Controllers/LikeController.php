<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Product;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function likeOrDislike($product_id)
    {
        $product = Product::find($product_id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        $like = $product->likes()->where('user_id', auth()->id())->first();
        if (!$like) {
            Like::create([
                'user_id' => auth()->id(),
                'product_id' => $product_id,
            ]);
            return response()->json(['message' => 'Liked'], 201);
        }
        $like->delete();
        return response()->json(['message' => 'DesLiked'], 201);
    }

    public function getLikedProducts()
    {
        $likes = auth()->user()->likes()->with('product')->get();
        return response()->json(['data' => $likes, 'message' => 'OK'], 201);
    }

    public function getLikedProduct($like_id)
    {
        $like = Like::where('id', $like_id)->with('product')->first();
        if (!$like) {
            return response()->json(['message' => 'Like not found'], 404);
        }
        return response()->json(['data' => $like, 'message' => 'OK'], 201);
    }

    public function allLikedProducts()
    {
        //         return response()->json(Like::orderBy('product_id', 'asc')->get());
        //        return response()->json(Like::orderBy('product_id', 'desc')->get());
        return response()->json(['data' => Like::with('product')->get()]);
    }
}
