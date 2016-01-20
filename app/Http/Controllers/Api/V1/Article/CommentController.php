<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/1/18
 * Time: 下午3:54
 * 文章评论相关接口
 */

namespace App\Http\Controllers\Api\V1\Article;


use App\Http\Controllers\Api\BaseController;

class CommentController extends BaseController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }
}