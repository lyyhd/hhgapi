<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/1/19
 * Time: 下午3:10
 * 短信通知发送接口
 */

namespace App\Http\Controllers\Api\V1\Sms;


use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpSms;
class SmsController extends BaseController
{
    protected $smsTemplate;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        //设置短信通信发送为云通讯
        $this->smsTemplate = 'YunTongXun';
        //关闭队列
        PhpSms::queue(false);
    }

    /**
     * @return array
     * 注册时发送短信验证码
     * post
     * mobile手机号码
     * code 验证码
     * deadLineTime过期时间
     * data[]参数集合
     *
     */
    public function verify()
    {
        $validator = Validator::make($this->request->all(),[
            'mobile'        => 'required|unique:customers',
        ],[
            'mobile.unique' => '手机号码已经注册账户',
            'mobile.required' => '手机号码未提供',
        ]);
        $mobiles = $validator->messages()->get('mobile');
        foreach($mobiles as $mobile){
            if($mobile == '手机号码已经注册账户') return return_rest('0',array('smsToken'=>"",'code'=>""),'手机号码已经注册账户');
            if($mobile == '手机号码未提供') return return_rest('0',array('smsToken'=>array(),'code'=>array()),'手机号码未提供');
        }
        //发送验证码短信
        $verfiy_code        = (string)$this->generate_code(4);
        //设置短信模板id
        $tem_id             = '22891';
        //设置短信过期时间
        $deadline_time      = 180;
        //TODO模板参数
        $token = $this->ttpassv2($this->request->get('mobile').'verify',time());
        $result = $this->sendTokenMessage($this->request->get('mobile'),$token,$tem_id,$deadline_time,array($verfiy_code));

        return return_rest('1',$result);
    }

    /**
     * @return array
     * 找回密码发送短信
     * post
     * mobile手机号码
     * code 验证码
     * deadLineTime过期时间
     * data[]参数集合
     *
     */
    public function forget()
    {
        $validator = Validator::make($this->request->all(),[
            'mobile'        => 'required|exists:customers',
        ],[
            'mobile.exists' => '手机号码未注册账户',
            'mobile.required' => '请输入手机号码',
        ]);
        //验证传入字段是否存在错误
        if($validator->messages()->has('mobile')){
            if($validator->messages()->get('mobile')[0] === '手机号码未注册账户' ) return return_rest('0','','手机号码未注册');
        }
        //发送验证码短信
        $verfiy_code        = (string)$this->generate_code(4);
        //设置短信模板id
        $tem_id             = '22890';
        //设置短信过期时间
        $deadline_time      = 60;
        //TODO模板参数
        $token = $this->ttpassv2($this->request->get('mobile').'verify',time());
        $result =  $this->sendTokenMessage($this->request->get('mobile'),$token,$tem_id,$deadline_time,array($verfiy_code));

        return return_rest('1',$result);
    }
    /**
     * 更换手机发送验证码
     */
    public function changeMobile()
    {
        $validator = Validator::make($this->request->all(),[
            'mobile'        => 'required|exists:customers',
        ],[
            'mobile.exists' => '手机号码未注册账户',
            'mobile.required' => '请输入手机号码',
        ]);
        //验证传入字段是否存在错误
        if($validator->messages()->has('mobile')){
            if($validator->messages()->get('mobile')[0] === '手机号码未注册账户' ) return return_rest('0','','手机号码未注册');
        }
        //发送验证码短信
        $verfiy_code        = (string)$this->generate_code(4);
        //设置短信模板id
        $tem_id             = '22890';
        //设置短信过期时间
        $deadline_time      = 60;
        //TODO模板参数
        $token = $this->ttpassv2($this->request->get('mobile').'verify',time());
        $result =  $this->sendTokenMessage($this->request->get('mobile'),$token,$tem_id,$deadline_time,array($verfiy_code));

        return return_rest('1',$result);
    }

    /**
     * 验证验证码
     */
    public function checkVerify()
    {
        $token = $this->request->get('smsToken');
        $validator = \Validator::make($this->request->all(), [
            'mobile'    => "required|confirm_mobile_not_change:$token",
            'verifyCode'    => "required|verify_code:$token|confirm_mobile_rule:mobile_required,$token"
        ], [
            'verifyCode.required' => '请输入短信验证码',
            'verify_code'   => '验证码错误',
            'confirm_mobile_not_change' => '当前手机号码与发送号码不符',
            'confirm_mobile_rule' => '验证码验证错误',
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
        return return_rest('1','','验证成功');
    }

    //生成随机验证码
    private function generate_code($length = 6) {
        return rand(pow(10,($length-1)), pow(10,$length)-1);
    }
    /**
     *  生成一个随机Token
     *  string   字符串
     *  salt     撒盐
     */

    private function ttpassv2($string, $salt = '')
    {
        return substr(md5(md5($string) . 'key' . $salt), 0, 30);
    }


    /**
     * //发送短信验证码
     * @param $phone
     * @param $temId 短信模板id
     * @param array $data 短信参数
     */
    private function sendTokenMessage($mobile,$smsToken = null,$temId,$deadline_time = 60,$data = array())
    {
        $code = $data[0];
        $result = PhpSms::make()->to($mobile)->template($this->smsTemplate, $temId)->data($data)->send();
        //$result = 'true';
        //设置过期时间
        $deadline_time += time();
        \SmsManager::storeSentInfo($smsToken,compact('mobile','code','deadline_time'));
//        $code = '1314';
        return compact('smsToken','code');
    }
}