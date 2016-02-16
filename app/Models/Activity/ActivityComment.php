<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/2/16
 * Time: 下午2:05
 */

namespace App\Models\Activity;


use App\Models\BaseModel;

class ActivityComment extends BaseModel
{
    protected $table = 'activity_comments';

    //获取评论对应的回复
    public function reply()
    {
        $this->hasMany('App\Models\Activity\ActivityCommentReply')->with('customer');
    }
    //获取评论对应用户
    public function customer()
    {
        $this->hasOne('App\Models\Customer');
    }
}