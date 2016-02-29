<?php


$api->group(['middleware' => 'jwt.auth'], function ($api) {
    /**
     * 投资公司
     * 获取个人投资经历
     */
    $api->get('invest/experience','InvestExperienceController@index')->name('invest.experience.index');
    //新增投资简历
    $api->post('invest/experience','InvestExperienceController@create')->name('invest.experience.create');
});
//获取投资轮次
$api->get('invest/round','InvestExperienceController@round')->name('invest.experience.round');



