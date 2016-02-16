<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/2/3
 * Time: 上午1:01
 */

namespace App\Http\Controllers\Api\V1\Activity;


use App\Http\Controllers\Api\BaseController;
use App\Models\Activity\Activity;
use App\Models\Activity\ActivityComment;
use App\Models\Activity\ActivityCustomerAttention;
use App\Models\Activity\ActivityCustomerCollect;
use Illuminate\Http\Request;

class ActivityController extends BaseController
{
    protected $activity;
    protected $request;

    /**
     * ActivityController constructor.
     * @param $activity
     */
    public function __construct(Request $request,Activity $activity)
    {
        $this->request = $request;
        $this->activity = $activity;
    }

    /**
     * 获取活动列表
     */
    public function index()
    {
        $per_page = 15;
        if($this->request->has('per_page')){
            $per_page = $this->request->get('per_page');
        }
        //获取活动列表
        $activity = $this->activity->select('id','image','brief','title')->paginate($per_page)->toArray();

        return return_rest('1',compact('activity'),'获取列表成功');
    }

    /**
     * 获取活动详情
     */
    public function detail()
    {
        $id = $this->request->get('id');
        try{
            $activity = $this->activity->select('id','title','image','content','created_at')->findOrFail($id)->toArray();
        }catch (\Exception $e){
            if($e->getMessage() === 'No query results for model [App\Models\Activity\Activity].') return return_rest('0','','该活动不存在');
            return return_rest('0','',$e->getMessage());
        }

        return return_rest('1',compact('activity'),'获取详情成功');
    }

    /**
     * 检查当前用户是否关注活动
     */
    public function checkAttention()
    {
        $id = $this->request->get('id');
        //判断活动是否存在并获取活动id
        try{
            $activity = $this->activity->findOrFail($id);
        }catch (\Exception $e){
            if($e->getMessage() === 'No query results for model [App\Models\Activity\Activity].') return return_rest('0','','该活动不存在');
            return return_rest('0','',$e->getMessage());
        }
        //判断用户是否关注该活动
        $attention = ActivityCustomerAttention::where('activity_id',$activity->id)->where('customer_id',$this->user()->id)->get();
        if(count($attention) >= 1){
            return return_rest('1',array('attention' => '1'), '用户已关注该活动');
        }
        return return_rest('1',array('attention' => '0'), '用户未关注该活动');
    }

    /**
     * 用户进行取消关注 关注操作
     */
    public function doAttention()
    {
        $id = $this->request->get('id');
        //判断活动是否存在并获取活动id
        try{
            $activity = $this->activity->findOrFail($id);
        }catch (\Exception $e){
            if($e->getMessage() === 'No query results for model [App\Models\Activity\Activity].') return return_rest('0','','该活动不存在');
            return return_rest('0','',$e->getMessage());
        }
        //判断用户是否关注该活动
        $attention = ActivityCustomerAttention::where('activity_id',$activity->id)->where('customer_id',$this->user()->id)->first();
        //判断操作类型 1关注 0取消关注
        $action = $this->request->get('attention');
        if($action == '1'){
            if(count($attention) >= 1){
                return return_rest('1','', '用户已关注该活动');
            }
            $attention = new ActivityCustomerAttention();
            $attention->customer_id = $this->user()->id;
            $attention->activity_id = $id;
            if($attention->save()){
                return return_rest('1','', '关注成功');
            }
            return return_rest('0','', '关注失败');
        }
        if($action == '0'){
            if(count($attention) == 0){
                return return_rest('0','', '用户未关注该活动');
            }
            if($attention->delete()){
                return return_rest('1','', '取消关注成功');
            }
            return return_rest('0','', '取消关注失败');
        }

    }
    /**
     * 检查当前用户是否收藏活动
     */
    public function checkCollect()
    {
        $id = $this->request->get('id');
        //判断活动是否存在并获取活动id
        try{
            $activity = $this->activity->findOrFail($id);
        }catch (\Exception $e){
            if($e->getMessage() === 'No query results for model [App\Models\Activity\Activity].') return return_rest('0','','该活动不存在');
            return return_rest('0','',$e->getMessage());
        }
        //判断用户是否关注该活动
        $collect = ActivityCustomerCollect::where('activity_id',$activity->id)->where('customer_id',$this->user()->id)->get();
        if(count($collect) >= 1){
            return return_rest('1',array('collect' => '1'), '用户已收藏该活动');
        }
        return return_rest('1',array('collect' => '0'), '用户未收藏该活动');
    }
    /**
     * 用户进行收藏 取消收藏操作
     */
    public function doCollect()
    {
        $id = $this->request->get('id');
        //判断活动是否存在并获取活动id
        try{
            $activity = $this->activity->findOrFail($id);
        }catch (\Exception $e){
            if($e->getMessage() === 'No query results for model [App\Models\Activity\Activity].') return return_rest('0','','该活动不存在');
            return return_rest('0','',$e->getMessage());
        }
        //判断用户是否收藏该活动
        $collect = ActivityCustomerCollect::where('activity_id',$activity->id)->where('customer_id',$this->user()->id)->first();
        //判断操作类型 1收藏 0取消收藏
        $action = $this->request->get('collect');
        if($action == '1'){
            if(count($collect) >= 1){
                return return_rest('1','', '用户已收藏该活动');
            }
            $collect = new ActivityCustomerCollect();
            $collect->customer_id = $this->user()->id;
            $collect->activity_id = $id;
            if($collect->save()){
                return return_rest('1','', '收藏成功');
            }
            return return_rest('0','', '关注失败');
        }
        if($action == '0'){
            if(count($collect) == 0){
                return return_rest('0','', '用户未收藏该活动');
            }
            if($collect->delete()){
                return return_rest('1','', '取消收藏成功');
            }
            return return_rest('0','', '取消收藏失败');
        }
    }
    /**
     * 新增评论
     */
    public function addComment()
    {
        //获取活动id
        $id = $this->request->get('id');
        //获取评论内容
        $content = $this->request->get('content');
        //增加评论
        $comment = new ActivityComment();
        $comment->activity_id = $id;
        $comment->content = $content;
        $comment->customer_id = $this->user()->id;
        $comment->customer_name = $this->user()->name;
        if($comment->save()){
            //获取评论列表c
            return return_rest(1,'','评论添加成功');
        }
        return return_rest(0,'','评论添加失败');
    }
    /**
     * 获取活动评论
     */
    public function comment()
    {
        //获取活动id
        $id = $this->request->get('id');
        //根据活动id获取相关评论
        $comments = ActivityComment::select('content','customer_name','created_at')
            ->where('activity_id',$id)
            ->orderBy('created_at','desc')
            ->get()
            ->toArray();
        return return_rest(1,compact('comments'),'操作成功');
    }
}