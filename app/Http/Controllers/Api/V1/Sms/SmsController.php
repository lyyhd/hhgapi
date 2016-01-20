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
        ]);
        //验证传入字段是否存在错误
        if($validator->fails()) return return_message('false', $validator->errors()->all());
        //发送验证码短信
        $verfiy_code        = (string)$this->generate_code(4);
        //设置短信模板id
        $tem_id             = '2289011';
        //设置短信过期时间
        $deadline_time      = 60;
        //TODO模板参数
        $token = $this->ttpassv2($this->request->get('mobile').'verify',time());
        return $this->sendMessage($this->request->get('mobile'),$token,$tem_id,$deadline_time,array($verfiy_code));
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

    //发送短信验证码
    /**
     * @param $phone
     * @param $temId 短信模板id
     * @param array $data 短信参数
     */
    private function sendMessage($mobile,$token = null,$deadline_time = 60,$temId,$data = array())
    {
        $code = $data[0];
        $result = 's';
        //设置过期时间
        $deadline_time += time();
        \SmsManager::storeSentInfo($token,compact('mobile','code','deadline_time'));
        return compact('token','code','result');
    }
}