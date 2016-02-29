<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/2/3
 * Time: 上午1:12
 */

namespace App\Models\Activity;


use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends BaseModel
{
    use SoftDeletes;

    public function customer_attention()
    {
        return $this->belongsToMany('App\Models\Customer','activity_customer_attention');
    }
    public function customer_collect()
    {
        return $this->belongsToMany('App\Models\Customer','activity_customer_collect');
    }
    public function customer()
    {
        return $this->belongsTo('App\Models\Customer');
    }
}