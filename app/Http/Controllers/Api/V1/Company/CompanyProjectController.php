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
use App\Models\Company\CompanyFinance;
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
        $project = $this->project->select('id','name','logo','finance_progress','brief','subscribe_amount')->with('field')->paginate($per_page)->toArray();

        return return_rest('1',compact('project'),'项目列表');
    }
    /**
     * 项目详情
     */
    public function show()
    {
        //获取项目id
        $id = $this->request->get('id');

        $project = $this->project->select('id','name','logo','brief','finance_progress','company_id','target_amount','start_amount','get_out','subscribe','currency','city')->with('field')->find($id)->toArray();
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
        //获取企业网站
        $project['website'] = Company::where('id',$project['company_id'])->first()->website;
        //获取融资轮次
        //获取公司介绍
        $project['companyIntroduce'] = CompanyIntroduce::select('company_introduce.id','company_introduce.content','company_introduce_config.name')->where('company_id',$project['company_id'])
            ->leftJoin('company_introduce_config','company_introduce.config_id','=','company_introduce_config.id')
            ->orderBy('company_introduce.config_id','asc')->get()->toArray();
        //获取动态详情
        //获取动态年份
        $years = CompanyProjectDynamic::select('year')->where('project_id',$project['id'])->groupBy('year')->orderBy('year','desc')->get()->toArray();
        foreach($years as $year)
        {
            $project['dynamic_ios'][$year['year']] = CompanyProjectDynamic::select('company_project_dynamic.id','company_project_dynamic.content','company_project_dynamic.date','company_project_dynamic_config.name')
                ->where('project_id',$project['id'])
                ->where('year',$year)
                ->leftJoin('company_project_dynamic_config','company_project_dynamic.config_id','=','company_project_dynamic_config.id')
                ->orderBy('date','desc')->get()->toArray();
        }
        $project['dynamic_android'] = CompanyProjectDynamic::select('company_project_dynamic.id','company_project_dynamic.content','company_project_dynamic.year','company_project_dynamic.date','company_project_dynamic_config.name')
            ->where('project_id',$project['id'])
            ->leftJoin('company_project_dynamic_config','company_project_dynamic.config_id','=','company_project_dynamic_config.id')
            ->orderBy('year','desc')
            ->orderBy('date','desc')->get()->toArray();
        return return_rest('1',compact('project'),'项目详情');
    }
    /**
     * 我的项目
     */
    public function mine()
    {
        $user = $this->user();
        if($user->company_id == 0){
            $project['is_company'] = '0';
            return return_rest('1',compact('project'),'项目详情');
        }
        //获取项目信息
        $project = $this->project
            ->select('id','name','logo','brief','finance_progress','company_id','target_amount','start_amount','get_out','subscribe','currency','city')->where('company_id',$user->company_id)
            ->with('field')
            ->first();
        if(is_null($project)) return return_rest('0','','该用户没有项目');
        $project = $project->toArray();
        //项目介绍
        $project_introduce = DB::table('company_project_detail')->where('company_project_id',$project['id'])->first();
        if(!$project_introduce) return return_rest('0','','项目介绍未添加,请联系harry');
        $project['project_introduce'] = $project_introduce->project_introduce;
        //获取公司介绍
        $project['companyIntroduce'] = CompanyIntroduce::select('company_introduce.id','company_introduce.content','company_introduce_config.name')->where('company_id',$project['company_id'])
            ->leftJoin('company_introduce_config','company_introduce.config_id','=','company_introduce_config.id')
            ->orderBy('company_introduce.config_id','asc')->get()->toArray();
        //获取企业网站
        $project['website'] = Company::where('id',$project['company_id'])->first()->website;
        //获取项目优势
        $project['teamAdvantage'] = "";
        $company_extend = DB::table('company_extend')->select('story')->where('company_id',$project['company_id'])->first();
        if($company_extend){
            $project['teamAdvantage'] = $company_extend->story;
        }
        $project['is_company'] = '1';
        //获取项目投资轮次
        $project_finance = DB::table('company_project_finance')
            ->where('project_id',$project['id'])
            ->orderBy('created_at','desc')
            ->first();
        $finance_name = '';
        switch($project_finance->finance_id){
            case '0':
                $finance_name = '未融资';
                break;
            case '1':
                $finance_name = '天使轮';
                break;
            case '2':
                $finance_name = 'Pre-A轮';
                break;
            case '3':
                $finance_name = 'A轮';
                break;
            case '4':
                $finance_name = 'A+轮';
                break;
            case '5':
                $finance_name = 'B轮';
                break;
            case '6':
                $finance_name = 'B+轮';
                break;
            case '7':
                $finance_name = 'C轮';
                break;
            case '8':
                $finance_name = 'D轮';
                break;
            case '9':
                $finance_name = 'E轮及以后';
                break;
        }

        $project['project_finance'] = $finance_name;
        return return_rest('1',compact('project'),'项目详情');
    }
    /**
     * 获取项目领域列表
     */
    public function field()
    {
        $field = CompanyProjectFieldConfig::select('id','name')->where('parent_id',0)->get()->toArray();
        return return_rest('1',compact('field'),'项目领域列表');
    }
    /**
     * 更新项目领域
     */
    public function updateField()
    {
        $id = $this->request->get('id');
        //TODO判断用户公司id是否匹配
        $project = $this->project->find($id);
        if($project){
            $fids = json_decode($this->request->get('field'));
            CompanyProjectField::where('project_id',$id)->delete();
            //判断field是否存在
            foreach($fids as $fid){
                $field = CompanyProjectField::where('project_id',$id)->where('field_id',$fid)->first();
                if(is_null($field)){
                    $field = new CompanyProjectField();
                    $field->project_id = $id;
                    $field->field_id = $fid;
                    $field->save();
                }
            }
            return return_rest('1','','领域更新成功');
        }
        return return_rest('0','','项目不存在');
    }
    /**
     * 更新项目信息
     */
    public function update()
    {
        $id = $this->request->get('id');
        $project = $this->project->find($id);
        if($project){
            $project->fill($this->request->input());
            if($project->save()){
                return return_rest('1','','项目更新成功');
            }
            return return_rest('0','','项目更新失败');
        }
        return return_rest('0','','项目不存在');
    }
    /**
     *更新项目介绍
    */
