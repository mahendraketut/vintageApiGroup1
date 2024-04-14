<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\CartOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponseTrait;

class OrderController extends Controller
{
    use ApiResponseTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get all orders with pagination
        $orders = Order::latest()->with('orderDetails')->paginate(10);

        // Return a collection of $orders with pagination
        return $this->successResponse($orders, 'Orders retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrderRequest $request)
    {
        // make a new order with the request data
        $orderId = 'ORD' . time() . rand(001, 999);

        // Calculate the total price of the order items
        $total = 0;
        foreach ($request->products as $productData) {
            // Retrieve the product details based on the product ID
            $product = Product::findOrFail($productData['id']);

            // Calculate the subtotal for this product
            $subtotal = $product->price * $productData['quantity'];
            $total += $subtotal;
        }

        $order = Order::create([
            'order_number' => $orderId,
            'user_id' => Auth::user()->id,
            'status' => 'pending',
            'total' => $total,
        ]);

        // Attach each product to the order with quantity and price
        foreach ($request->products as $productData) {
            $product = Product::findOrFail($productData['id']);
            $subtotal = $product->price * $productData['quantity'];

            $order->products()->attach($product->id, [
                'quantity' => $productData['quantity'],
                'price' => $product->price,
            ]);
        }

        // Return a success response
        return $this->successResponse($order, 'Order created successfully.', 201);
    }

    /**
     * Method that handle direct order creation
     *
     * @param StoreOrderRequest $request
     */
    public function directOrder(StoreOrderRequest $request)
    {
        // make a new order with the request data
        $orderId = 'ORD' . time() . rand(001, 999);

        // Calculate the total price of the order items
        $total = 0;
        foreach ($request->products as $productData) {
            // Retrieve the product details based on the product ID
            $product = Product::findOrFail($productData['id']);

            // Calculate the subtotal for this product
            $total += $product->price * $productData['quantity'];
        }

        $order = Order::create([
            'order_number' => $orderId,
            'user_id' => Auth::user()->id,
            'status' => 'pending',
            'total' => $total,
        ]);

        // Attach each product to the order with quantity and price
        foreach ($request->products as $productData) {
            $product = Product::findOrFail($productData['id']);
            $subtotal = $product->price * $productData['quantity'];

            $order->products()->attach($product->id, [
                'quantity' => $productData['quantity'],
                'price' => $product->price,
            ]);
        }

        // Return a success response
        return $this->successResponse($order, 'Direct order created successfully.', 201);
    }

    /**
     * Handle order creation from cart
     *
     * @param CartOrderRequest $request
     */
    public function cartOrder(CartOrderRequest $request)
    {
        //Retrieve cart items from the request
        $cartItems = Cart::whereIn('id', $request->cart_item_ids)->get();

        //Calculate the total price of the order items
        $total = 0;
        foreach ($cartItems as $cartItem) {
            //Retrieve the product details based on the product ID
            $product = Product::findOrFail($cartItem->product_id);

            //Calculate the subtotal for this product
            $total += $product->price * $cartItem->quantity;
        }

        //Create a new order
        $orderId = 'ORD' . time() . rand(001, 999);

        $order = Order::create([
            'order_number' => $orderId,
            'user_id' => Auth::user()->id,
            'status' => 'pending',
            'total' => $total,
        ]);

        //Attach each product to the order with quantity and price
        foreach ($cartItems as $cartItem) {
            $product = Product::findOrFail($cartItem->product_id);
            $subtotal = $product->price * $cartItem->quantity;

            $order->products()->attach($product->id, [
                'quantity' => $cartItem->quantity,
                'price' => $product->price,
            ]);
        }

        // Clear selected cart items from the cart
        Cart::whereIn('id', $request->cart_item_ids)->delete();

        //Return a success response
        return $this->successResponse($order, 'Order created successfully.', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        // Return a single order
        $order = Order::with('orderDetails')->findOrFail($order->id);

        return $this->successResponse($order, 'Order retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrderRequest $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function cancel(Order $order)
    {
        // Update the order status to cancelled and return a success response
        $order = Order::findOrFail($order->id);
        $order->update(['status' => 'cancelled']);

        return $this->successResponse($order, 'Order cancelled successfully.');
    }
}
