<?php

namespace app\Rules;

use Closure;
use Illuminate\Contracts\Validation\Rule;

class NoNegativeValue implements Rule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function passes($attribute, $value)
    {
        return $value >= 0;
    }

    public function message()
    {
        return "The :attribute can't be a negative value";
    }
}
