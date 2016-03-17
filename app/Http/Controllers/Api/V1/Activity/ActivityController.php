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
use App\Models\Activity\ActivityCommentReply;
use App\Models\Activity\ActivityCustomerAttention;
use App\Models\Activity\ActivityCustomerCollect;
use App\Models\Activity\Apply;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
        if($take = $this->request->get('take')){
            //获取活动列表
            $activity = $this->activity->select('id','image','brief','title','begin_at','created_at')->take($take)->orderBy('created_at','desc')->get()->toArray();

            return return_rest('1',compact('activity'),'获取列表成功');
        }
        //获取活动列表
        $activity = $this->activity->select('id','image','brief','title','begin_at','created_at')->orderBy('created_at','desc')->paginate($per_page)->toArray();

        return return_rest('1',compact('activity'),'获取列表成功');
    }

    /**
     * 获取活动详情
     */
    public function detail()
    {
        $id = $this->request->get('id');
        try{
            $activity = $this->activity->select('id','title','image','content','begin_at','end_at','address')->findOrFail($id)->toArray();
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
     * 获取用户关注列表
     */
    public function attentionList()
    {
        $per_page = 15;
        if($this->request->has('per_page')){
            $per_page = $this->request->get('per_page');
        }
        //获取活动id
        $activities = DB::table('activity_customer_attention')->select('activity_id')->where('customer_id',$this->user()->id)->orderBy('created_at','desc')->get();
        $aid = array();
        foreach($activities as $activity){
            $aid[] = $activity->activity_id;
        }
        //获取活动
        $attentionList = $this->activity->select('id','image','brief','title','created_at','begin_at','end_at')->whereIn('id',$aid)->paginate($per_page);
        $attentionList = $attentionList->toArray();
        if($attentionList){
            return return_rest('1',compact('attentionList'),'获取列表成功');
        }
        return return_rest('1',"",'用户暂未关注活动');
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
        $comment->customer_avatar = $this->user()->avatar;
        $comment->customer_mobile = $this->user()->mobile;
        //判断回复用户
        if($reply_customer_id = $this->request->get('reply_customer_id')){
            //获取用户信息
            $reply_customer = Customer::find($reply_customer_id);
            if(!$reply_customer){
                return return_rest('0','','评论添加失败1');
            }
            $comment->reply_customer_id = $reply_customer_id;
            $comment->reply_customer_name = $reply_customer->name;
        }
        if($comment->save()){
            //对活动进行+1的评论
            $this->activity->find($id)->increment('comments');
            //获取评论列表c
            return return_rest('1','','评论添加成功');
        }
        return return_rest('0','','评论添加失败2');
    }
    /**
     * 获取活动评论
     */
    public function comment()
    {
        //获取活动id
        $id = $this->request->get('id');
        //根据活动id获取相关评论
        $comments = ActivityComment::select('id','mobile','content','customer_id','customer_name','customer_avatar','created_at','reply_customer_id','reply_customer_name')
            ->where('activity_id',$id)
            ->orderBy('created_at','desc')
            ->get()->toArray();
        return return_rest('1',compact('comments'),'操作成功');
    }
    /**
     * 判读用户是否报名活动
     */
    public function checkApply()
    {
        $customer_id = $this->user()->id;
        $activity_id = $this->request->get('id');
        if($apply = Apply::isCustomerApply($customer_id,$activity_id)){
            $apply = $apply->toArray();
            $apply['isApply'] = '1';
            return return_rest('1',compact('apply'),'该用户已报名');
        }
        return return_rest('1',array('isApply'=>'0'),'未报名');
    }
    /**
     * 活动报名
     */
    public function apply()
    {
        //获取报名用户id
        $customer_id = $this->user()->id;
        //活动id
        $activity_id = $this->request->get('id');
        //检查用户是否已报名
        if($apply = Apply::isCustomerApply($customer_id,$activity_id)){
            return return_rest('0',compact('apply'),'该用户已报名');
        }
        //获取报名信息
        $apply = new Apply();
        $apply->name = $this->request->get('name');
        $apply->mobile = $this->request->get('mobile');
        $apply->email = $this->request->get('email');
        $apply->customer_id = $customer_id;
        $apply->activity_id = $activity_id;
        if($apply->save()){
            return return_rest('1','','报名成功');
        }
        return return_rest('0','','报名失败');
    }
    //检查用户是否被禁言
    public function checkGag()
    {
        //获取用户是否被禁言
        $customer = Customer::find($this->user()->id);
        if($customer){
            $isGag = $customer->is_activity_comment;
            return return_rest('1',array('isGag'=>$isGag),'用户是否被禁言');
        }
        return return_rest('0','','该用户不存在');
    }
}