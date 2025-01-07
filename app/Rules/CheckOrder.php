<?php

namespace App\Rules;

use App\Models\Order;
use App\Models\OrderProduct;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckOrder implements ValidationRule
{
    private $order_id;
    private $orderProducts;
    private $orderProduct_id;

    public function __construct($order_id, $orderProducts, $orderProduct_id)
    {
        $this->order_id = $order_id;
        $this->orderProducts = $orderProducts;
        $this->orderProduct_id = $orderProduct_id;
    }

    /**
     * Run the validation rule.
     *
     * @param \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $order = Order::find($this->order_id);
        if (!$order) {
            $fail("Order with id {$this->order_id} not found");
            return;
        }
//        $orderProducts = Order::where('id',$this->order_id)->orderProducts;
        $bool = false;


        $bool = false;
        foreach ($this->orderProducts as $array) {
            if ($array->product_id == $this->orderProduct_id) {
                $bool = true;
            }
        }
        if (!$bool) {
            $fail("Order products with id {$this->orderProduct_id}  not found");

        }


    }
}
