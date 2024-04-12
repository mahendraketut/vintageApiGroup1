<?php

namespace app\Rules;

use Closure;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CheckProductQuantity implements Rule
{

    private $productId;

    public function __construct($productId)
    {
        $this->productId = $productId;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function passes($attribute, $value)
    {
        return (int) $value <= DB::table('products')
                ->where('id', $this->productId)
                ->value('quantity');
    }

    public function message()
    {
        return "The quantity added to cart cannot be greater than the product's available quantity.";
    }
}