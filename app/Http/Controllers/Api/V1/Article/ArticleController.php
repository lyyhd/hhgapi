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
use DebugBar\StandardDebugBar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    //获取文章详情
    public function detail($id)
    {

        $article = $this->article->withOnly('content',['article_id','content'])->find($id);
        return $article;
    }


}