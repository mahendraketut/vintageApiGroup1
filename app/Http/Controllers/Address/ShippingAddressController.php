<?php

namespace App\Http\Controllers\Address;

use App\Http\Controllers\Controller;

use App\Models\ShippingAddress;
use App\Http\Requests\StoreShippingAddressRequest;
use App\Http\Requests\UpdateShippingAddressRequest;
use App\Traits\ApiResponseTrait;

class ShippingAddressController extends Controller
{
    use ApiResponseTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get all the shipping addresses
        $shippingAddresses = ShippingAddress::where('user_id', auth()->id())->get();

        // Return the shipping addresses with response code 200 - OK
        return $this->successResponse($shippingAddresses, 'Shipping addresses retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreShippingAddressRequest $request)
    {
        // Create a new shipping address
        $shippingAddress = ShippingAddress::create([
            'user_id' => auth()->id(),
            'full_name' => $request->full_name,
            'phone' => $request->phone,
            'address' => $request->address,
            'district' => $request->district,
            'city' => $request->city,
            'province' => $request->province,
            'country' => $request->country,
            'zip_code' => $request->zip_code,
        ]);

        // Return the shipping address with response code 201 - Created
        return $this->successResponse($shippingAddress, 'Shipping address created', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Find the shipping address
        $shippingAddress = ShippingAddress::findOrFail($id);

        // Return the shipping address with response code 200 - OK if found, or send a 404 - Not Found response
        if ($shippingAddress == null) {
            return $this->notFoundResponse('Shipping address not found');
        } elseif (auth()->id() != $shippingAddress->user_id) {
            return $this->unauthorizedResponse('You are not authorized to view this shipping address');
        } else {
            return $this->successResponse($shippingAddress, 'Shipping address retrieved successfully');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateShippingAddressRequest $request, $id)
    {
        // Find the shipping address
        $shippingAddress = ShippingAddress::find($id);

        // Update the shipping address details
        $shippingAddress->update([
            'full_name' => $request->full_name,
            'phone' => $request->phone,
            'address' => $request->address,
            'district' => $request->district,
            'city' => $request->city,
            'province' => $request->province,
            'country' => $request->country,
            'zip_code' => $request->zip_code,
        ]);

        // Return the updated shipping address with response code 200 - OK
        return $this->successResponse($shippingAddress, 'Shipping address updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Find the shipping address
        $shippingAddress = ShippingAddress::find($id);

        // Delete the shipping address
        $shippingAddress->delete();

        // Return a success response with response code 200 - OK
        return $this->successResponse(null, 'Shipping address deleted successfully');
    }

    /**
     * See all deleted shipping addresses.
     */
    public function trash()
    {
        // Get all the deleted shipping addresses
        $shippingAddresses = ShippingAddress::onlyTrashed()->where('user_id', auth()->id())->get();

        // Return the deleted shipping addresses with response code 200 - OK
        return $this->successResponse($shippingAddresses, 'Shipping addresses in trash retrieved successfully');
    }

    /**
     * Restore a deleted shipping address.
     */
    public function restore($id)
    {
        // Find the deleted shipping address
        $shippingAddress = ShippingAddress::onlyTrashed()->find($id);

        // Restore the shipping address
        $shippingAddress->restore();

        // Return a success response with response code 200 - OK
        return $this->successResponse($shippingAddress, 'Shipping address restored successfully');
    }
}
