<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/1/20
 * Time: 下午3:14
 */

namespace App\Http\Controllers\Api\V1\Image;


use App\Http\Controllers\Api\BaseController;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Intervention\Image\Exception\RuntimeException;
use Qiniu\Auth;

class ImageController extends BaseController
{
    protected $avatar_path;
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->avatar_path = 'uploads/avatars/';
    }

    //图片上传
    /**
     *
     */
    public function upload()
    {
        try {
            $file = $this->request->file('image');
            $fileName = $file->getClientOriginalName();
            \Image::make($file)
                ->save('uploads/images/'.$fileName);
            //上传图片至七牛
            $this->disk->put($fileName,fopen('uploads/images/'.$fileName, 'r'));
            //
            $imgUrl = $this->disk->downloadUrl($fileName);
            return return_message(true,$imgUrl);
        }catch (\Exception $e){
            //图片上传失败
            return return_message(false,$e->getMessage());
        }
    }

    //无token上传图片
    public function avatarUpload()
    {
        try {
            $file = $this->request->file('image');
            $fileName = $file->getClientOriginalName();
            \Image::make($file)
                ->save('uploads/avatars/'.$fileName);
            //上传图片至七牛
            $this->disk->put($fileName,fopen('uploads/avatars/'.$fileName, 'r'));
            //
            $imgUrl = $this->disk->downloadUrl($fileName);
            return return_message(true,$imgUrl);
        }catch (\Exception $e){
            //图片上传失败
            return return_message(false,$e->getMessage());
        }
    }

    //头像上传
    public function avatar()
    {
        //验证数据
        $validator = \Validator::make($this->request->all(),[
            'avatar'    => 'required'
        ],[
            'avatar.required'   => '图片没有上传'
        ]);

        if($validator->fails()) return $this->errorBadRequest($validator->messages());

        //TODO 获取手机号码
        $customer = $this->user();
        try {
            $file = $this->request->file('avatar');
            $fileName = $this->avatar_path.$customer->mobile.'.'.$file->getClientOriginalExtension();
            $qnName = $customer->mobile.'.'.$file->getClientOriginalExtension();
            \Image::make($file)->save($fileName);
            //上传图片至七牛
            if($this->disk->put($qnName,fopen($fileName, 'r'))){
                //获取图片地址
                $imgUrl = $this->disk->downloadUrl($qnName);
//                //更新七牛缓存
//                $auth = new Auth(config('filesystems.disks.qiniu.access_key'),config('filesystems.disks.qiniu.secret_key'));
//                $url = "http://fusion.qiniuapi.com/refresh";
//                $token = $auth->signRequest($url, null);
//                $client = new Client();
//                $urls = array('urls' => array($imgUrl));
//                $result = $client->request('POST',$url,[
//                    'headers' => [
//                        'Content-Type' => 'application/json',
//                        'Authorization'     => "QBox $token",
//                    ],
//                    'body' => json_encode($urls)
//                ]);
                //添加进入数据库
                $customer->avatar = $imgUrl;
                $customer->save();
                return return_rest('1',compact('imgUrl'),'头像上传成功');
            }
            return return_rest('0','','头像上传失败');
        }catch (\Exception $e){
            //图片上传失败
            return return_rest('0','',$e->getMessage());
        }
    }


    public function uploadImage($file,$path,$fileName,$height = 300,$width = 300)
    {
        \Image::make($file)->resize($width,$height)->save($path.$fileName);
    }

}