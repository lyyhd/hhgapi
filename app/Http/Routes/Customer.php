<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/1/21
 * Time: 下午2:32
 */

// 需要jwt验证后才能使用的API
$api->group(['middleware' => 'jwt.auth'], function ($api) {
    //获取我的工作经历
    $api->get('customer/employment/experience','CustomerController@employmentExperience')->name('customer.employment.experience');
    //获取我的创业经历
    $api->get('customer/company/experience','CustomerController@companyExperience')->name('customer.company.experience');
});