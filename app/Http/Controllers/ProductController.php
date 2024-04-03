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
    use HandlesImageUploads, ApiResponseTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth('sanctum')->user();
        $product_query = Product::with(['category', 'brand', 'user', 'images']);

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
        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'condition' => $request->condition,
            'discount' => $request->discount,
            'category_id' => $request->category_id,
            'user_id' => auth('sanctum')->user()->id,
            'brand_id' => $request->brand_id,
            'size' => $request->size
        ]);


        // check if the user uploaded an image
        if ($request->hasFile('images')) {
            // store the image in the storage folder using Handle Image Uploads trait
            $imageNames = $this->storeImage($request->file('images'));
            // create a new image record in the database and associate it with the product
            if (count($imageNames) === 1) {
                $product->images()->create(['url' => $imageNames[0]]);
            } else {
                foreach ($imageNames as $imageName) {
                    $product->images()->create(['url' => $imageName]);
                }
            }
        }

        return $this->createdResponse(['product' => $product]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::with(['category', 'brand', 'user', 'images'])->findOrFail($id);

        return $this->showResponse($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductUpdateRequest $request, string $id)
    {
        $product = Product::findOrFail($id);

        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'condition' => $request->condition,
            'discount' => $request->discount,
            'category_id' => $request->category_id,
            'brand_id' => $request->brand_id,
            'size' => $request->size
        ]);

        // check if the user uploaded an image
        if ($request->hasFile('images')) {
            // store the image in the storage folder using Handle Image Uploads trait
            $imageNames = $this->updateImages($request->file('images'), 'public/images', $product->images->pluck('url')->toArray());
            // create a new image record in the database and associate it with the product
            if (count($imageNames) === 1) {
                $product->images()->create(['url' => $imageNames[0]]);
            } else {
                foreach ($imageNames as $imageName) {
                    $product->images()->create(['url' => $imageName]);
                }
            }
        }

        return $this->updateResponse(['product' => $product]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return $this->deleteResponse($product);
    }

    public function restore(string $id)
    {
        $product = Product::withTrashed()->findOrFail($id);

        $product->restore();

        return $this->restoreResponse($product);
    }
}
