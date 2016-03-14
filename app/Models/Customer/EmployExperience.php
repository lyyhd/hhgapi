<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/2/29
 * Time: 上午1:40
 */

namespace App\Models\Customer;


use App\Models\BaseModel;

class EmployExperience extends BaseModel
{
    protected $table = 'employment_experience';

    public function company()
    {
        return $this->hasOne('App\Models\Company\Company','id','company_id');
    }
}