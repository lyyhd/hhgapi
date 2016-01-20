<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/1/16
 * Time: 下午4:43
 */

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    // 接口帮助调用
    use Helpers;

    // 请求
    protected $request;

    // 返回错误的请求
    protected function errorBadRequest($message='')
    {
        return $this->response->array($message)->setStatusCode(400);
    }

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

}