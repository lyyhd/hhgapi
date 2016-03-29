<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/3/29
 * Time: 下午1:55
 */

namespace App\Models\Invest;


use App\Models\BaseModel;

class InvestCompany extends BaseModel
{
    protected $table = 'invest_company';
    protected $fillable = ['brief','website','weixin'];
}