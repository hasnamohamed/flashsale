<?php

namespace App\Rules;

use App\Models\Product;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StockAvailable implements ValidationRule
{
    protected $qty;

    public function __construct($qty)
    {
        $this->qty = $qty;
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $product = Product::find($value);

        if (!$product || $product->available() < $this->qty) {
            $fail('Not enough stock available for this product.');
        }
    }
}
