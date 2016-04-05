<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/1/21
 * Time: 下午2:32
 */
/**
 * 公司相关
 */
//获取公司列表
$api->get('company',[
    'as'    => 'company.index',
    'uses'  => 'CompanyController@index'
]);
//获取公司详情
$api->get('company/detail','CompanyController@show')->name('company.show');
//更新公司信息
$api->put('company',[
    'as'    => 'company.update',
    'uses'  => 'CompanyController@update'
]);
//获取公司列表
$api->get('company/all','CompanyController@all')->name('company.all');

//获取公司领域
$api->get('company/field',[
    'as'    => 'company.field',
    'uses'  => 'CompanyFieldController@index',
]);

//获取领域详情 子类
$api->get('company/field/{id}/detail',[
    'as'    => 'company.field',
    'uses'  => 'CompanyFieldController@fieldDetail',
]);
//公司访问量+1
$api->get('company/view','CompanyController@view')->name('company.view');
//文章分享数增加
$api->post('company/share','CompanyController@addShare')->name('company.addShare');
//获取职位列表
$api->get('company/position','CompanyController@position')->name('company.position');


/*
 * 项目相关
 */
//获取项目列表
$api->get('company/project','CompanyProjectController@index')->name('company.project');
//获取项目详情
$api->get('company/project/detail','CompanyProjectController@show')->name('company.project.detail');
//获取项目领域列表
$api->get('company/project/field','CompanyProjectController@field')->name('company.project.field');
//获取项目投资轮次列表
$api->get('company/project/round','CompanyProjectController@round')->name('company.project.round');
//项目访问量+1
$api->get('company/project/view','CompanyProjectController@viewPlus')->name('company.project.view');
// 需要jwt验证后才能使用的API
$api->group(['middleware' => 'jwt.auth'], function ($api) {
    //获取我的项目
    $api->get('company/project/mine','CompanyProjectController@mine')->name('company.project.mine');
    //创建公司
    $api->post('company/store','CompanyController@store')->name('company.store');
    //获取我的创业经历
    $api->get('company/experience','CompanyController@experience')->name('company.experience');
    //更新我的项目接口
    $api->put('company/project','CompanyProjectController@update')->name('company.project.update');
    //更新项目logo
    $api->put('company/project/logo','CompanyProjectController@logo')->name('company.project.logo');
    //更新项目介绍
    $api->put('company/introduce','CompanyController@introduce')->name('company.update.introduce');
    //更新项目领域
    $api->put('company/project/field','CompanyProjectController@updateField')->name('company.project.field.update');
    //更新团队介绍 团队优势
    $api->put('company/project/team','CompanyProjectController@updateTeam')->name('company.team');
    //检查用户是否收藏项目
    $api->get('company/project/collect', ['as' => 'company.project.getCollect', 'uses' => 'CompanyProjectController@checkCollect']);
    //收藏项目
    $api->post('company/project/collect', ['as' => 'company.project.postCollect', 'uses' => 'CompanyProjectController@doCollect']);
    //获取投资人收藏项目
    $api->get('company/project/loveList', 'CompanyProjectController@loveList')->name('company.project.loveList');
    //创业者获取项目约谈记录
    $api->get('company/project/interview/list','CompanyProjectController@interviewList')->name('company.project.interview.list');
    //获取约谈记录详情
    $api->get('company/project/interview/show','CompanyProjectController@interviewShow')->name('company.project.interview.show');
    //获取该项目的投资人列表
    $api->get('company/project/investor','CompanyProjectController@investor')->name('company.project.investor');
    //获取项目投资人详情
    $api->get('company/project/investor/show','CompanyProjectController@investorDetail')->name('company.project.investor.show');
    //增加约谈记录
    $api->post('company/project/interview/add','CompanyProjectController@interviewAdd')->name('company.project.interview.add');
});