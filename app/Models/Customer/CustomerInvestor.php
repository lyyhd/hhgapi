<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/3/23
 * Time: 下午2:53
 */

namespace App\Models\Customer;


use App\Models\BaseModel;

class CustomerInvestor extends BaseModel
{
    protected $table = 'customer_investor';

    protected $fillable = ['currency','amount','finance_round'];
}