<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/3/15
 * Time: 下午2:14
 */

namespace App\Models\Activity;


use App\Models\BaseModel;

class Apply extends BaseModel
{
    protected $table = 'activity_customer_apply';

    public function isCustomerApply($customer_id,$activity_id)
    {
        return Apply::where('customer_id',$customer_id)
            ->where('activity_id',$activity_id)
            ->first();
    }
}