//    public function
    /**
     * 项目logo替换
     */
    public function logo()
    {
        //验证数据
        $validator = \Validator::make($this->request->all(),[
            'logo'    => 'required',
            'id'      => 'required'
        ],[
            'logo.required'   => 'logo没有上传',
            'id.required'   => '未指定项目id'
        ]);


        if($validator->fails()) return return_rest('0','',$validator->messages()->first('logo') ? $validator->messages()->first('logo') : $validator->messages()->first('id'));
        //TODO 获取手机号码
        $project = $this->project->find($this->request->get('id'));
        $fileName = md5(uniqid(str_random(10)));
        $file = 'uploads/project/logo/'.$fileName.'.jpg';
        try {
            \Image::make($this->request->file('logo'))->save($file);
            //添加进入数据库
            $project->logo = $file;
            $project->save();
            return return_rest('1',compact('file'),'logo上传成功');
        }catch (\Exception $e){
            //图片上传失败
            return return_rest('0','',$e->getMessage());
        }

    }
    /**
     * 项目logo替换
     * v2
     * post
     */
    public function projectLogo()
    {
        //验证数据
        $validator = \Validator::make($this->request->all(),[
            'logo'    => 'required',
            'id'      => 'required'
        ],[
            'logo.required'   => 'logo没有上传',
            'id.required'   => '未指定项目id'
        ]);


        if($validator->fails()) return return_rest('0','',$validator->messages()->first('logo') ? $validator->messages()->first('logo') : $validator->messages()->first('id'));
        //TODO 获取手机号码
        $project = $this->project->find($this->request->get('id'));
        $fileName = md5(uniqid(str_random(10)));
        $file = 'uploads/project/logo/'.$fileName.'.jpg';
        try {
            \Image::make($this->request->file('logo'))->save($file);
            //添加进入数据库
            $project->logo = $file;
            $project->save();
            return return_rest('1',compact('file'),'logo上传成功');
        }catch (\Exception $e){
            //图片上传失败
            return return_rest('0','',$e->getMessage());
        }

    }
}