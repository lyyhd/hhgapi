<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/2/21
 * Time: 下午10:48
 */

namespace App\Http\Controllers\Api\V1\Company;


use App\Http\Controllers\Api\BaseController;
use App\Models\Company\CompanyProject;
use App\Models\Customer;
use Illuminate\Http\Request;

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

        $project = $this->project->with('field')->with('detail')->find($id);
        if(!$project){
            return return_rest('0','','该项目不存在');
        }
        //获取项目创世人 联合创始人
        $customer = Customer::where('company_id',$project['id'])->select('id','name','position')->get()->toArray();

        return return_rest('1',compact('project','customer'),'项目详情');
    }
}