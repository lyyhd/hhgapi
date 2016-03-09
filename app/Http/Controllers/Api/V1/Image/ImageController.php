<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/1/20
 * Time: 下午3:14
 */

namespace App\Http\Controllers\Api\V1\Image;


use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use Intervention\Image\Exception\RuntimeException;

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
        //获取图片名称
        $fileName = md5(uniqid(str_random(10)));
        //
        try {
            \Image::make($this->request->file('image'))
                ->resize(300,300)
                ->save('uploads/images/'.$fileName.'.jpg');
            return return_message(true,'uploads/images/'.$fileName.'.jpg');
        }catch (\Exception $e){
            //图片上传失败
            return return_message(false,$e->getMessage());
        }
    }

    public function avatarUpload()
    {
        //获取图片名称
        $fileName = $this->request->file('image')->getClientOriginalName();
        //
        try {
            \Image::make($this->request->file('image'))
                ->resize(300,300)
                ->save('uploads/avatars/'.$fileName.'.jpg');
            return return_message(true,'uploads/avatars/'.$fileName.'.jpg');
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
        $fileName = $customer->mobile;
        $file = $this->avatar_path.$fileName.'.jpg';
        try {
            \Image::make($this->request->file('avatar'))->resize(300,300)->save($file);
            //添加进入数据库
            $customer->avatar = $file;
            $customer->save();
            return return_rest('1',compact('file'),'头像上传成功');
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