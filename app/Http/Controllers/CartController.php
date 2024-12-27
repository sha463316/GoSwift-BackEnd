<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Rules\StockAvailable;
use Illuminate\Http\Request;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Validation\Rule;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);
        $product = Product::find($request->input('product_id'));
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        $cart = $product->carts()->where('user_id', auth()->id())->first();

        if (!$cart) {
            $request->validate([
                'payment_method' => ['required', 'string', Rule::in(['CashSyriatel', 'CashMTN', 'Cash', 'Bank'])],
                'location' => 'required|string',

            ]);
            $request->validate([
                'quantity' => ['required', 'integer', 'min:1', new StockAvailable($request->input('product_id'))],
            ]);


            Cart::create([
                    'product_id' => $request->input('product_id'),
                    'quantity' => $request->input('quantity'),
                    'payment_method' => $request->input('payment_method'),
                    'location' => $request->input('location'),
                    'user_id' => auth()->user()->id,
                ]
            );
            return response()->json([
                'message' => 'Product added to cart successfully',
            ], 201);
        } else {
            $cart->delete();
            return response(status: 200);
        }
    }

    public function placeOrder()
    {
        $carts = Cart::where('user_id', auth()->id())->get();

        if ($carts->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 404);
        }

        foreach ($carts as $cart) {
            $product = Product::find($cart->product_id);
            if ($cart->quantity <= $product->quantity) {
                $product->update([
                    'quantity' => $product->quantity - $cart->quantity,
                ]);
                $totalPrice = $product->price * $cart->quantity;
                Order::create([
                    'user_id' => auth()->id(),
                    'store_id' => $product->store_id,
                    'product_id' => $product->id,
                    'quantity' => $cart->quantity,
                    'total_price' => $totalPrice,
                    'payment_method' => $cart->payment_method,
                    'order_location' => $cart->location,
                ]);
            } else {
                // For Notification
            }
        }
        $carts->each->delete();
        return response()->json(['message' => 'All Order placed successfully'], 201);
    }

    public function showCart()
    {
        $carts=Cart::where('user_id', auth()->id())->with('product')->get();
        return response()->json(['carts' => $carts], 200);
    }
}
