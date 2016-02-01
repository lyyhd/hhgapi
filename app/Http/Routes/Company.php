<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/1/21
 * Time: 下午2:32
 */
//获取公司列表
$api->get('company',[
    'as'    => 'company.index',
    'uses'  => 'CompanyController@index'
]);
//更新公司信息
$api->put('company',[
    'as'    => 'company.update',
    'uses'  => 'CompanyController@update'
]);

//获取公司领域相关路由
$api->get('company/field',[
    'as'    => 'company.field',
    'uses'  => 'CompanyFieldController@index',
]);
//获取领域详情 子类
$api->get('company/field/{id}/detail',[
    'as'    => 'company.field',
    'uses'  => 'CompanyFieldController@fieldDetail',
]);