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
use App\Models\ArticleType;
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
        if($type = $this->request->get('type')){
            $article = $this->article->where('type_id',$type)->paginate();
            return return_rest('1',compact('article'),'获取文章详情');
        }
        $article = $this->article->orderBy('created_at','desc')->paginate(1);
        return return_rest('1',compact('article'),'获取文章列表');
    }

    /**
     * 获取首页文章
     *
     */
    public function indexArticle()
    {

    }

    //获取文章详情
    public function detail()
    {
        $id = $this->request->get('id');
        $article = $this->article->withOnly('content',['article_id','content'])->find($id);
        return return_rest('1',compact('article'),'获取文章详情');
    }
    //增加文章阅读量
    public function addView()
    {
        $id = $this->request->get('id');
        //对文章进行+1的阅读量
        $this->article->find($id)->increment('view');
    }
    /**
     * 获取新闻分类
     */
    public function articleType()
    {
        $types = ArticleType::all()->toArray();

        return return_rest('1',compact('types'),'获取文章类型');
    }

}