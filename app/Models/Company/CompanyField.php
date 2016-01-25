<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/1/21
 * Time: 下午3:28
 */

namespace App\Models\Company;


use App\Models\BaseModel;

class CompanyField extends BaseModel
{
    protected $table = 'company_field';
    public function company()
    {
        return $this->hasMany('App\Models\Company\Company');
    }
}