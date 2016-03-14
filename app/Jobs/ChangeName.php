<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Models\Activity\ActivityComment;
use App\Models\Customer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ChangeName extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    protected $name,$uid;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($uid)
    {
        $this->uid = $uid;
        //获取用户最新名称
        $this->name = Customer::find($uid)->name;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //更新用户评论中名称
        $comment = ActivityComment::where('customer_id',$this->uid)->update(['customer_name' => $this->name]);
    }
}
