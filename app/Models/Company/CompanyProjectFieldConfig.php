<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/2/22
 * Time: 下午1:32
 */

namespace App\Models\Company;


use App\Models\BaseModel;

class CompanyProjectFieldConfig extends BaseModel
{
    protected $table = 'company_project_field_config';

    //获取subField
    public function subField()
    {
        return $this->hasMany('App\Models\Company\CompanyProjectFieldConfig','parent_id');
    }
}