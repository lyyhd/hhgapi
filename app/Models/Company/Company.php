<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/1/21
 * Time: 下午2:43
 */

namespace App\Models\Company;


use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends BaseModel
{
    use SoftDeletes;
    protected $table = 'company';

    protected $dates = ['deleted_at'];

    // 可填充的字段
    protected $fillable = ['contract','address','finance_status','website','email'];

    public function field()
    {
        return $this->belongsTo('App\Models\Company\CompanyField');
    }

    public function address()
    {
        return $this->hasOne('App\Models\Company\CompanyAddress');
    }

    public function customer()
    {
        return $this->hasMany('App\Models\Customer');
    }


    public function finance()
    {
        return $this->belongsTo('App\Models\Company\CompanyFinance','finance_status','status');
    }

    public function scopeStatus($query)
    {
        return $query->where('status',1);
    }
    //按照领域搜索
    public function scopeSearchField($query,$field = false)
    {
        return $field ? $query->where('field_id',$field) : null;
    }
    //按照融资状态进行搜索
    public function scopeSearchFinance($query,$finance = false)
    {
        return $finance ? $query->where('finance_status',$finance) : null;
    }
    //按照用户搜索
    public function scopeSearchCustomer($query,$customer = false)
    {
        return $customer ? $query->where('customer_id',$customer) : null;
    }
}