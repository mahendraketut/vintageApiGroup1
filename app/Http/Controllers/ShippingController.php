<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;

use App\Models\Shipping;
use App\Http\Requests\StoreShippingRequest;
use App\Http\Requests\UpdateShippingRequest;
use Illuminate\Support\Env;

class ShippingController extends Controller
{
    public function createTrackingNumber(Shipping $shipping) {
        // generate tracking number\
        $tracking_number = 'TRK' . time() . rand(001, 999);

        $shipping->tracking_number = $tracking_number;
        $shipping->save();

        $order = $shipping->order;
        $order->status = 'shipping';
        $order->save();

        return response()->json([
            'message' => "order status updated to Shipping",
            $shipping
        ]);
    }
}