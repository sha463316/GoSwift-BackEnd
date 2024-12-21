<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function createOrder(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'payment_method' => ['required', 'string', Rule::in(['CashSyriatel', 'CashMTN', 'Cash', 'Bank'])],
            'order_location' => 'required|string',
        ]);
        $product = Product::findOrFail($request->product_id);

        if ($product->quantity < $request->quantity) {
            return response()->json(['message' => 'Not enough stock available'], 400);
        }

        $totalPrice = $product->price * $request->quantity;

        $order = Order::create([
            'user_id' => auth()->id(),
            'store_id' => $product->store_id,
            'product_id' => $product->id,
            'quantity' => $request->quantity,
            'total_price' => $totalPrice,
            'payment_method' => $request->payment_method,
            'order_location' => $request->order_location,
        ]);

        $product->decrement('quantity', $request->quantity);

        return response()->json(['message' => 'Order created successfully', 'order' => $order], 201);
    }

    public function showUserOrders()
    {
//         $orders = auth()->user()->orders()->with(['product', 'store'])->first();
        $orders = auth()->user()->orders()->get();

        return response()->json(['message' => 'ok', 'orders' => $orders], 200);
    }

    public function showUserOrder($id)
    {
        // $order = Order::where('id', $id)->with(['product', 'store'])->first();
        $order = Order::where('id', $id)->first();

        if ($order) {
            if (auth()->user()->id != $order->user_id) {
                return response()->json(['message' => 'Permission denied.'], 403);
            }
            return response()->json(['message' => 'ok', 'order' => $order], 200);
        } else return response()->json(['message' => 'Order not found.'], 404);
    }

    public function updateOrder(Request $request, $order_id)
    {
        $order = Order::findOrFail($order_id);

        if (auth()->user()->id != $order->user_id) {
            return response()->json(['message' => 'Permission denied.'], 403);
        }

        $request->validate([
            'quantity' => 'sometimes|integer|min:1',
            'payment_method' => ['sometimes', 'string', Rule::in(['CashSyriatel', 'CashMTN', 'Cash', 'Bank'])],
            'order_location' => 'sometimes|string',
        ]);
        $newQuantity = $request->input('quantity', $order->quantity);
        $quantityDifference = $newQuantity - $order->quantity;

        if ($quantityDifference > 0 && $order->product->quantity < $quantityDifference) {
            return response()->json(['message' => 'Not enough stock available'], 400);
        }
        $totalPrice = $order->product->price * $newQuantity;
        $order->update([
            'quantity' => $newQuantity,
            'total_price' => $totalPrice,
            'payment_method' => $request->input('payment_method', $order->payment_method),
            'order_location' => $request->input('order_location', $order->order_location),
        ]);
        $order->product->update(['quantity' => $order->product->quantity - $quantityDifference]);
        return response()->json(['message' => 'Order updated successfully', 'order' => $order], 200);
    }

    public function deleteOrder($order_id)
    {
        $order = Order::where('id', $order_id)->first();
        if ($order) {
            if (auth()->user()->id != $order->user_id) {
                return response()->json(['message' => 'Permission denied.'], 403);
            }
            $order->product->increment('quantity', $order->quantity);
            $order->delete();
            return response()->json(['message' => 'Order deleted successfully'], 204);
        } else return response()->json(['message' => 'Order not found.'], 404);
    }


}
