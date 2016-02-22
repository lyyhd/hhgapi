<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/1/18
 * Time: 下午3:07
 */

namespace App\Models;


use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class Customer extends BaseModel implements AuthenticatableContract
{
    use SoftDeletes, Authenticatable;

    // 查询用户的时候，不暴露密码
    protected $hidden = ['password'];

    // 可填充的字段
    protected $fillable = ['name','sex','email','nickname','brief'];

    public function getCustomerByMobile($mobile)
    {
        return $this->where('mobile',$mobile)->first();
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company\Company');
    }

    public function activity()
    {
        return $this
            ->belongsToMany('App\Models\Activity\Activity','activity_customer_attention','customer_id','activity_id')
            ->select('activities.id','title','brief','image','activities.created_at');
    }
}