<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/3/29
 * Time: 下午1:56
 * 投资公司接口
 */

namespace App\Http\Controllers\Api\V1\Invest;


use App\Http\Controllers\Api\BaseController;
use App\Models\Invest\InvestCompany;
use Illuminate\Http\Request;

class InvestCompanyController extends BaseController
{
    protected $request,$investCompany;
    public function __construct(Request $request,InvestCompany $investCompany)
    {
        $this->request = $request;
        $this->investCompany = $investCompany;
    }

    public function index()
    {

    }

    public function update()
    {
        //获取投资人公司信息
        $investCompany = $this->investCompany->find($this->user()->company_id);
        //判断该用户是否有投资公司
        if(is_null($investCompany)) return return_rest('0','','更新失败 该用户没有投资公司');
        $investCompany->fill($this->request->input());

        if($investCompany->save()){
            return return_rest('1','','更新成功');
        }
        return return_rest('0','','更新失败');
    }
}