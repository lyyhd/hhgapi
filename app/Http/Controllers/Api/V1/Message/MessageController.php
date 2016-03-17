<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/3/16
 * Time: 下午10:57
 */

namespace App\Http\Controllers\Api\V1\Message;


use App\Http\Controllers\Api\BaseController;
use App\Models\Customer;
use Carbon\Carbon;
use Cmgmyr\Messenger\Models\Message;
use Cmgmyr\Messenger\Models\Participant;
use Cmgmyr\Messenger\Models\Thread;
use Illuminate\Http\Request;

class MessageController extends BaseController
{
    /**
     * @var Pusher
     */
    protected $pusher,$request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * 获取用户消息列表
     */
    public function index()
    {
        $customer_id = $this->user()->id;
        $threads = Thread::forUser($customer_id)->get();
        foreach($threads as $thread){
            $message = $thread->messages()->latest()->get();
            $thread->message = $message[0]['body'];
        }
        return return_rest('1',compact('threads'),'消息列表');
    }
    /**
     * 存储提醒消息
     */
    public function store()
    {
        //推送内容
        $subject = $this->request->get('subject');
        $thread = Thread::create([
            'subject' => $subject
        ]);
        //生成message
        $message = Message::create([
            'thread_id' => $thread->id,
            'user_id'   => $this->user()->id,
            'body'      => $this->request->get('message'),
        ]);
        // Sender
        Participant::create(
            [
                'thread_id' => $thread->id,
                'user_id'   => $this->user()->id,
                //'last_read' => new Carbon,
            ]
        );
        // Recipients
        if ($this->request->has('recipients')) {
            $thread->addParticipants($this->request->get('recipients'));
        }
        return return_rest('1','','消息添加成功');
    }
    //获取未读消息数量
    public function unread()
    {
        $customer = Customer::find($this->user()->id);
        $count = $customer->newMessagesCount();
        return return_rest('1',compact('count'),'未读消息');
    }
    //标记消息为已读
    public function read()
    {

        $thread = Thread::find($this->request->get('id'));
        if (!$thread) {
            return return_rest('0',compact('count'),'该消息不存在');
        }

        $thread->markAsRead($this->user()->id);
        return return_rest('1',compact('count'),'已读消息');
    }

}