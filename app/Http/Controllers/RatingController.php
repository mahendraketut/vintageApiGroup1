<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Http\Requests\StoreRatingRequest;
use App\Http\Requests\UpdateRatingRequest;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    use ApiResponseTrait;
    /**
     * Display a listing of the resource.
     * if there is product id in the request, return ratings for that product only otherwise returns all ratings.
     *
     * @param \Illuminate\Http\Request  $request
     */
    public function index()
    {
        $ratings = Rating::all();

        return $this->successResponse($ratings, 'Ratings retrieved successfully');
    }

    /**
     * Get rating for specific product.
     */
    public function getRating($productId)
    {
        $ratings = Rating::where('product_id', $productId)->get();

        return $this->successResponse($ratings, 'Ratings retrieved successfully');
    }

    /**
     * Return the average rating for the product. with total number of ratings.
     *
     * @param Rating $productId
     */
    public function averageRating($productId)
    {
        $ratings = Rating::where('product_id', $productId)->get();
        $totalRatings = $ratings->count();
        $averageRating = $ratings->avg('rating');

        return $this->successResponse([
            'average_rating' => $averageRating,
            'total_ratings' => $totalRatings,
        ], 'Average rating retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRatingRequest $request)
    {
        $rating = Rating::create(
            [
                'rating' => $request->rating,
                'comment' => $request->comment,
                'user_id' => Auth::user()->id,
                'product_id' => $request->product_id,
            ]
        );

        return $this->successResponse($rating, 'Rating created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Rating $rating)
    {
        $rating = Rating::findOrFail($rating->id);
        return $this->successResponse($rating, 'Rating retrieved successfully');
    }
}
