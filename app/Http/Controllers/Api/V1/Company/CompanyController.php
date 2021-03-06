<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/1/21
 * Time: 下午2:31
 * 公司管理
 */

namespace App\Http\Controllers\Api\V1\Company;


use App\Http\Controllers\Api\BaseController;
use App\Models\Company\Company;
use App\Models\Company\CompanyAddress;
use App\Models\Company\CompanyAuth;
use App\Models\Company\CompanyExperience;
use App\Models\Company\CompanyExtend;
use App\Models\Company\CompanyField;
use App\Models\Company\CompanyIntroduce;
use App\Models\Customer;
use App\Transformer\CompanyTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyController extends BaseController
{
    protected $company;

    public function __construct(Request $request,Company $company)
    {
        parent::__construct($request);

        $this->company = $company;
    }

    /**
     * 获取审核通过的公司列表
     */
    public function index()
    {
        //判断是否有搜索条件
        $company = $this->company
            ->status()
            ->searchField($this->request->get('field'))//根据领域
            ->searchFinance($this->request->get('finance'))//根据融资状态
            ->withOnly('address',['company_id','city'])
            ->withOnly('customer',['name'])
            ->withOnly('field',['name'])
            ->orderBy('id','desc')
            ->where('status',1)
            ->paginate();
        return $this->response->paginator($company,new CompanyTransformer());
    }

    /**
     * 获取公司详情
     *
     */
    public function show()
    {
        $id = $this->request->get('id');
        $company = $this->company->find($id);
        //判断公司是否存在
        if(!$company){
            return return_rest('0','','该公司不存在');
        }
        $company = $company->toArray();
        //获取项目介绍
        $company['introduce'] = CompanyIntroduce::select('company_introduce.id','company_introduce.content','company_introduce_config.name')->where('company_id',$company['id'])
            ->leftJoin('company_introduce_config','company_introduce.config_id','=','company_introduce_config.id')
            ->orderBy('company_introduce.config_id','asc')->get()->toArray();
        return return_rest('1',compact('company'),'获取公司详情');

    }
    //获取公司团队成员
    public function team()
    {
        $id = $this->request->get('id');
        //获取团队介绍
        $team = Customer::select('id','user_name','name','avatar','is_company_creator','position','position_detail','brief')->where('company_id',$id)->get()->toArray();
        return return_rest('1',compact('team'),'获取团队成员');
    }

    //获取我的项目
//    public function
    /**
     * 更新信息
     * public function update()
    {
    $user = $this->user();

    $user->fill($this->request->input());

    $user->save();

    //return $this->response->item($user, new CustomerTransformer);
    return return_rest(1,'','更新成功');
    }
     */
    public function update(){
        //获取登录用户信息
        $user = $this->user();
        //获取用户公司信息
        $company =  $this->company->where('id',$user->company_id)->first();
        if(!$company){
            return return_rest('0','','该用户没有公司');
        }
        //获取更新信息
        $company->fill($this->request->input());
        if($company->save()){
            return return_rest('1','','更新成功');
        }
        return return_rest('0','','更新失败');
    }
    /**
     * 获取公司列表
     *
     */
    public function all()
    {
        $query = Company::select('id','name','brief','logo','field_id','address')
            ->withOnly('customer',['id','company_id','name','position','avatar'])
            ->withOnly('field',['id','name'])
            ->where('status',1)
            ->orderBy('id','desc');
        //判断搜索条件
        if($this->request->has('field') && $this->request->get('field') > 0)
        {
            $field = $this->request->get('field');
            $query->searchField($field);
        }
        //
        $company = $query->paginate(15)->toArray();
        return return_rest('1',compact('company'),'公司列表');
    }
    /**
     * 创建新公司
     */
    public function store()
    {
        DB::beginTransaction();
        try{
            $company = new Company();
            $company->name = $this->request->get('name');
            $company->full_name = $this->request->get('full_name');
            $company->logo = $this->request->get('logo');
            $company->website = $this->request->get('website');
            $company->brief = $this->request->get('brief');
            $company->address = $this->request->get('province').$this->request->get('city').$this->request->get('dist');
            if(empty($this->request->get('sub_field'))){
                $company->field_id = $this->request->get('company_field');
            }else{
                $company->field_id = $this->request->get('sub_field');
            }
            //获取公司类型
            $company->type = $this->request->get('type');
            if($this->request->has('iphoneAppstoreLink')){
                $company->iphoneAppstoreLink = $this->request->get('iphoneAppstoreLink');
            }
            if($this->request->has('androidLink')){
                $company->androidLink = $this->request->get('androidLink');
            }
            if($this->request->has('website')){
                $company->website = $this->request->get('website');
            }
            if($this->request->has('weixin')){
                $company->weixin = $this->request->get('weixin');
            }
            if($this->request->has('operationStatus')){
                $company->operationStatus = $this->request->get('operationStatus');
            }
            $company->save();
            //增加公司详情
            $extend = new CompanyExtend();
            $extend->company_id = $company->id;
            $extend->projectAdvantage = $this->request->get('projectAdvantage');
            $extend->dataLights = $this->request->get('dataLights');
            $extend->story = $this->request->get('story');
            $extend->save();
            //增加公司认证信息
            $auth = new CompanyAuth();
            $auth->customer_id = $this->user()->id;
            $auth->company_id = $company->id;
            $auth->position = $this->request->get('position');
            $auth->position_detail = $this->request->get('position_detail');
            $auth->start_year = $this->request->get('startYear');
            $auth->start_month = $this->request->get('startMonth');
            $auth->is_today = $this->request->get('is_today');
            if($this->request->get('is_today') == 0){
                $auth->end_year = $this->request->get('end_year') ? $this->request->get('endYear') : Carbon::now()->year;
                $auth->end_month = $this->request->get('end_month') ? $this->request->get('endMonth') : Carbon::now()->month;
            }
            $auth->bizCardLinkFile = $this->request->get('auth');
            $auth->company_name = $company->name;
            $auth->save();
            //更新用户公司信息
            //创建公司 如果用户类型为3游客 变更为1创业者
            Customer::where('id',$this->user()->id)->update([
                'company_id' => $company->id,
                'position' => $this->request->get('position'),
                'position_detail' => $this->request->get('position_detail'),
                'is_company_creator'    => 1,
                'type'              => 1,
                'type_state'        => 1
            ]);
            //添加创业经历
            $experience = new CompanyExperience();
            $experience->company_id = $company->id;
            $experience->startYear = $this->request->get('startYear');
            $experience->startMouth = $this->request->get('startMonth');
            $experience->is_today = $this->request->get('is_today');
            $experience->endYear = $this->request->has('endYear') ? $this->request->has('endYear') : '';
            $experience->endMouth = $this->request->has('endMonth') ? $this->request->has('endMonth') : '';
            $experience->bizCardLink = $this->request->get('auth');
            $experience->customer_id = $this->user()->id;
            $experience->save();
            DB::commit();
            return return_rest('1','','公司添加成功');
        }catch (\Exception $e) {
            DB::rollBack();
            return return_rest('0','',$e->getMessage());
        }

    }
    /**
     *获取创业经历
     */
    public function experience()
    {
        $experience = CompanyExperience::where('customer_id',$this->user()->id)->get()->toArray();

        return return_rest('1',compact('experience'),'我的创业经历');
    }
    /**
     * 更新公司介绍
     *
     */
    public function introduce()
    {
        //获取介绍id
        $id = $this->request->get('id');
        //获取介绍内容
        $content = $this->request->get('content');

        $introduce = CompanyIntroduce::find($id);
        if($introduce){
            $introduce->content = $content;
            if($introduce->save()){
                return return_rest('1','','更新成功');
            }
        }
        return return_rest('0','','介绍不存在');
    }
    /**
     * 对公司访问量+1
     *
     */
    public function view()
    {
        $id = $this->request->get('id');
        if(!$id){
            return return_rest('0','','该项目不存在,别逗了');
        }
        //对访问量进行+1
        $this->company->find($id)->increment('view');
        return return_rest('1','','成功增加');
    }
    //增加文章分享量
    public function addShare()
    {
        $id = $this->request->get('id');

        $this->company->find($id)->increment('share');
        return return_rest('1','','分享量+1');
    }
    //获取职位列表
    public function position()
    {
        $position = DB::table('company_position')->select('id','name')->get();
        return return_rest('1',compact('position'),'职位列表');
    }
}