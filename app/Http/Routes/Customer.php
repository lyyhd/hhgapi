<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/1/21
 * Time: 下午2:32
 */

//获取投资人列表
$api->get('customer/investor','CustomerController@investor')->name('customer.investor');
// 需要jwt验证后才能使用的API
$api->group(['middleware' => 'jwt.auth'], function ($api) {
    //获取我的工作经历
    $api->get('customer/employment/experience','CustomerController@employmentExperience')->name('customer.employment.experience');
    //获取我的创业经历
    $api->get('customer/company/experience','CustomerController@companyExperience')->name('customer.company.experience');
    //自增创业经历
    $api->post('customer/employment','CustomerController@employment')->name('customer.employment.create');
    //更新投资人信息
    $api->put('customer/investor','CustomerController@updateInvestor')->name('customer.investor.update');
    //更新投资领域
    $api->put('customer/investor/field','CustomerController@updateInvestorField')->name('customer.investor.update.field');
    //更新用户手机号码
    $api->post('customer/changeMobile','CustomerController@changeMobile')->name('customer.changeMobile');
    //更改用户类型
    $api->put('customer/changeType','CustomerController@changeType')->name('customer.changeType');
});