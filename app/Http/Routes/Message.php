<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/1/20
 * Time: 上午1:10
 */
//需要登录进行的操作
$api->group(['middleware' => 'jwt.auth'],function($api){
    //获取用户消息列表
    $api->get('customer/messages','MessageController@index')->name('customer.messages');
    //添加消息
    $api->post('customer/messages','MessageController@store')->name('customer.messages.store');
    //获取未读消息数量
    $api->get('customer/messages/unread','MessageController@unread')->name('customer.messages.unread');
    //将消息标记为已读
    $api->get('customer/messages/read/{id}','MessageController@read')->name('customer.messages.read');
});