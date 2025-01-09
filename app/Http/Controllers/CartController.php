<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Rules\StockAvailable;
use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;


class CartController extends Controller
{


    public function addToCart(Request $request)
    {

        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ]);
        $request->validate([
            'quantity' => ['required', 'integer', 'min:1', new StockAvailable($request->input('product_id'))],
        ]);
        $product = Product::find($request->input('product_id'));
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        $id = auth()->user()->id;
        $cart = session()->get("cart{$id}");
        $cart[$product->id] = ([
            'id' => $product->id,
            'quantity' => $request->input('quantity'),
            'price' => $product->price * $request->input('quantity'),
            'product' => $product,
        ]);
        session()->put("cart{$id}", $cart);
        return session()->get("cart{$id}");
    }


    public function showCart()
    {

        $id = auth()->user()->id;
        $cart = session()->get("cart{$id}");
        return response()->json(['Cart' => $cart]);
    }

    public function updateCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ]);
        $request->validate([
            'quantity' => ['required', 'integer', 'min:1', new StockAvailable($request->input('product_id'))],
        ]);
        $product = Product::find($request->input('product_id'));
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        $id = auth()->user()->id;
        $cart = session()->get("cart{$id}");
        if (!$cart[$product->id]) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        $cart[$product->id]['quantity'] = $request->input('quantity');
        return response()->json(['message' => 'Cart updated', 'Cart' => $cart]);
    }

    public function clearCart(Request $request, $productId)
    {
        $id = auth()->user()->id;
        session()->forget("cart{$id}");
        return response()->json(['message' => 'Cart removed']);

    }

    public function deleteFromCart($productId)
    {
        $id = auth()->user()->id;
        $cart = session()->get("cart{$id}");
        unset($cart[$productId]);
        session()->put("cart{$id}", $cart);
        return response()->json(['message' => 'Product removed', 'Cart' => $cart]);
    }

    public function placeOrder(Request $request)
    {
        $request->validate([
            'payment_method' => ['required', Rule::in(['CashSyriatel', 'CashMTN', 'Cash', 'Card'])],
            'location' => 'required|string|max:255',
        ]);
        $id = auth()->user()->id;
        $carts = session()->get("cart{$id}");
        if (!$carts) {
            return response()->json(['message' => 'Cart is empty'], 404);
        }
        $order = Order::create([
            'user_id' => $id,
            'payment_method' => $request->input('payment_method'),
            'location' => $request->input('location'),
            'total_price' => 0

        ]);

        $totalPrice = 0;
        foreach ($carts as $cart) {
            $totalPrice += $cart['price'];
            OrderProduct::create([
                'order_id' => $order->id,
                'product_id' => $cart['id'],
                'quantity' => $cart['quantity'],
                'price' => $cart['price'],
            ]);
        }
        $order->update([
            'total_price' => $totalPrice
        ]);
        session()->forget("cart{$id}");
        return response()->json(['message' => 'Order created', 'Cart' => $carts]);
    }


//    public function addToCart(Request $request)
//    {
//        $request->validate([
//            'product_id' => 'required|exists:products,id',
//        ]);
//        $product = Product::find($request->input('product_id'));
//        if (!$product) {
//            return response()->json(['message' => 'Product not found'], 404);
//        }
//        $cart = $product->carts()->where('user_id', auth()->id())->first();
//
//        if (!$cart) {
//            $request->validate([
//                'payment_method' => ['required', 'string', Rule::in(['CashSyriatel', 'CashMTN', 'Cash', 'Bank'])],
//                'location' => 'required|string',
//
//            ]);
//            $request->validate([
//                'quantity' => ['required', 'integer', 'min:1', new StockAvailable($request->input('product_id'))],
//            ]);
//
//
//            Cart::create([
//                    'product_id' => $request->input('product_id'),
//                    'quantity' => $request->input('quantity'),
//                    'payment_method' => $request->input('payment_method'),
//                    'location' => $request->input('location'),
//                    'user_id' => auth()->user()->id,
//                ]
//            );
//            return response()->json([
//                'message' => 'Product added to cart successfully',
//            ], 201);
//        } else {
//            $cart->delete();
//            return response(status: 200);
//        }
//    }

//    public
//    function placeOrder()
//    {
//        $carts = Cart::where('user_id', auth()->id())->get();
//
//        if ($carts->isEmpty()) {
//            return response()->json(['message' => 'Cart is empty'], 404);
//        }
//
//        foreach ($carts as $cart) {
//            $product = Product::find($cart->product_id);
//            if ($cart->quantity <= $product->quantity) {
//                $product->update([
//                    'quantity' => $product->quantity - $cart->quantity,
//                ]);
//                $totalPrice = $product->price * $cart->quantity;
//                Order::create([
//                    'user_id' => auth()->id(),
//                    'store_id' => $product->store_id,
//                    'product_id' => $product->id,
//                    'quantity' => $cart->quantity,
//                    'total_price' => $totalPrice,
//                    'payment_method' => $cart->payment_method,
//                    'order_location' => $cart->location,
//                ]);
//            } else {
//
//                // For Notification
//            }
//        }
//        $carts->each->delete();
//        return response()->json(['message' => 'All Order placed successfully'], 201);
//    }

}
