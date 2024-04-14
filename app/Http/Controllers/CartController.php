<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartStoreRequest;
use App\Http\Requests\CartUpdateRequest;
use App\Models\Cart;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;

class CartController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth('sanctum')->user();

        $cartProducts = Cart::with('Product')->where('user_id', $user->id)->get();

        if (!$cartProducts) {
            return $this->notFoundResponse();
        }

        return $this->showResponse($cartProducts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CartStoreRequest $request)
    {
        $user = auth('sanctum')->user();

        // make sure user can't add more than 100 unique products to cart
        $maxProducts = 100;

        // count the amount of different product has been added to cart
        $productCountQuery = Cart::where('user_id', $user->id)
            ->select('product_id')
            ->distinct()
            ->count();


        if ($productCountQuery >= $maxProducts) {
            return $this->errorResponse('You can only add a maximum of ' . $maxProducts . ' unique products to your cart.', 400);
        }


        // check if user have added the product to cart before
        $cart = Cart::where('user_id', $user->id)->where('product_id', $request->product_id)->first();

        if (!is_null($cart)) {
            $productQuantity = $cart->product->quantity;
            $request['quantity'] = $cart->quantity + 1;

            if ($request['quantity'] <= $productQuantity) {
                $cart->update([
                    'product_id' => $request->product_id,
                    'quantity' => $request->quantity,
                ]);

                return $this->successResponse($cart);
            }
        }

        $cart = Cart::create([
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'user_id' => $user->id
        ]);

        if (!$cart) {
            return $this->serverErrorResponse();
        }

        return $this->createdResponse($cart->product);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cart = Cart::with('Product')->find($id);

        return $this->showResponse($cart);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CartUpdateRequest $request, string $id)
    {
        $cart = Cart::findOrFail($id);

        $cart->update([
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
        ]);

        return $this->updateResponse($cart->product);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cart = Cart::findOrFail($id);
        $cart->delete();

        return $this->deleteResponse($cart->product);
    }
}