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
$api->get('activity/detail',['as' => 'activity.detail','uses' => 'ActivityController@detail']);
//获取评论列表
$api->get('activity/comment','ActivityController@comment')->name('activity.getComment');

//需要登录验证的路由
$api->group(['middleware' => 'jwt.auth'], function ($api) {
    $api->get('activity/attention', ['as' => 'activity.getAttention', 'uses' => 'ActivityController@checkAttention']);
    $api->post('activity/attention', ['as' => 'activity.postAttention', 'uses' => 'ActivityController@doAttention']);
    $api->get('activity/collect', ['as' => 'activity.getCollect', 'uses' => 'ActivityController@checkCollect']);
    $api->post('activity/collect', ['as' => 'activity.postCollect', 'uses' => 'ActivityController@doCollect']);
    //添加评论
    $api->post('activity/comment','ActivityController@addComment')->name('activity.addComment');
    //获取关注列表
    $api->get('activity/attentionList','ActivityController@attentionList')->name('activity.attentionList');
    //检查用户是否已报名活动
    $api->get('activity/apply','ActivityController@checkApply')->name('activity.checkApply');
});
