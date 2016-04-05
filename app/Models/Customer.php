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
use Cmgmyr\Messenger\Traits\Messagable;

class Customer extends BaseModel implements AuthenticatableContract
{
    use SoftDeletes, Authenticatable, Messagable;

    // 查询用户的时候，不暴露密码
    protected $hidden = ['password'];

    // 可填充的字段
    protected $fillable = ['name','sex','email','nickname','brief','address','type'];

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
    //获取投资经历
    public function invest_experience()
    {
        return $this->hasMany('App\Models\Invest\InvestExperience')->with('round','company')->orderBy('created_at','desc');
    }
    //获取用户投资的项目
    public function invest_project()
    {
        return $this->belongsToMany('App\Models\Company\CompanyProject','invest_project','customer_id','project_id');
    }
    //获取投资人的公司
    public function investorCompany()
    {
        return $this->hasOne('App\Models\Invest\InvestCompany','id','company_id');
    }
    //获取投资人的公司名称
    public function investorCompanyName()
    {
        return $this->hasOne('App\Models\Invest\InvestCompany','id','company_id')->select('id','name');
    }
}