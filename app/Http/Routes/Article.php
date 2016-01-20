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