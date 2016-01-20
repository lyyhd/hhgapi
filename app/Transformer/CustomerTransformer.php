<?php
namespace App\Transformer;
use App\Models\Customer;
use League\Fractal\TransformerAbstract;

class CustomerTransformer extends TransformerAbstract
{
    public function transform(Customer $customer)
    {
        return $customer->toArray();
    }
}
