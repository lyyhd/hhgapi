<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/2/16
 * Time: 下午2:05
 */

namespace App\Models;


use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArticleComment extends BaseModel
{
    protected $table = 'article_comments';

    use SoftDeletes;
    protected $dates = ['deleted_at'];

}