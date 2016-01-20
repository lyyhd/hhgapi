<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/1/18
 * Time: 上午12:21
 * 资讯管理api
 */

namespace App\Http\Controllers\Api\V1\Article;


use App\Http\Controllers\Api\BaseController;
use App\Models\Article;
use App\Transformer\ArticleTransformer;
use Illuminate\Http\Request;

class ArticleController extends BaseController
{
    protected $article;

    public function __construct(Request $request, Article $article)
    {
        parent::__construct($request);

        $this->article = $article;
    }

    /**
     *现实所有文章
     */
    public function index(){
        //获取文章列表
        $article = $this->article->paginate();
        return $this->response->paginator($article, new ArticleTransformer());
    }
}