<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Product;

class StockAvailable implements ValidationRule
{
    protected $productId;

    /**
     * Constructor to initialize the product ID.
     *
     * @param int $productId
     */
    public function __construct( $productId)
    {
        $this->productId = $productId; // استلام معرّف المنتج فقط
    }

    /**
     * Run the validation rule.
     *
     * @param string $attribute
     * @param mixed $value
     * @param \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // جلب المنتج من قاعدة البيانات باستخدام معرّف المنتج
        if (!$this->productId) {
        $fail('Product id is required.');
    }
        $product = Product::find($this->productId);

        if (!$product) {
            $fail('The selected product does not exist.');
            return;
        }

        // التحقق من أن الكمية المطلوبة أقل من أو تساوي الكمية المتوفرة
        if ($value > $product->quantity) {
            $fail("The requested quantity exceeds the available stock of {$product->quantity}.");
        }
    }
}
