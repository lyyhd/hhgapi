<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/1/21
 * Time: 下午3:27
 */

namespace App\Http\Controllers\Api\V1\Company;


use App\Http\Controllers\Api\BaseController;
use App\Models\Company\CompanyField;
use Illuminate\Http\Request;

class CompanyFieldController extends BaseController
{
    protected $model_field;

    public function __construct(Request $request,CompanyField $model_field)
    {
        parent::__construct($request);
        $this->model_field = $model_field;
    }

    /**
     * 获取所有领域 父节点 列表
     */
    public function index()
    {
        return $this->model_field->where('parent','0')->get();
    }
    /**
     * 获取领域详情
     */
    public function fieldDetail($id)
    {
        return $this->model_field->select('id','name')->where('parent',$id)->get();
    }
}