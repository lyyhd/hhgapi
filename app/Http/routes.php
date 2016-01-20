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
    $api->group(['namespace' => 'Article'], function($api){
        require (__DIR__ . '/Routes/Article.php');
    });
    /**
     * 短信验证码
     */
    $api->post('verify', [
        'as'        => 'sms.verify',
        'uses'      => 'Sms\SmsController@verify'
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

    //获取轮播图
    $api->get('carousel', [
        'as'    => 'carousel.index',
        'uses'  => 'Carousel\CarouselController@index'
    ]);

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

        //获取用户详情
        $api->get('/user/{userId}/detail/{detailId}',['as' => 'user.detail','uses' => 'Customer\DetailController@show']);
    });


});
