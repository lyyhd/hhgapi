<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/2/16
 * Time: 下午2:06
 */

namespace App\Models\Activity;


use App\Models\BaseModel;

class ActivityCommentReply extends BaseModel
{
    protected $table = 'activity_comments_replies';

    public function customer()
    {
        return $this->hasOne('App\Models\Customer');
    }

    public function reply()
    {
        return $this->hasMany('App\Models\Activity\ActivityCommentReply','reply_id','id');
    }
}