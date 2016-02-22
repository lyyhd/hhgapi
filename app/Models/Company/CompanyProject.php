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

    //获取项目领域
    public function field()
    {
        return $this->belongsToMany('App\Models\Company\CompanyProjectFieldConfig','company_project_field','project_id','field_id')->select('name');
    }
    //项目详情
    public function detail()
    {
        return $this->hasOne('App\Models\Company\CompanyProjectDetail');
    }
}