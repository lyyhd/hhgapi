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
    protected $table = 'activity_comment_replies';

    public function customer()
    {
        $this->hasOne('App\Models\Customer');
    }
}