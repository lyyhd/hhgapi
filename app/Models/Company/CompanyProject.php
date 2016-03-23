<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/2/21
 * Time: 下午10:46
 */

namespace App\Models\Company;


use App\Models\BaseModel;

class CompanyProject extends BaseModel
{
    protected $table = 'company_project';

    protected $fillable = ['name','brief','city'];

    protected $casts = [
      'get_out' => 'int'
    ];

    //获取项目领域
    public function field()
    {
        return $this->belongsToMany('App\Models\Company\CompanyProjectFieldConfig','company_project_field','project_id','field_id');
    }
    //项目详情
    public function detail()
    {
        return $this->hasOne('App\Models\Company\CompanyProjectDetail');
    }
    //公司介绍
    public function companyIntroduce()
    {
        //return
    }
    //公司信息
    //投资轮次
    public function finance()
    {
        return $this->hasOne('App\Models\Company\CompanyProjectFinance','project_id')->select('id','project_id','finance_id');
    }
    //获取投资人
    public function investor()
    {
        return $this->belongsToMany('App\Models\Customer','invest_project');
    }
    //获取当前投资轮次
    public function current_finance()
    {
        return $this->hasOne('App\Models\Company\CompanyProjectFinance','project_id')->select('id','project_id','finance_id')->orderBy('created_at','desc');
    }

}