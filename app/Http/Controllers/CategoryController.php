<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Traits\ApiResponseTrait;

class CategoryController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $category_query = Category::with('products');

        if ($request->keyword) {
            $category_query->where('name', 'LIKE', '%' . $request->keyword . '%');
        }

        // sorted by
        if ($request->sortBy && in_array($request->sortBy, ['id', 'name'])) {
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

        $categories = $category_query->orderBy($sortBy, $sortOrder)->paginate(3);

        return $this->showResponse($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = Category::with('products');
        $category = $category->find($id);

        return response()->json(['category' => $category]);
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
