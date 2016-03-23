<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/3/23
 * Time: 下午1:40
 * 用户投资记录表
 */

namespace App\Models\Invest;


use App\Models\BaseModel;

class InvestProject extends BaseModel
{
    protected $table = 'invest_project';

    //查询关联用户
    public function customer()
    {

    }
}