<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/1/20
 * Time: 上午1:10
 */
//文章相关api
//get article 获取所有文章
$api->get('article',['as' => 'article.index','uses' => 'ArticleController@index']);
//获取文章详情
$api->get('article/detail',['as' => 'article.detail','uses' => 'ArticleController@detail']);
//阅读量增加
$api->get('article/view','ArticleController@addView')->name('article.addView');
//获取文章类型列表
$api->get('article/types','ArticleController@articleType')->name('article.types');
//获取评论列表
$api->get('article/comment','ArticleController@comment')->name('article.getComment');

//需要登录进行的操作
$api->group(['middleware' => 'jwt.auth'],function($api){
    //评论时 检测用户是否被禁言
    $api->get('article/gag','ArticleController@checkGag')->name('article.checkGag');
    //添加评论
    $api->post('article/comment','ArticleController@addComment')->name('article.addComment');
});