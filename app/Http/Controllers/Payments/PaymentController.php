<?php

namespace App\Http\Controllers\Payments;

use App\Http\Requests\PaymentStoreRequest;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Midtrans\Config;
use Midtrans\CoreApi;

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

        $order = Order::find($request->order_id);
        $totalPrice = $order->total;

        $payment = Payment::create([
            'order_id' => $request->order_id,
            'amount' => $totalPrice
        ]);

        // Set your Merchant Server Key
        \Midtrans\Config::$serverKey = config('midtrans.serverKey');
        // Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
        \Midtrans\Config::$isProduction = false;
        // Set sanitization on (default)
        \Midtrans\Config::$isSanitized = true;
        // Set 3DS transaction for credit card to true
        \Midtrans\Config::$is3ds = true;

        $params = array(
            'transaction_details' => array(
                'order_id' => $request->order_id,
                'gross_amount' => $totalPrice,
            ),
            'customer_details' => array(
                'name' => Auth::user()->username,
                'email' => Auth::user()->email
            )
        );
        
        $snapResponse = \Midtrans\Snap::createTransaction($params);

        $payment->snap_token = $snapResponse->token;
        $payment->save();

        if (!$payment) {
            return $this->serverErrorResponse();
        }

        return $this->createdResponse($snapResponse);
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

    // payment success notification (will be hooked by midtrans)
    public function notification(Request $request)
    {
        $transaction = $request->input('transaction_status');
        $paymentMethod = $request->input('payment_type');
        $orderId = $request->input('order_id');
        $fraud = $request->input('fraud_status');

        // Retrieve the order from the database
        $order = Order::where('id', $orderId)->first();

        // handling order status according to transaction status
        if ($order) {
            if ($transaction == 'capture') {
                if ($fraud == 'challenge') {
                    // handle captured challenge
                }
                else if ($fraud == 'accept') {
                    // handle captured accept
                }
                // $order->status = 'processing';
                $order->update(['status' => 'processing']);
            } else if ($transaction == 'settlement') {
                $order->update(['status' => 'processing']);
            } else if ($transaction == 'cancel' || $transaction == 'deny' || $transaction == 'expire') {
                $order->update(['status' => 'cancelled']);
            } else if ($transaction == 'pending') {
                $order->update(['status' => 'pending']);
            }
            $order->save();
            $payment = $order->payment;
            $payment->payment_method = $paymentMethod;
            $payment->save();
        }
        
        return response()->json(['message' => 'Payment notification received']);

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