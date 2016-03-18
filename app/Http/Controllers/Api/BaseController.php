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
use zgldh\QiniuStorage\QiniuStorage;

class BaseController extends Controller
{
    // 接口帮助调用
    use Helpers;

    // 请求
    protected $request;

    //七牛
    protected $disk;

    // 返回错误的请求
    protected function errorBadRequest($message='')
    {
        return $this->response->array($message)->setStatusCode(400);
    }

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->disk = QiniuStorage::disk('qiniu');
    }

    /**
     * 图片上传至七牛
     */
    public function qiniuUpload($filename,$path = '')
    {
        $this->disk->put($filename,fopen(storage_path($path).'/'.$filename, 'r'));
        $filenameUrl = $this->disk->downloadUrl($filename);
        return $filenameUrl;
    }

}