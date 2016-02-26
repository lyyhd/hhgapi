<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/1/16
 * Time: 下午4:46
 */

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Api\BaseController;
use App\Models\Customer;
use Goodspb\LaravelEasemob\Facades\Easemob;
use Intervention\Image\Facades\Image;
use Mockery\CountValidator\Exception;


class AuthController extends BaseController
{
    public function login()
    {
        $validator = \Validator::make($this->request->all(), [
            'mobile'    => 'required|exists:customers',
            'password' => 'required',
        ]);

        $credentials = $this->request->only('mobile', 'password');

        if ( ! $token = \JWTAuth::attempt($credentials)) {
//            $validator->after(function ($validator) {
//                //$validator->errors()->add('error_msg', '用户名或密码错误');
//                return $this->errorBadRequest(return_rest('0','','用户名或密码错误','10021'));
//            });
            return return_rest('0','','用户名或密码错误');
        }

        if ($validator->fails()) {
            //return $this->errorBadRequest($validator->messages());
            $messages = $validator->messages();
            $mobiles = $messages->get('mobile');
            foreach($mobiles as $mobile){
                if($mobile == 'The selected mobile is invalid.') return $this->errorBadRequest(return_rest('0','','手机号码未注册'));
            }
            return return_rest('0','','请按照规则输入手机号码');
        }
        //登录成功 获取用户信息
        $customer = Customer::select('type','name','nickname')->where('mobile',$this->request->get('mobile'))->first();
        return return_rest('1',compact('token','customer'),'登陆成功');
    }

    public function refreshToken()
    {
        $newToken = \JWTAuth::parseToken()->refresh();
        return $this->response->array(['token' => $newToken]);
    }

    public function logout()
    {
        //获取当前用户token
        $token = \JWTAuth::getToken();
        //让token失效
        try{
            \JWTAuth::invalidate($token);
            return return_rest('1','','已退出登录');
        }catch (\Exception $e){
            return return_rest('0','',$e->getMessage());
        }
    }

    /**
     * 昵称 type
     */
    public function signup()
    {
        $token = $this->request->get('smsToken');
        $validator = \Validator::make($this->request->all(), [
            'mobile'    => "required|confirm_mobile_not_change:$token",
            'password'     => 'required',
            'verifyCode'    => "required|verify_code:$token|confirm_mobile_rule:mobile_required,$token"
        ], [
            'verifyCode.required' => '请输入短信验证码',
            'verify_code'   => '验证码错误',
            'confirm_mobile_not_change' => '当前手机号码与发送号码不符',
            'confirm_mobile_rule' => '验证码验证错误'
        ]);
        $messages = $validator->messages();
        if($messages->has('mobile')){
            $mobiles_rule = $messages->get('mobile');
            foreach($mobiles_rule as $mobile_rule){
                if($mobile_rule == '当前手机号码与发送号码不符') return return_rest('0','','当前手机号码与发送号码不符');
            }
        }
        if($messages->has('verifyCode')){
            $verifyCodes = $messages->get('verifyCode');
            foreach($verifyCodes as $verifyCode){
                if($verifyCode == '请输入短信验证码') return return_rest('0','','请输入短信验证码');
                if($verifyCode == '验证码错误') return return_rest('0','','验证码错误');
                if($verifyCode == '验证码验证错误') return return_rest('0','','验证码验证错误');
            }
        }
        if($messages->has('password'))
        {
            return return_rest('0','','请输入密码');
        }
        //增加环信注册 失败返回false
        $easemob = Easemob::user_register($this->request->get('mobile'),$this->request->get('password'));
        //TODO
        if(isset($easemob['mobile'])) return return_rest('0','','该用户已注册环信');
        //设置用户相关信息
        $mobile     = $this->request->get('mobile');
        $password   = $this->request->get('password');
        //TODO用户类型 设置默认为3游客 1为创业者2为投资人
        $type       = $this->request->has('type') ? $this->request->has('type') : 3;
        //TODO 其他信息
        $customer = new Customer;
        $customer->mobile   = $mobile;
        $customer->password = bcrypt($password);
        $customer->type     = $type;
        $customer->avatar     = 'uploads/avatars/'.$mobile.'.jpg';
        if($customer->save()){
            // 用户注册事件
            $token = \JWTAuth::fromUser($customer);
            //为用户生成头像
            $img = Image::make('uploads/avatars/avatar.png');
            $img->save('uploads/avatars/'.$mobile.'.jpg');
            return return_rest('1',array('token'=>$token));
        }

        $this->errorBadRequest(return_rest('0','','操作失败'));
    }

}