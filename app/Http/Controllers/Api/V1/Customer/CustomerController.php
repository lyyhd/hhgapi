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
use Illuminate\Http\Request;

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
        return $this->response->item($this->user(), new CustomerTransformer);
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
        $user = $this->me();

        $user->fill($this->request->input());

        $user->save();

        return $this->response->item($user, new CustomerTransformer);
    }
    /**
     *忘记密码 检查verify code
     */
    public function forgetVerify()
    {
        $token = $this->request->get('token');
        $validator = \Validator::make($this->request->all(), [
            'mobile'    => "required|exists:customers",
            'verifyCode'    => "required|verify_code:$token|confirm_mobile_rule:mobile_required,$token"
        ], [
            'mobile.required'           => '缺少手机号码字段',
            'mobile.exists'             => '手机号码不存在',
            'verifyCode.required'       => '缺少验证码字段',
            'verify_code'               => '验证码错误',
            'confirm_mobile_not_change' => 'smsToken错误',
            'confirm_mobile_rule'       => '验证失败'
        ]);
        if ($validator->fails())return $this->errorBadRequest($validator->messages());
        //验证通过
        $customer = $this->modelCustomer->getCustomerByMobile($this->request->get('mobile'));
        //设置用户为登录状态
        $token = \JWTAuth::fromUser($customer);
        $result = return_message(true,'验证通过');
        return compact('token','result');
    }
    /**
     * 修改密码
     */
    public function forgetPassword()
    {
        $validator = \Validator::make($this->request->all(), [
            'password'              => 'required|confirmed',
            'password_confirmation' => 'required|same:password',
        ], [
            'password.confirmed'         => '两次输入的密码不一致',
            'password_confirmation.same' => '两次输入的密码不一致',
        ]);

        if($validator->fails()) return $this->errorBadRequest($validator->messages());
        //变更密码
        $customer = $this->user();
        $customer->password = bcrypt($this->request->get('password'));
        $customer->save();

        return $customer;

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
        $customer = $this->me();

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
    //找回密码
    //注册
    //发送验证码

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
}