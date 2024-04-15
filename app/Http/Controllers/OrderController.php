<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\CartOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Cart;
use App\Models\ShippingAddress;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponseTrait;
use App\Traits\checkShippingCostTraits;

class OrderController extends Controller
{
    use ApiResponseTrait, checkShippingCostTraits;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get all orders with pagination
        $orders = Order::latest()->with('orderDetails', 'shipping')->paginate(10);

        // Return a collection of $orders with pagination
        return $this->successResponse($orders, 'Orders retrieved successfully.');
    }

    public function directOrder(StoreOrderRequest $request)
    {
        $address = ShippingAddress::findOrFail($request->address_id);
        // dd($address->city);

        $origin = 114; // TODO : Change this to seller origin city
        $destination = $this->getCityId($address->city);
        $weight = 100;
        $courier = $request->courier;
        $cost = 0;
        $service = '';

        $shippingCost = $this->calculateCost($origin, $destination, $weight, $courier);
        // dd($shippingCost);

        $shippingDetails = $this->extractShippingCostDetails($shippingCost);

        if (!$shippingDetails) {
            return response()->json(['success' => false, 'message' => 'Invalid shipping cost response.'], 400);
        }

        // Access extracted shipping details
        $service = $shippingDetails['service'];
        $etd = $shippingDetails['etd'];
        $cost = $shippingDetails['cost'];

        // Create order and attach products and shipping details
        $order = $this->createOrder($request->products, $service, $cost, $request->address_id);

        // Reload order with details for response
        $order = Order::with('orderDetails', 'shipping')->findOrFail($order->id);

        // Return success response with created order details
        return $this->successResponse($order, 'Direct order created successfully.', 201);
    }

    /**
     * Create a new order with associated products and shipping details.
     *
     * @param array $productsData
     * @param string $shippingService
     * @param float $shippingCost
     * @param int $addressId
     * @return Order
     */
    private function createOrder($productsData, $shippingService, $shippingCost, $addressId)
    {
        $total = 0;

        foreach ($productsData as $productData) {
            $product = Product::findOrFail($productData['id']);

            if ($product->quantity < $productData['quantity']) {
                throw new \Exception('Insufficient stock for product: ' . $product->name);
            }

            $subtotal = $product->price * $productData['quantity'];
            $total += $subtotal;

            // Update product quantity in stock
            $product->decrement('quantity', $productData['quantity']);
        }

        // Calculate total order amount (including shipping cost)
        $total += $shippingCost;

        // Create the order
        $order = Order::create([
            'order_number' => 'ORD' . time() . rand(001, 999),
            'user_id' => Auth::user()->id,
            'status' => 'pending',
            'total' => $total,
        ]);

        // Attach order details (products) to the order
        foreach ($productsData as $productData) {
            $product = Product::findOrFail($productData['id']);
            $order->orderDetails()->create([
                'product_id' => $product->id,
                'quantity' => $productData['quantity'],
                'price' => $product->price,
            ]);
        }

        // Attach shipping details to the order
        $order->shipping()->create([
            'shipping_address_id' => $addressId,
            'tracking_number' => 'TRK' . time() . rand(001, 999),
            'service' => $shippingService,
            'cost' => $shippingCost
        ]);

        return $order;
    }

    /**
     * Handle order creation from cart
     *
     * @param CartOrderRequest $request
     */
    public function cartOrder(CartOrderRequest $request)
    {
        $address = ShippingAddress::findOrFail($request->address_id);
        // dd($address->city);

        $origin = 114; // TODO : Change this to seller origin city
        $destination = $this->getCityId($address->city);
        $weight = 1000;
        $courier = $request->courier;
        $cost = 0;
        $service = '';

        $shippingCost = $this->calculateCost($origin, $destination, $weight, $courier);
        // dd($shippingCost);

        $shippingDetails = $this->extractShippingCostDetails($shippingCost);

        if (!$shippingDetails) {
            return response()->json(['success' => false, 'message' => 'Invalid shipping cost response.'], 400);
        }

        // Access extracted shipping details
        $service = $shippingDetails['service'];
        $etd = $shippingDetails['etd'];
        $cost = $shippingDetails['cost'];

        // Create order and attach products and shipping details
        $order = $this->createOrderFromCart($request, ['service' => $service, 'cost' => $cost]);

        // Attach the order detail and shipping to the order
        $order = Order::with('orderDetails', 'shipping')->findOrFail($order->id);

        //Return a success response
        return $this->successResponse($order, 'Order created successfully.', 201);
    }

    /**
     * Create a new order from a CartOrderRequest with associated shipping details.
     *
     * @param CartOrderRequest $request
     * @param array $shippingData
     * @return Order
     */
    private function createOrderFromCart(CartOrderRequest $request, $shippingData)
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

        // Calculate total order amount including shipping cost
        $total += $shippingData['cost'];

        // Clear selected cart items from the cart
        Cart::whereIn('id', $request->cart_item_ids)->delete();

        // Attach shipping details to the order

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

            //Update the product quantity in stock
            $product->decrement('quantity', $cartItem->quantity);
        }

        $order->shipping()->create([
            'shipping_address_id' => $request->address_id,
            'tracking_number' => 'TRK' . time() . rand(001, 999),
            'service' => $shippingData['service'],
            'cost' => $shippingData['cost'],
        ]);

        return $order;
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        // Return a single order
        $order = Order::with('orderDetails', 'shipping')->findOrFail($order->id);

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
