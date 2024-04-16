<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentStoreRequest;
use App\Models\Payment;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;

class PaymentController extends Controller
{

    use ApiResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payments = Payment::all();

        return $this->showResponse($payments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PaymentStoreRequest $request)
    {
        $payment = Payment::create([
            'method_id' => $request->method_id,
            'order_id' => $request->order_id,
            'amount' => $request->amount
        ]);

        $order_id = $request->order_id;

        $order = [
            'product_name' => 'Product A',
            'quantity' => 5,
            'price' => 200.00
        ];

        if (!$payment) {
            return $this->serverErrorResponse();
        }

        // $prefilledMessage = "Hi, I would like to pay for my order:\nProducts:".$order['product_name']."\nquantity: ".$order['quantity']."\nPrice: ".$order['price'];

        // $prefilledMessage = "Hi, I would like to pay for my order: ".$order_id;
        // $prefilledMessage = urlencode($prefilledMessage);
        $whatsappUrl = "https://api.whatsapp.com/send?phone=+6281246871634&text=Hi%2C%20I%20would%20like%20to%20pay%20for%20my%20order";

        return redirect()->away($whatsappUrl);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return $this->notFoundResponse();
        }

        return $this->showResponse($payment);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
