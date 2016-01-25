<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/1/21
 * Time: 下午2:32
 */
$api->get('company',[
    'as'    => 'company.index',
    'uses'  => 'CompanyController@index'
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