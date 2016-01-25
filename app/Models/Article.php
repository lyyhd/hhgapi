<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/1/18
 * Time: 上午12:46
 */

namespace App\Models;


use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends BaseModel
{
    protected $table = 'articles';

    use SoftDeletes;

    //
    public function getArticleByFilter($filter, $limit = 15)
    {
        return $this->applyFilter($filter)->paginate($limit);
    }
    //
    public function applyFilter($filter)
    {
        switch($filter){

        }
    }
    //阅读量+1
    //
    //设置关联
    public function content()
    {
        return $this->hasOne('App\Models\ArticleContent');
    }

    public function type()
    {
        return $this->belongsTo('App\Models\ArticleType');
    }

}