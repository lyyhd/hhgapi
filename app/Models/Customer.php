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
    protected $fillable = ['name'];

    public function getCustomerByMobile($mobile)
    {
        return $this->where('mobile',$mobile)->first();
    }

    public function company()
    {
        return $this->hasOne('App\Models\Company\Company')->with('finance');
    }
}