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
    protected $model_company;

    public function __construct(Request $request,Company $model_company)
    {
        parent::__construct($request);

        $this->model_company = $model_company;
    }

    /**
     * 获取审核通过的公司列表
     */
    public function index()
    {
        //判断是否有搜索条件
        $company = $this->model_company
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

}