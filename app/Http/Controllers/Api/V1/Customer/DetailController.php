<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/1/18
 * Time: 下午5:10
 */

namespace App\Http\Controllers\Api\V1\Customer;


use App\Http\Controllers\Api\BaseController;

class DetailController extends BaseController
{
    public function show($userId,$detailId)
    {
        return compact('userId','detailId');
    }
}