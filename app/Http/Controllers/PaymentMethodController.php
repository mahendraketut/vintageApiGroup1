<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;

class PaymentMethodController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payment_methods = PaymentMethod::all();

        return $this->showResponse($payment_methods);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $method = PaymentMethod::create(['name' => $request->name]);

        if (!$method) {
            return $this->serverErrorResponse();
        }

        return $this->createdResponse($method);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
