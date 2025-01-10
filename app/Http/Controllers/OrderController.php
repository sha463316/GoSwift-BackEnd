<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Rules\CheckOrder;
use App\Rules\StockAvailable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function placeOrder(Request $request)
    {
        $request->validate([
            'payment_method' => ['required', Rule::in(['CashSyriatel', 'CashMTN', 'Cash', 'Card'])],
            'location' => 'required|string|max:255',
            'product_id' => 'required|numeric|exists:products,id',
        ]);

        $request->validate([
            'quantity' => ['required', 'integer', 'min:1', new StockAvailable($request->input('product_id'))],
        ]);

        $product = Product::find($request->input('product_id'));


        $order = Order::create([
            'user_id' => auth()->id(),
            'payment_method' => $request->input('payment_method'),
            'location' => $request->input('location'),
            'total_price' => $request->input('quantity') * $product->price,

        ]);
        OrderProduct::create([
            'order_id' => $order->id,
            'product_id' => $request->input('product_id'),
            'quantity' => $request->input('quantity'),
            'price' => $request->input('quantity') * $request->input('product_price'),
        ]);

        return response()->json(['message' => 'Order created successfully', 'order' => $order], 201);
    }


    public function updateOrder(Request $request, $order_id)
    {
        $request->validate([
            'payment_method' => ['required', Rule::in(['CashSyriatel', 'CashMTN', 'Cash', 'Card'])],
            'location' => 'required|string|max:255',
            'orderProduct_id' => 'required|numeric',
        ]);

        $request->validate([
            'quantity' => ['required', 'integer', 'min:1', new StockAvailable($request->input('orderProduct_id'))],
        ]);
        $product_id = $request->input('orderProduct_id');

        $order = Order::find($order_id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        if ($order->user_id !== auth()->id()) {
            return response()->json(['message' => 'You cannot edit this order'], 403);
        }
        $product_ids = $order->orderProducts()->pluck('product_id')->toArray();
        if (!in_array($request->input('orderProduct_id'), $product_ids)) {
            return response()->json(['message' => 'Order product not found'], 404);
        }

        $orderProduct = OrderProduct::where('order_id', $order_id)->where('product_id', $product_id)->first();
        $orderProduct->update([
            'quantity' => $request->input('quantity'),
            'price' => $request->input('quantity') * Product::find($product_id)->price
        ]);


        $items = $order->orderProducts()->with('product')->get();
        $total_price = 0.0;
        foreach ($items as $item) {
            $total_price += $item->quantity * $item->product->price;
        }
        $order->update([
            'payment_method' => $request->input('payment_method'),
            'location' => $request->input('location'),
            'total_price' => $total_price,
        ]);

        return response()->json(['message' => 'Order updated successfully', 'order' => $order, 'products' => $items], 201);

    }


    public
    function showOrders()
    {
        $orders = Order::where('user_id', auth()->id())->with('orderProducts')->get();
        return response()->json(['orders' => $orders], 200);
    }

    public
    function showOrder($order_id)
    {
        $order = Order::where('id', $order_id)->with('orderProducts')->first();
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        if ($order->user_id !== auth()->id()) {
            return response()->json(['message' => 'You cannot edit this order'], 403);
        }
        return response()->json(['order' => $order], 201);
    }

    public
    function deleteOrder($order_id)
    {
        $order = Order::where('id', $order_id)->first();
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        if ($order->user_id !== auth()->id()) {
            return response()->json(['message' => 'You cannot edit this order'], 403);
        }

        $order->orderProducts()->delete();
        $order->delete();
        return response()->json(['message' => 'Order deleted successfully'], 200);
    }


    public function accepted($order_id)
    {
        $order = Order::where('id', $order_id)->with('orderProducts')->first();
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        if ($order->status != 'Pending') {
            return response()->json(['message' => 'The order is not pending'], 404);
        }
        $items = $order->orderProducts()->get()->toArray();


        $accepted = true;
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            if ($item['quantity'] > $product->quantity) {
                $accepted = false;
            }
        }
        if (!$accepted) {
            return response()->json(['message' => 'The quantity of one product from order not enough'], 404);
        }
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            $product->update([
                'quantity' => $product->quantity - $item['quantity']
            ]);
        }
        $order->update(['status' => 'Accepted']);

        return response()->json(['order' => $order], 201);
    }

    public
    function declined(Request $request, $order_id)
    {
        $request->validate([
            'reason' => ['required', 'string'],
        ]);
        $order = Order::find($order_id);
        if ($order->status != 'Pending') {
            return response()->json(['message' => 'The order is not pending'], 404);
        }
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        $order->update(['status' => 'Declined']);
        return response()->json(['reason' => $request->input('reason')], 201);
    }

    public
    function showOrdersPending()
    {
        $orders = Order::where('status', 'Pending')->with('orderProducts')->get();
        return response()->json(['orders' => $orders], 200);

    }


