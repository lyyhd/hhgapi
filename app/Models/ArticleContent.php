<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/1/21
 * Time: 上午9:15
 */

namespace App\Models;


class ArticleContent extends BaseModel
{
    public function article()
    {
        return $this->belongsTo('App\Models\Article');
    }
}