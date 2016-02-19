<?php
namespace App\Http\Controllers\Api\V1\Customer;
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/1/18
 * Time: 下午3:12
 */

use \App\Http\Controllers\Api\BaseController;
use App\Models\Company\Company;
use App\Models\Customer;
use App\Transformer\CustomerTransformer;
use Goodspb\LaravelEasemob\Facades\Easemob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CustomerController extends BaseController
{
    protected $modelCustomer;
    public function __construct(Request $request,Customer $modelCustomer)
    {
        parent::__construct($request);
        $this->modelCustomer = $modelCustomer;
    }

    /**
     * @api {get} /customer 当前用户信息
     * @apiDescription 当前用户信息
     * @apiGroup user
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "data": {
     *         "id": 2,
     *         "email": 'liyu01989@gmail.com',
     *         "name": "foobar",
     *         "created_at": "2015-09-08 09:13:57",
     *         "updated_at": "2015-09-08 09:13:57",
     *         "deleted_at": null
     *       }
     *     }
     */
    public function show()
    {
        $user = $this->modelCustomer->select('id','name','mobile','avatar','nickname','brief','type','sex','email','address')
            ->withOnly('company',array('id','customer_id','name','website','finance_status','position','weixin','email'))
            ->find($this->user()->id);

//        return $this->response->item($user, new CustomerTransformer);
        $user = $user->toArray();
        if(is_null($user['company'])){
            $user['is_company'] = '0';
        }else{
            $user['is_company'] = '1';
        }
        $user['company'] = is_null($user['company']) ? "" : $user['company'];
        return return_rest('1',compact('user'),'获取成功');
    }
    /**
     * 根据手机号码获取用户信息
     */
    public function detailByMobile()
    {
        $mobile = $this->request->get('mobile');
        $user = $this->modelCustomer->select('id','name','mobile','avatar','nickname','brief','type','sex','email','address')
            ->withOnly('company',array('id','customer_id','name','website','finance_status','position','weixin','email'))
            ->where('mobile',$mobile)
            ->first();
        if(!$user){
            return return_rest('0','','该用户不存在');
        }
        $user = $user->toArray();
        if(is_null($user['company'])){
            $user['is_company'] = '0';
        }else{
            $user['is_company'] = '1';
        }
        $user['company'] = is_null($user['company']) ? "" : $user['company'];
        return return_rest('1',compact('user'),'获取成功');
    }

    /**
     * @api {put} /user 修改个人信息
     * @apiDescription 修改个人信息
     * @apiGroup user
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiParam {String} [name] 姓名
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *        "id": 2,
     *        "email": 'liyu01989@gmail.com',
     *        "name": "ffff",
     *        "created_at": "2015-10-28 07:30:56",
     *        "updated_at": "2015-10-28 09:42:43",
     *        "deleted_at": null,
     *     }
     */
    public function update()
    {
        $user = $this->user();

        $user->fill($this->request->input());

        if($user->save()){
            return return_rest('1','','更新成功');
        }
        return return_rest('0','','更新失败');
        //return $this->response->item($user, new CustomerTransformer);

    }
    /**
     *忘记密码 检查verify code
     */
    public function forgetVerify()
    {
        $token = $this->request->get('smsToken');
        $validator = \Validator::make($this->request->all(), [
            'mobile'    => "required|exists:customers",
            'verifyCode'    => "required|verify_code:$token|confirm_mobile_rule:mobile_required,$token"
        ], [
            'mobile.required'           => '缺少手机号码字段',
            'mobile.exists'             => '手机号码未注册',
            'verifyCode.required'       => '缺少验证码字段',
            'verify_code'               => '验证码错误',
            'confirm_mobile_not_change' => '手机号码与发送验证码手机不符',
            'confirm_mobile_rule'       => '验证失败'
        ]);
        $messages = $validator->messages();
        if($messages->has('mobile')){
            $mobiles_rule = $messages->get('mobile');
            foreach($mobiles_rule as $mobile_rule){
                if($mobile_rule === '手机号码未注册') return return_rest('0','','手机号码未注册');
            }
            return return_rest('0','','手机号码输入有误');
        }
        if($messages->has('verifyCode')){
            $verifyCodes_rule = $messages->get('verifyCode');
            foreach($verifyCodes_rule as $verifyCode_rule){
                if($verifyCode_rule === '手机号码与发送验证码手机不符') return return_rest(0,'','手机号码与发送验证码手机不符');
            }
            return return_rest('0','','验证码错误');
        }

        //验证通过
        $customer = $this->modelCustomer->getCustomerByMobile($this->request->get('mobile'));
        //设置用户为登录状态
        $token = \JWTAuth::fromUser($customer);
        return return_rest('1',compact('token'),'验证成功');
    }
    /**
     * 修改密码
     */
    public function forgetPassword()
    {
        $validator = \Validator::make($this->request->all(), [
            'password'              => 'required',
        ]);

        if($validator->fails()){
            if($validator->messages()->get('password')[0] === 'The password field is required.') return return_rest('0','','密码提供信息不正确');
        }

        //变更密码
        $customer = $this->user();
        //变更环信密码
        $easemob_reset_password = Easemob::reset_password($customer->mobile,$this->request->get('password'));
        if(!$easemob_reset_password) return return_rest('0','','环信密码修改失败');
        //更新数据库密码
        $customer->password = bcrypt($this->request->get('password'));
        if($customer->save()){
            return return_rest('1','','密码修改成功');
        }

        return return_rest('0','','密码修改失败');

    }
    /**
     * @api {put} /user/password 修改密码
     * @apiDescription 修改密码
     * @apiGroup user
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiParam {String} old_password          旧密码
     * @apiParam {String} password              新密码
     * @apiParam {String} password_confirmation 确认新密码
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 204 No Content
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *         "password": [
     *             "两次输入的密码不一致",
     *             "新旧密码不能相同"
     *         ],
     *         "password_confirmation": [
     *             "两次输入的密码不一致"
     *         ],
     *         "old_password": [
     *             "密码错误"
     *         ]
     *     }
     */
    public function editPassword()
    {
        $customer = $this->user();

        $validator = \Validator::make($this->request->all(), [
            'old_password'          => 'required',
            'password'              => 'required|confirmed|different:old_password',
            'password_confirmation' => 'required|same:password',
        ], [
            'password.confirmed'         => '两次输入的密码不一致',
            'password_confirmation.same' => '两次输入的密码不一致',
            'password.different'         => '新旧密码不能相同',
        ]);

        $auth = \Auth::once([
            'mobile'    => $customer->mobile,
            'password' => $this->request->get('old_password'),
        ]);

        if (!$auth) {
            $validator->after(function ($validator) {
                $validator->errors()->add('old_password', '密码错误');
            });
        }

        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }

        $customer->password = bcrypt($this->request->get('password'));

        $customer->save();

        return $this->response->noContent();
    }

    //上传头像

    //完善用户个人信息

    //我的公司
    /**
     * @param $id 用户id
     * @return Customer
     */
    public function company($id,Company $company)
    {
        return $company->searchCustomer($id)
            ->withOnly('field',['id','name'])//查询公司领域
            ->first();
    }

    /**
     * 联系人列表
     *
     */
    public function contract()
    {
        //解析数据
        $mobileList = $this->request->input('mobilelist');

        $mobileList = json_decode($mobileList,true);
        $mobiles = array();
        try{
            foreach ($mobileList as $list){
                foreach ($list as $key => $val){
                    array_push($mobiles,$val);
                }
            }
        }catch(\Exception $e){
            return return_rest('0','',$e->getMessage());
        }

        $list = $this->modelCustomer->select('name','mobile')->whereIn('mobile',$mobiles)->get()->toArray();

        return return_rest('1',compact('list'),'获取列表成功');
    }
}