//    public function createOrder(Request $request)
//    {
//        $request->validate([
//            'product_id' => 'required|exists:products,id',
//            'quantity' => 'required|integer|min:1',
//            'payment_method' => ['required', 'string', Rule::in(['CashSyriatel', 'CashMTN', 'Cash', 'Bank'])],
//            'order_location' => 'required|string',
//        ]);
//        $request->validate([
//            'quantity' => ['required', 'integer', 'min:1', new StockAvailable($request->input('product_id'))],
//        ]);
//        $product = Product::findOrFail($request->product_id);
//        $totalPrice = $product->price * $request->quantity;
//
//        $order = Order::create([
//            'user_id' => auth()->id(),
//            'store_id' => $product->store_id,
//            'product_id' => $product->id,
//            'quantity' => $request->quantity,
//            'total_price' => $totalPrice,
//            'payment_method' => $request->payment_method,
//            'order_location' => $request->order_location,
//        ]);
//
//        $product->decrement('quantity', $request->quantity);
//
//        return response()->json(['message' => 'Order created successfully', 'order' => $order], 201);
//    }


//    public function showUserOrders()
//    {
////         $orders = auth()->user()->orders()->with(['product', 'store'])->first();
//        $orders = auth()->user()->orders()->get();
//
//        return response()->json(['message' => 'ok', 'orders' => $orders], 200);
//    }

//    public function showUserOrder($id)
//    {
//        // $order = Order::where('id', $id)->with(['product', 'store'])->first();
//        $order = Order::where('id', $id)->first();
//
//        if ($order) {
//            if (auth()->user()->id != $order->user_id) {
//                return response()->json(['message' => 'Permission denied.'], 403);
//            }
//            return response()->json(['message' => 'ok', 'order' => $order], 200);
//        } else return response()->json(['message' => 'Order not found.'], 404);
//    }

//    public function updateOrder(Request $request, $order_id)
//    {
//        $order = Order::findOrFail($order_id);
//
//        if (auth()->user()->id != $order->user_id) {
//            return response()->json(['message' => 'Permission denied.'], 403);
//        }
//
//        $request->validate([
//            'quantity' => 'sometimes|integer|min:1',
//            'payment_method' => ['sometimes', 'string', Rule::in(['CashSyriatel', 'CashMTN', 'Cash', 'Bank'])],
//            'order_location' => 'sometimes|string',
//        ]);
//        $newQuantity = $request->input('quantity', $order->quantity);
//        $quantityDifference = $newQuantity - $order->quantity;
//
//        if ($quantityDifference > 0 && $order->product->quantity < $quantityDifference) {
//            return response()->json(['message' => 'Not enough stock available'], 400);
//        }
//        $totalPrice = $order->product->price * $newQuantity;
//        $order->update([
//            'quantity' => $newQuantity,
//            'total_price' => $totalPrice,
//            'payment_method' => $request->input('payment_method', $order->payment_method),
//            'order_location' => $request->input('order_location', $order->order_location),
//        ]);
//        $order->product->update(['quantity' => $order->product->quantity - $quantityDifference]);
//        return response()->json(['message' => 'Order updated successfully', 'order' => $order], 200);
//    }

//    public function deleteOrder($order_id)
//    {
//        $order = Order::where('id', $order_id)->first();
//        if ($order) {
//            if (auth()->user()->id != $order->user_id) {
//                return response()->json(['message' => 'Permission denied.'], 403);
//            }
//            $order->product->increment('quantity', $order->quantity);
//            $order->delete();
//            return response()->json(['message' => 'Order deleted successfully'], 204);
//        } else return response()->json(['message' => 'Order not found.'], 404);
//    }


}
