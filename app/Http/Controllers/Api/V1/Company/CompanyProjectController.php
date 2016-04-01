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
use App\Models\Company\CompanyExtend;
use App\Models\Company\CompanyFinance;
use App\Models\Company\CompanyIntroduce;
use App\Models\Company\CompanyProject;
use App\Models\Company\CompanyProjectDynamic;
use App\Models\Company\CompanyProjectField;
use App\Models\Company\CompanyProjectFieldConfig;
use App\Models\Customer;
use App\Models\Invest\InvestProject;
use App\Models\Invest\InvestRoundConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Customer\CustomerProject;
class CompanyProjectController extends BaseController
{
    protected $request,$project;
    public function __construct(Request $request,CompanyProject $project)
    {
        parent::__construct($request);
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
        $query = $this->project->select('id','name','logo','finance_progress','brief','subscribe_amount');
        //获取搜索条件
        if($this->request->has('field')){
            $query->whereHas('field', function($q)
            {
                if(!($this->request->get('field') == "\"\"")){
                    $field_id = explode(',',$this->request->get('field'));
                    $q->whereIn('field_id',$field_id);
                }
            });
        }
        if($this->request->has('finance')){
            $query->whereHas('finance', function($q)
            {
                if(!($this->request->get('finance') == "\"\"")){
                    $finance_id = explode(',',$this->request->get('finance'));
                    $q->whereIn('finance_id',$finance_id);
                }
            });
        }
        //获取项目投资轮次
        $project = $query
            ->with('field')
            ->with('finance')
            ->paginate($per_page)
            ->toArray();
        $i = 0;
        foreach($project['data'] as $item)
        {
            $project['data'][$i]['finance']['name'] = '天使轮';
            if(!is_null($item['finance']['finance_id'])){
                $project['data'][$i]['finance']['name'] = $this->financeName($item['finance']['finance_id']);
            }

            $i++;
        }
        return return_rest('1',compact('project'),'项目列表');
    }
    /**
     * 项目详情
     */
    public function show()
    {
        //获取项目id
        $id = $this->request->get('id');

        $project = $this->project->select('id','name','logo','brief','finance_progress','company_id','target_amount','start_amount','get_out','subscribe','subscribe_amount','currency','city','view','share')->with('field')->find($id);
        if(!$project){
            return return_rest('0','','该项目不存在');
        }
        $project = $project->toArray();
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
        //获取项目投资轮次
        $project_finance = DB::table('company_project_finance')
            ->where('project_id',$project['id'])
            ->orderBy('created_at','desc')
            ->first();
        $project['project_finance'] = '天使轮';
        if($project_finance){
            $project['project_finance'] = $this->financeName($project_finance->finance_id);
        }
        $project['project_finance'] = $project['project_finance'];
        return return_rest('1',compact('project'),'项目详情');
    }
    /**
     * 我的项目
     */
    public function mine()
    {
        $user = $this->user();
        //获取用户类型 是否为投资人
        if($user->type == '2'){
            //该用户为投资人 获取用户投资列表
            $invest_project = InvestProject::select('project_id')->where('customer_id',$user->id)->get()->toArray();
            if(empty($invest_project)) return return_rest('0',array('project'=>array()),'暂无投资项目');
            $project_id = array_pluck($invest_project,'project_id');
            $project = CompanyProject::select('id','name','brief','logo')->whereIn('id',$project_id)->with('field')->get()->toArray();
            $i = 0;
            foreach($project as $item){
                //获取项目投资轮次
                $project_finance = DB::table('company_project_finance')
                    ->where('project_id',$item['id'])
                    ->orderBy('created_at','desc')
                    ->first();
                $project[$i]['project_finance'] = '天使轮';
                if($project_finance){
                    $project[$i]['project_finance'] = $this->financeName($project_finance->finance_id);
                }
                $i++;
            }
            return return_rest('1',compact('project'),'项目列表');
        }
        if($user->company_id == 0){
            $project['is_company'] = '0';
            $project['is_project'] = '0';
            return return_rest('1',compact('project'),'项目详情');
        }
        //获取项目信息
        $project = $this->project
            ->select('id','name','logo','brief','finance_progress','company_id','target_amount','start_amount','get_out','subscribe','subscribe_amount','currency','city','view','share')
            ->where('company_id',$user->company_id)
            ->with('field')
            ->first();
        if(is_null($project)) return return_rest('0',array('is_company'=>'1','is_project'=>'0'),'该用户没有项目');
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
        $project['website'] = Company::where('id',$user->company_id)->first()->website;
        //获取项目优势
        $project['teamAdvantage'] = "";
        $company_extend = CompanyExtend::select('story')->where('company_id',$user->company_id)->first();
        if($company_extend){
            $project['teamAdvantage'] = $company_extend->story;
        }
        $project['is_company'] = '1';
        $project['is_project'] = '1';
        //获取项目投资轮次
        $project_finance = DB::table('company_project_finance')
            ->where('project_id',$project['id'])
            ->orderBy('created_at','desc')
            ->first();
        $project['project_finance'] = '天使轮';
        if($project_finance){
            $project['project_finance'] = $this->financeName($project_finance->finance_id);
        }
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
     * 获取投资轮次列表
     */
    public function round()
    {
        $round = InvestRoundConfig::select('id','name')->get()->toArray();
        return return_rest('1',compact('round'),'项目投资轮次列表');
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
    public function updateTeam()
    {
        //获取项目id
        $id = $this->request->get('id');
        //获取公司id
        $company = Company::find($id);
        if(!$company){
            return return_rest('0','','公司不存在');
        }
        $content = $this->request->get('content');
        //更新团队优势 团队介绍
        $extend = CompanyExtend::where('company_id',$company->id)->first();
        if(is_null($extend)){
            return return_rest('0','','更新失败');
        }
        $extend->story = $content;
        if($extend->save()){
            return return_rest('1','','更新成功');
        }
        return return_rest('0','','更新失败');
    }
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
        $file = $this->request->file('logo');
        $fileName = md5(uniqid(str_random(10))).'.'.$file->getClientOriginalExtension();
        try {
            $file->move(storage_path('uploads/projectLogo'), $fileName);
            //将图片上传至七牛
            $imgUrl = $this->qiniuUpload($fileName,'uploads/projectLogo');
            //添加进入数据库
            $project->logo = $imgUrl;
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
        $file = $this->request->file('logo');
        $fileName = md5(uniqid(str_random(10))).'.'.$file->getClientOriginalExtension();
        try {
            $file->move(storage_path('uploads/projectLogo'), $fileName);
            //将图片上传至七牛
            $imgUrl = $this->qiniuUpload($fileName,'uploads/projectLogo');
            //添加进入数据库
            $project->logo = $imgUrl;
            $project->save();
            return return_rest('1',compact('file'),'logo上传成功');
        }catch (\Exception $e){
            //图片上传失败
            return return_rest('0','',$e->getMessage());
        }

    }

    /**
     * 获取投资轮次
     */
    private function financeName($finance_id)
    {
        switch($finance_id){
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
        return $finance_name;
    }
    /**
     * 访问量+1
     */
    public function viewPlus()
    {
        $id = $this->request->get('id');
        if(!$id){
            return return_rest('0','','该项目不存在,别逗了');
        }
        //对访问量进行+1
        $this->project->find($id)->increment('view');
        return return_rest('1','','成功增加');
    }
    /**
     * 检查当前用户是否收藏项目
     */
    public function checkCollect()
    {
        $id = $this->request->get('id');
        //判断活动是否存在并获取活动id
        try{
            $project = $this->project->findOrFail($id);
        }catch (\Exception $e){
            if($e->getMessage() === 'No query results for model [App\Models\Company\CompanyProject].') return return_rest('0','','该项目不存在');
            return return_rest('0','',$e->getMessage());
        }
        //判断用户是否关注该活动
        $collect = Customer\CustomerProject::where('project_id',$project->id)->where('customer_id',$this->user()->id)->get();
        if(count($collect) >= 1){
            return return_rest('1',array('collect' => '1'), '用户已收藏该项目');
        }
        return return_rest('1',array('collect' => '0'), '用户未收藏该项目');
    }
    /**
     * 用户进行收藏 取消收藏操作
     */
    public function doCollect()
    {
        $id = $this->request->get('id');
        //判断活动是否存在并获取活动id
        try{
            $project = $this->project->findOrFail($id);
        }catch (\Exception $e){
            if($e->getMessage() === 'No query results for model [App\Models\Company\CompanyProject].') return return_rest('0','','该项目不存在');
            return return_rest('0','',$e->getMessage());
        }
        //判断用户是否收藏该活动
        $collect = CustomerProject::where('project_id',$project->id)->where('customer_id',$this->user()->id)->first();
        //判断操作类型 1收藏 0取消收藏
        $action = $this->request->get('collect');
        if($action == '1'){
            if(count($collect) >= 1){
                return return_rest('1','', '用户已收藏该活动');
            }
            $collect = new CustomerProject();
            $collect->customer_id = $this->user()->id;
            $collect->project_id = $id;
            if($collect->save()){
                return return_rest('1','', '收藏成功');
            }
            return return_rest('0','', '收藏失败');
        }
        if($action == '0'){
            if(count($collect) == 0){
                return return_rest('0','', '用户未收藏该项目');
            }
            if($collect->delete()){
                return return_rest('1','', '取消收藏成功');
            }
            return return_rest('0','', '取消收藏失败');
        }
    }
    /**
     * 获取投资人用户收藏项目
     */
    public function loveList()
    {
        $per_page = 15;
        if($this->request->has('per_page')){
            $per_page = $this->request->get('per_page');
        }
        //获取活动id
        $projects = CustomerProject::select('project_id')->where('customer_id',$this->user()->id)->orderBy('created_at','desc')->get();
        $pid = array();
        foreach($projects as $project){
            $pid[] = $project->project_id;
        }
        //获取活动
        $loveList = $this->project->select('id','name','logo','brief','finance_progress','company_id','target_amount','start_amount','get_out','subscribe','subscribe_amount','currency','city','view','share')->whereIn('id',$pid)->paginate($per_page);
        $loveList = $loveList->toArray();
        if($loveList){
            return return_rest('1',compact('loveList'),'获取列表成功');
        }
        return return_rest('1',"",'用户暂未关注活动');
    }
}