<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/2/27
 * Time: 下午11:42
 * 投资经历
 */

namespace App\Http\Controllers\Api\V1\Invest;


use App\Http\Controllers\Api\BaseController;
use App\Models\Invest\InvestExperience;
use App\Models\Invest\InvestRoundConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvestExperienceController extends BaseController
{
    protected $investExperience,$request;
    public function __construct(InvestExperience $investExperience,Request $request)
    {
        $this->investExperience = $investExperience;
        $this->request = $request;
    }

    public function index()
    {
        $customer = $this->user();
        $experience = $this->investExperience->where('customer_id',$customer->id)->withOnly('round',array('id','name'))->withOnly('company',array('id','name','logo','brief'))->orderBy('created_at','desc')->get()->toArray();
        return return_rest(1,compact('experience'),'投资简历列表');
    }

    /**
     * 创建新的投资经历
     */
    public function create()
    {
        $validator = \Validator::make($this->request->all(),[

        ],[

        ]);
        $customer = $this->user();
        $investExperience = new InvestExperience();
        $investExperience->customer_id = $customer->id;
        $investExperience->company_id = $this->request->get('company_id');
        $investExperience->round_id = $this->request->get('round_id');
        $investExperience->invest_amount_unit = $this->request->get('invest_amount_unit');
        $investExperience->invest_amount = $this->request->get('invest_amount');
        $investExperience->finance_amount_unit = $this->request->get('finance_amount_unit');
        $investExperience->finance_amount = $this->request->get('finance_amount');
        $investExperience->valuation_unit = $this->request->get('valuation_unit');
        $investExperience->valuation = $this->request->get('valuation');
        $investExperience->invest_group = $this->request->get('invest_group');
        $investExperience->invest_group_id = $this->request->get('invest_group_id');
        $investExperience->invest_year = $this->request->get('invest_year');
        $investExperience->invest_mouth = $this->request->get('invest_mouth');
        $investExperience->save();
        return return_rest('1','','添加成功');
    }

    /**
     * 获取投资轮次
     */
    public function round()
    {
        $round = InvestRoundConfig::get()->toArray();
        return return_rest('1',compact('round'),'投资轮次列表');
    }

}