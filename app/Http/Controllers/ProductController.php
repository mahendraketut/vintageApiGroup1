<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Traits\HandlesImageUploads;
use App\Traits\ApiResponseTrait;

class ProductController extends Controller
{
    use HandlesImageUploads;
    use ApiResponseTrait;
    /**
     * Display a listing of the resource.
     */
    public function __invoke(Request $request)
    {
        $user = auth('sanctum')->user();
        $product_query = Product::with(['category', 'brand', 'user']);

        if ($user) {
            $product_query->where('user_id', $user->id);
        }

        // search by product name keyword
        if ($request->keyword) {
            $product_query->where('name', 'LIKE', '%' . $request->keyword . '%');
        }

        // search by price range
        if ($request->min_price && $request->max_price) {
            $minPrice = $request->get('min_price', null);
            $maxPrice = $request->get('max_price', null);

            $product_query->where(function ($query) use ($minPrice, $maxPrice) {
                if (!is_null($minPrice)) {
                    $query->where('price', '>=', $minPrice);
                }
                if (!is_null($maxPrice)) {
                    $query->where('price', '<=', $maxPrice);
                }
            });
        }

        // filter by category
        if ($request->category) {
            $product_query->whereHas('category', function ($query) use ($request) {
                $query->where('name', $request->category);
            });
        }

        // filter by brand
        if ($request->brand) {
            $product_query->whereHas('brand', function ($query) use ($request) {
                $query->where('name', $request->brand);
            });
        }

        // sort by
        if ($request->sortBy && in_array($request->sortBy, ['id', 'name', 'price'])) {
            $sortBy = $request->sortBy;
        } else {
            $sortBy = 'id';
        }

        // sort order
        if ($request->sortOrder && in_array($request->sortOrder, ['asc', 'desc'])) {
            $sortOrder = $request->sortOrder;
        } else {
            $sortOrder = 'asc';
        }

        $products = $product_query->orderBy($sortBy, $sortOrder)->get();

        return $this->showResponse($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductStoreRequest $request)
    {
        $validated = $request->validated();
        unset($validated['images']);

        $product = Product::create($validated);

        if ($request->hasFile('images')) {
            $imageNames = $this->storeImage($request->file('images'), 'public/images');

            if (is_array($imageNames)) {
                foreach ($imageNames as $imageName) {
                    $product->image()->create([], ['url' => $imageName]);
                }
            }
        }

        if ($product) {
            return $this->successResponse($product, 'Product added successfully');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::find($id);

        return $this->showResponse($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductUpdateRequest $request, string $id)
    {
        $user = auth('sanctum')->user();
        $validated = $request->validated();

        unset($validated['images']);
        $validated['user_id'] = $user->id;

        $product = Product::find($id);
        if (!$product) {
            return $this->notFoundResponse();
        }

        if ($request->hasFile('images')) {
            if ($product->image) {
                $imageNames = $this->storeImage($request->file('images'), 'public/images');
                
                if (is_array($imageNames)) {
                    foreach ($imageNames as $imageName) {
                        $product->image()->updateOrCreate([], ['url' => $imageName]);
                    }
                } else {
                    $product->image()->updateOrCreate([], ['url' => $imageNames]);
                }
            }
        }

        if ($product) {
            return $this->updateResponse($product);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        $product = Product::find($id);

        if (!$product) {
            return $this->notFoundResponse();
        }

        $product->delete();

        return $this->deleteResponse($product);
    }

    public function restore(string $id)
    {
        $product = Product::withTrashed()->find($id);

        if (!$product) {
            return $this->notFoundResponse();
        }

        $product->restore();

        return $this->restoreResponse($product);
    }
}
