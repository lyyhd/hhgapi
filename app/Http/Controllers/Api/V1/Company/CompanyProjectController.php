<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/2/21
 * Time: 下午10:48
 */

namespace App\Http\Controllers\Api\V1\Company;


use App\Http\Controllers\Api\BaseController;
use App\Models\Company\Company;
use App\Models\Company\CompanyIntroduce;
use App\Models\Company\CompanyProject;
use App\Models\Company\CompanyProjectDynamic;
use App\Models\Company\CompanyProjectField;
use App\Models\Company\CompanyProjectFieldConfig;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyProjectController extends BaseController
{
    protected $request,$project;
    public function __construct(Request $request,CompanyProject $project)
    {
        $this->request = $request;
        $this->project = $project;
    }
    //获取项目列表
    public function index()
    {
        //获取分页数据
        $per_page = 15;
        if($this->request->has('per_page')){
            $per_page = $this->request->get('per_page');
        }
        $project = $this->project->select('id','name','logo','finance_progress','brief')->with('field')->paginate($per_page)->toArray();

        return return_rest('1',compact('project'),'项目列表');
    }
    /**
     * 项目详情
     */
    public function show()
    {
        //获取项目id
        $id = $this->request->get('id');

        $project = $this->project->select('id','name','logo','brief','finance_progress','company_id','target_amount','start_amount','get_out','subscribe','currency')->with('field')->find($id);
        if(!$project){
            return return_rest('0','','该项目不存在');
        }
        //获取项目创世人 联合创始人
        $project['customer'] = Customer::where('company_id',$project['company_id'])->select('id','name','position','avatar','mobile')->get()->toArray();
        //获取项目优势
        $project['teamAdvantage'] = "";
        $company_extend = DB::table('company_extend')->select('story')->where('company_id',$project['company_id'])->first();
        if($company_extend){
            $project['teamAdvantage'] = $company_extend->story;
        }
        //获取公司介绍
        $project['companyIntroduce'] = CompanyIntroduce::select('company_introduce.id','company_introduce.content','company_introduce_config.name')->where('company_id',$project['company_id'])
            ->leftJoin('company_introduce_config','company_introduce.config_id','=','company_introduce_config.id')
            ->orderBy('company_introduce.config_id','asc')->get()->toArray();
        //获取公司动态详情
        $project['dynamic'] = CompanyProjectDynamic::select('company_project_dynamic.id','company_project_dynamic.content','company_project_dynamic.year','company_project_dynamic.date','company_project_dynamic_config.name')->where('company_id',$project['compayy_id'])
            ->leftJoin('company_project_dynamic_config','company_project_dynamic.config_id','=','company_project_dynamic_config.id')
            ->orderBy('year','desc')->get()->toArray();
        return return_rest('1',compact('project'),'项目详情');
    }
    /**
     * 我的项目
     */
    public function mine()
    {
        $user = $this->user();
        //获取项目信息
        $project = $this->project
            ->select('id','name','logo','brief','finance_progress','company_id','target_amount','start_amount','get_out','subscribe','currency','city')->where('company_id',$user->company_id)
            ->with('field')
            ->first()->toArray();
        //项目介绍
        $project['project_introduce'] = DB::table('company_project_detail')->where('company_project_id',$project['id'])->first()->project_introduce;
        //获取企业网站
        $project['website'] = Company::where('id',$project['company_id'])->first()->website;
        return return_rest('1',compact('project'),'项目详情');
    }
    /**
     * 获取项目领域列表
     */
    public function field()
    {
        $field = CompanyProjectFieldConfig::select('id','name')->withOnly('subField',array('id','name','parent_id'))->where('parent_id',0)->get()->toArray();
        return return_rest('1',compact('field'),'项目领域列表');
    }

    /**
     * 更新项目信息
     */
    public function update()
    {

        $project = new CompanyProject();

    }
}