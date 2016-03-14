<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/2/27
 * Time: 下午11:38
 */

namespace App\Models\Invest;


use App\Models\BaseModel;

class InvestExperience extends BaseModel
{
    protected $table = 'invest_experience';

    //获取投资轮次
    public function round()
    {
        return $this->hasOne('App\Models\Invest\InvestRoundConfig','id','round_id');
    }
    //获取公司信息
    public function company()
    {
        return $this->hasOne('App\Models\Company\Company','id','company_id');
    }
}