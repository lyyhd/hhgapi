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
    protected $dates = ['deleted_at'];

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
    //获取位于发布状态的文章
    public function scopeByStatus($query)
    {
        return $query->where('status_id', '=' , '1');
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