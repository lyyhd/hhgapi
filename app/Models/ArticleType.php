<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/1/21
 * Time: 上午11:04
 */

namespace App\Models;


class ArticleType extends BaseModel
{

    public function article()
    {
        return $this->hasMany('App\Models\Article');
    }
}