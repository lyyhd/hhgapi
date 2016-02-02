<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/2/3
 * Time: 上午1:10
 */
//get article 获取所有文章
$api->get('activity',['as' => 'activity.index','uses' => 'ActivityController@index']);
//获取文章详情
$api->get('activity/{id}/detail',['as' => 'activity.detail','uses' => 'ActivityController@detail']);

//需要登录验证的路由
$api->group(['middleware' => 'jwt.auth'], function ($api) {
    $api->get('activity/{id}/attention', ['as' => 'activity.getAttention', 'uses' => 'ActivityController@checkAttention']);
    $api->post('activity/{id}/attention', ['as' => 'activity.getAttention', 'uses' => 'ActivityController@doAttention']);
});
