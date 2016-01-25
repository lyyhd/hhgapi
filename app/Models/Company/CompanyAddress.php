<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/1/21
 * Time: 下午4:18
 */

namespace App\Models\Company;


use App\Models\BaseModel;

class CompanyAddress extends BaseModel
{
    protected $table = 'company_address';

    public function company()
    {
        return $this->belongsTo('App\Models\Company\Company');
    }
}