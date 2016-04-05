<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/4/5
 * Time: 下午9:49
 */

namespace App\Models\Company\Project\Interview;


use App\Models\BaseModel;

class Interview extends BaseModel
{
    protected $table = 'interview';

    public function investor()
    {
        return $this->belongsTo('App\Models\Customer','investor_id')->with('investorCompanyName');
    }
}