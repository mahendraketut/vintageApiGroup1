<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Wishlist;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Sanctum;
use Spatie\FlareClient\Http\Exceptions\NotFound;

class WishlistController extends Controller
{

    use ApiResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth('sanctum')->user();

        // $wishlist_query = Wishlist::with('user');
        $wishlists = Wishlist::where('user_id', $user->id)->get();

        $wishlists->loadMissing('Product');

        // dd($wishlists);

        if (!$wishlists) {
            return $this->notFoundResponse();
        }

        return $this->successResponse($wishlists, 'Wishlists retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth('sanctum')->user();

        $wishlist = Wishlist::create([
            'product_id' => $request->product_id,
            'user_id' => $user->id
        ]);

        if (!$wishlist) {
            return $this->serverErrorResponse();
        }

        return $this->createdResponse($wishlist->product);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $wishlistProduct = Wishlist::find($id)->with('product');

        if (!$wishlistProduct) {
            return $this->notFoundResponse();
        }

        return $this->successResponse($wishlistProduct);
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
        $wishlist = Wishlist::findOrFail($id);

        $wishlist->delete();

        return $this->deleteResponse($wishlist->product);
    }
}
