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


class AuthController extends BaseController
{
    public function login()
    {
        $validator = \Validator::make($this->request->all(), [
            'mobile'    => 'required',
            'password' => 'required',
        ]);

        $credentials = $this->request->only('mobile', 'password');

        if ( ! $token = \JWTAuth::attempt($credentials)) {
            $validator->after(function ($validator) {
                $validator->errors()->add('error_msg', '用户名或密码错误');
            });
        }

        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }

        return $this->response->array(['token' => $token]);
    }

    public function refreshToken()
    {
        $newToken = \JWTAuth::parseToken()->refresh();
        return $this->response->array(['token' => $newToken]);
    }

    /**
     * @api {post} /auth/signup 注册
     * @apiDescription 注册
     * @apiName auth/signup
     * @apiGroup Auth
     * @apiPermission none
     * @apiVersion 0.1.0
     * @apiParam {Email}  email   email[唯一]
     * @apiParam {String} password   密码
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         token: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjEsImlzcyI6Imh0dHA6XC9cL21vYmlsZS5kZWZhcmEuY29tXC9hdXRoXC90b2tlbiIsImlhdCI6IjE0NDU0MjY0MTAiLCJleHAiOiIxNDQ1NjQyNDIxIiwibmJmIjoiMTQ0NTQyNjQyMSIsImp0aSI6Ijk3OTRjMTljYTk1NTdkNDQyYzBiMzk0ZjI2N2QzMTMxIn0.9UPMTxo3_PudxTWldsf4ag0PHq1rK8yO9e5vqdwRZLY
     *     }
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *         "email": [
     *             "该邮箱已被他人注册"
     *         ],
     *     }
     */
    public function signup()
    {
        $token = $this->request->get('token');
        $validator = \Validator::make($this->request->all(), [
            'mobile'    => "required|unique:customers|confirm_mobile_not_change:$token",
            'password'     => 'required',
            'verifyCode'    => "required|verify_code:$token|confirm_mobile_rule:mobile_required,$token"
        ], [
            'mobile.unique' => '该手机号码已被他人注册',
        ]);

        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }

        $mobile  = $this->request->get('mobile');
        $password = $this->request->get('password');

        $customer = new Customer;
        $customer->mobile = $mobile;
        $customer->password = bcrypt($password);
        $customer->save();

        // 用户注册事件
        $token = \JWTAuth::fromUser($customer);
        return $this->response->array(['token' => $token]);
    }

}