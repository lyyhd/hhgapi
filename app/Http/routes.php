<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['middleware' => ['web']], function () {
    //
});
//

$api = app('Dingo\Api\Routing\Router');

$api->version('v1',['namespace' => 'App\Http\Controllers\Api\V1'], function($api) {
    //文章相关api
    $api->group(['namespace' => 'Article'], function($api){
        require (__DIR__ . '/Routes/Article.php');
    });
    //活动相关api
    //文章相关api
    $api->group(['namespace' => 'Activity'], function($api){
        require (__DIR__ . '/Routes/Activity.php');
    });
    //公司相关api
    $api->group(['namespace' => 'Company'], function($api){
        require (__DIR__ . '/Routes/Company.php');
    });
    /**
     * 短信验证码
     */
    //注册时发送
    $api->post('sms/verify', [
        'as'        => 'sms.verify',
        'uses'      => 'Sms\SmsController@verify'
    ]);
    //忘记密码时发送
    $api->post('sms/forget', [
        'as'        => 'sms.forget',
        'uses'      => 'Sms\SmsController@forget'
    ]);
    # Auth
    // 登录
    $api->post('auth/login', [
        'as'   => 'auth.login',
        'uses' => 'AuthController@login'
    ]);
    // 注册
    $api->post('auth/signup', [
        'as'   => 'auth.signup',
        'uses' => 'AuthController@signup'
    ]);
    //退出
    $api->post('auth/logout',[
       'as'     => 'auth.logout',
        'uses'  => 'AuthController@logout'
    ]);
    /**
     * 忘记密码
     */
    //确认忘记密码信息
    $api->post('customer/forget/verify',[
        'as'    => 'customer.forgetVerify',
        'uses'  => 'Customer\CustomerController@forgetVerify'
    ]);
    //获取轮播图
    $api->get('carousel', [
        'as'    => 'carousel.index',
        'uses'  => 'Carousel\CarouselController@index'
    ]);
    //根据用户手机号码获取用户信息
    $api->get('customer/byMobile','Customer\CustomerController@detailByMobile')->name('customer.detailByMobile');

    // 需要jwt验证后才能使用的API
    $api->group(['middleware' => 'jwt.auth'], function ($api) {
        # Auth
        // 刷新token
        $api->post('auth/refreshToken', [
            'as'   => 'auth.refreshToken',
            'uses' => 'AuthController@refreshToken'
        ]);

        #User
        // 获得个人信息
        $api->get('/customer', [
            'as'   => 'customer.show',
            'uses' => 'Customer\CustomerController@show'
        ]);
        // 更新个人信息
        $api->put('/customer', [
            'as'   => 'customer.update',
            'uses' => 'Customer\CustomerController@update'
        ]);
        // 修改密码
        $api->post('/customer/password', [
            'as'   => 'customer.password.update',
            'uses' => 'Customer\CustomerController@editPassword'
        ]);
        //设置新密码
        $api->post('customer/forget/password',[
            'as'    => 'customer.forgetPassword',
            'uses'  => 'Customer\CustomerController@forgetPassword'
        ]);
        //上传图片 临时
        $api->post('customer/avatar',[
            'as'    => 'customer.avatar',
            'uses'  => 'Image\ImageController@avatar'
        ]);
        //修改新密码
        $api->post('customer/forget/password',[
            'as'    => 'customer.forgetPassword',
            'uses'  => 'Customer\CustomerController@forgetPassword'
        ]);
        //获取联系人列表
        $api->post('customer/contract',[
            'as'    => 'customer.contract',
            'uses'  => 'Customer\CustomerController@contract'
        ]);

        //获取用户详情
        $api->get('/user/{userId}/detail/{detailId}',['as' => 'user.detail','uses' => 'Customer\DetailController@show']);
        //获取我的公司
        $api->get('/customer/{id}/company',['as' => 'customer.company','uses' => 'Customer\CustomerController@company']);
    });

    $api->post('image/upload',[
        'as'    => 'image.upload',
        'uses'  => 'Image\ImageController@upload'
    ]);

});
