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
use App\Transformer\CompanyTransformer;
use Illuminate\Http\Request;

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
            ->paginate();
        return $this->response->paginator($company,new CompanyTransformer());
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
        if(!$company = $this->company->where('customer_id',$user->id)->first()){
            return return_rest('0','','该用户没有公司');
        }
        //获取更新信息
        $company->fill($this->request->input());
        if($company->save()){
            return return_rest('1','','更新成功');
        }
        return return_rest('0','','更新失败');
    }
}