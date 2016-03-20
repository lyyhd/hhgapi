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
use App\Models\ArticleComment;
use App\Models\ArticleType;
use App\Models\Customer;
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
        //判断是否获取首页文章
        if($this->request->get('index') == '1')
        {
            $article = $this->article->where('is_index','1')
                ->byStatus()
                ->limit(6)->offset(0)
                ->orderBy('indexed_at','desc')
                ->paginate();
            return return_rest('1',compact('article'),'获取首页文章列表');
        }
        //获取文章列表
        if($type = $this->request->get('type')){
            $article = $this->article->where('type_id',$type)->orderBy('created_at','desc')->byStatus()->paginate();
            return return_rest('1',compact('article'),'获取文章列表');
        }
        $article = $this->article->orderBy('created_at','desc')->byStatus()->paginate(15);
        return return_rest('1',compact('article'),'获取文章列表');
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
        return return_rest('1','','文章阅读量+1');
    }
    /**
     * 获取新闻分类
     */
    public function articleType()
    {
        $types = ArticleType::all()->toArray();

        return return_rest('1',compact('types'),'获取文章类型');
    }
    /**
     * 检查用户是否被禁言
     *
     */
    public function checkGag()
    {
        //获取用户是否被禁言
        $customer = Customer::find($this->user()->id);
        if($customer){
            $isGag = $customer->is_article_comment;
            return return_rest('1',array('isGag'=>$isGag),'用户是否被禁言');
        }
        return return_rest('0','','该用户不存在');
    }
    /**
     * 获取文章评论
     */
    public function comment()
    {
        //获取活动id
        $id = $this->request->get('id');
        //根据活动id获取相关评论
        $comments = ArticleComment::select('id','mobile','content','customer_id','customer_name','customer_avatar','created_at','reply_customer_id','reply_customer_name')
            ->where('article_id',$id)
            ->orderBy('created_at','desc')
            ->get()->toArray();
        return return_rest('1',compact('comments'),'操作成功');
    }
    /**
     * 添加文章评论
     */
    public function addComment()
    {
        //获取活动id
        $id = $this->request->get('id');
        //获取评论内容
        $content = $this->request->get('content');
        //增加评论
        $comment = new ArticleComment();
        $comment->article_id = $id;
        $comment->content = $content;
        $comment->customer_id = $this->user()->id;
        $comment->customer_name = $this->user()->name;
        $comment->customer_avatar = $this->user()->avatar;
        //判断回复用户
        if($reply_customer_id = $this->request->get('reply_customer_id')){
            //获取用户信息
            $reply_customer = Customer::find($reply_customer_id);
            if(!$reply_customer){
                return return_rest('0','','评论添加失败');
            }
            $comment->reply_customer_id = $reply_customer_id;
            $comment->reply_customer_name = $reply_customer->name;
        }
        if($comment->save()){
            //对文章进行+1的评论
            $this->article->find($id)->increment('comments');
            //获取评论列表c
            return return_rest('1','','评论添加成功');
        }
        return return_rest('0','','评论添加失败');
    }


}